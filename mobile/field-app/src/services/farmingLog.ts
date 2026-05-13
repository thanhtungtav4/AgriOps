import AsyncStorage from '@react-native-async-storage/async-storage';
import { getApiBaseUrl, API_CONFIG } from '../config/api';
import type { FarmingLog, ApiResponse, QueuedLog } from '../types';
import { authService } from './auth';

const QUEUE_KEY = '@ariops:log_queue';

interface ApiErrorBody {
  error?: {
    code?: string;
    message?: string;
    details?: Record<string, unknown>;
  };
  message?: string;
}

class ApiRequestError extends Error {
  constructor(public readonly body: ApiErrorBody) {
    super(body.error?.message ?? body.message ?? 'Request failed');
  }
}

function errorCodeFrom(err: unknown): string | undefined {
  if (err instanceof ApiRequestError) {
    return err.body.error?.code;
  }

  if (!(err instanceof Error)) {
    return undefined;
  }

  try {
    const body = JSON.parse(err.message) as ApiErrorBody;
    return body.error?.code;
  } catch {
    return undefined;
  }
}

export interface LogSubmission {
  work_task_id: number;
  notes?: string;
  photo_uris?: string[];
  actual_start_at?: string;
  actual_end_at?: string;
  metadata?: Record<string, unknown>;
  client_uuid?: string;
}

class FarmingLogService {
  private async request<T>(path: string, options?: RequestInit): Promise<T> {
    const response = await fetch(`${getApiBaseUrl()}${path}`, {
      ...options,
      headers: {
        Accept: 'application/json',
        ...authService.getAuthHeader(),
        ...options?.headers,
      },
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({ message: 'Request failed' }));
      throw new ApiRequestError(errorData);
    }

    return response.json();
  }

  async submitLog(submission: LogSubmission): Promise<FarmingLog> {
    const formData = new FormData();

    formData.append('work_task_id', String(submission.work_task_id));
    if (submission.notes) formData.append('notes', submission.notes);
    if (submission.actual_start_at) formData.append('actual_start_at', submission.actual_start_at);
    if (submission.actual_end_at) formData.append('actual_end_at', submission.actual_end_at);
    if (submission.client_uuid) formData.append('client_uuid', submission.client_uuid);
    if (submission.metadata) {
      Object.entries(submission.metadata).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          formData.append(`metadata[${key}]`, String(value));
        }
      });
    }

    if (submission.photo_uris && submission.photo_uris.length > 0) {
      for (const uri of submission.photo_uris) {
        formData.append('photos[]', {
          uri,
          type: 'image/jpeg',
          name: 'photo.jpg',
        } as unknown as Blob);
      }
    }

    const response = await this.request<ApiResponse<FarmingLog>>('/work-tasks/' + submission.work_task_id + '/logs', {
      method: 'POST',
      body: formData,
    });

    return response.data;
  }

  async getLog(id: number): Promise<FarmingLog> {
    return this.request<ApiResponse<FarmingLog>>(`/farming-logs/${id}`).then(r => r.data);
  }

  async getLogsForTask(taskId: number): Promise<FarmingLog[]> {
    return this.request<ApiResponse<FarmingLog[]>>(`/work-tasks/${taskId}/logs`).then(r => r.data);
  }
}

export const farmingLogService = new FarmingLogService();

export interface SyncResult {
  success: boolean;
  queuedLog: QueuedLog;
  serverLog?: FarmingLog;
  error?: string;
  isConflict?: boolean;
}

class OfflineQueueService {
  private isProcessing = false;

  async enqueue(log: Omit<QueuedLog, 'syncStatus' | 'retryCount' | 'lastAttempt' | 'errorMessage' | 'serverLogId'>): Promise<QueuedLog> {
    const queuedLog: QueuedLog = {
      ...log,
      syncStatus: 'pending',
      retryCount: 0,
      lastAttempt: null,
      errorMessage: null,
      serverLogId: null,
    };

    const stored = await this.getQueue();
    const exists = stored.some(q => q.localId === log.localId);
    if (exists) return stored.find(q => q.localId === log.localId)!;

    stored.push(queuedLog);
    await this.saveQueue(stored);

    return queuedLog;
  }

  async getQueue(): Promise<QueuedLog[]> {
    const raw = await AsyncStorage.getItem(QUEUE_KEY);
    return raw ? JSON.parse(raw) : [];
  }

  async markSynced(localId: string, serverLogId: number): Promise<void> {
    const stored = await this.getQueue();
    const idx = stored.findIndex(q => q.localId === localId);
    if (idx !== -1) {
      stored[idx] = { ...stored[idx], syncStatus: 'synced', serverLogId };
      await this.saveQueue(stored);
    }
  }

  async markFailed(localId: string, error: string): Promise<void> {
    const stored = await this.getQueue();
    const idx = stored.findIndex(q => q.localId === localId);
    if (idx !== -1) {
      stored[idx] = {
        ...stored[idx],
        syncStatus: 'failed',
        retryCount: stored[idx].retryCount + 1,
        lastAttempt: new Date().toISOString(),
        errorMessage: error,
      };
      await this.saveQueue(stored);
    }
  }

  async markConflict(localId: string, error: string): Promise<void> {
    const stored = await this.getQueue();
    const idx = stored.findIndex(q => q.localId === localId);
    if (idx !== -1) {
      stored[idx] = {
        ...stored[idx],
        syncStatus: 'conflict',
        retryCount: stored[idx].retryCount,
        lastAttempt: new Date().toISOString(),
        errorMessage: error,
        serverLogId: null,
      };
      await this.saveQueue(stored);
    }
  }

  async markUploading(localId: string): Promise<void> {
    const stored = await this.getQueue();
    const idx = stored.findIndex(q => q.localId === localId);
    if (idx !== -1) {
      stored[idx] = { ...stored[idx], syncStatus: 'uploading' };
      await this.saveQueue(stored);
    }
  }

  async retry(localId: string): Promise<SyncResult> {
    const stored = await this.getQueue();
    const queuedLog = stored.find(q => q.localId === localId);
    if (!queuedLog) {
      return {
        success: false,
        queuedLog: {
          localId,
          taskId: 0,
          notes: null,
          photoUris: [],
          loggedAt: new Date().toISOString(),
          actualStartAt: null,
          actualEndAt: null,
          metadata: {},
          syncStatus: 'failed',
          retryCount: 0,
          lastAttempt: null,
          errorMessage: 'Log not found in queue',
          serverLogId: null,
        },
        error: 'Log not found in queue',
      };
    }

    await this.markUploading(localId);

    try {
      const serverLog = await farmingLogService.submitLog({
        work_task_id: queuedLog.taskId,
        notes: queuedLog.notes ?? undefined,
        photo_uris: queuedLog.photoUris,
        actual_start_at: queuedLog.actualStartAt ?? undefined,
        actual_end_at: queuedLog.actualEndAt ?? undefined,
        metadata: queuedLog.metadata,
        client_uuid: queuedLog.localId,
      });

      await this.markSynced(localId, serverLog.id);
      return { success: true, queuedLog, serverLog };
    } catch (err) {
      const error = err instanceof Error ? err.message : 'Unknown error';
      const isConflict = errorCodeFrom(err) === 'TASK_TERMINAL_STATE_CONFLICT';

      if (isConflict) {
        await this.markConflict(localId, error);
      } else {
        await this.markFailed(localId, error);
      }

      return {
        success: false,
        queuedLog: {
          ...queuedLog,
          syncStatus: isConflict ? 'conflict' : 'failed',
          retryCount: isConflict ? queuedLog.retryCount : queuedLog.retryCount + 1,
        },
        error,
        isConflict,
      };
    }
  }

  async processQueue(): Promise<void> {
    if (this.isProcessing) return;
    this.isProcessing = true;

    try {
      const pending = await this.getQueue();
      const toRetry = pending.filter(
        q => (q.syncStatus === 'pending' || q.syncStatus === 'failed') && q.retryCount < API_CONFIG.MAX_RETRIES
      );

      for (const q of toRetry) {
        await this.retry(q.localId);
        await new Promise(resolve => setTimeout(resolve, API_CONFIG.RETRY_DELAY_MS));
      }
    } finally {
      this.isProcessing = false;
    }
  }

  async cleanSynced(): Promise<void> {
    const stored = await this.getQueue();
    const filtered = stored.filter(q => q.syncStatus !== 'synced');
    await this.saveQueue(filtered);
  }

  private async saveQueue(queue: QueuedLog[]): Promise<void> {
    await AsyncStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
  }
}

export const offlineQueueService = new OfflineQueueService();
