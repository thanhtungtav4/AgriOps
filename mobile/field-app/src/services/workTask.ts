import { getApiBaseUrl } from '../config/api';
import type { WorkTask, ApiResponse } from '../types';
import { authService } from './auth';

export interface TaskFilters {
  status?: WorkTask['status'];
  assigned_user_id?: number;
  planting_batch_id?: number;
  due_before?: string;
}

export interface TaskListResponse {
  data: WorkTask[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

class WorkTaskService {
  private async request<T>(path: string, options?: RequestInit): Promise<T> {
    const response = await fetch(`${getApiBaseUrl()}${path}`, {
      ...options,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...authService.getAuthHeader(),
        ...options?.headers,
      },
    });

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Request failed' }));
      throw new Error(error.message || 'Request failed');
    }

    return response.json();
  }

  async getTasks(filters?: TaskFilters): Promise<TaskListResponse> {
    const params = new URLSearchParams();
    if (filters?.status) params.append('status', filters.status);
    if (filters?.assigned_user_id) params.append('assigned_user_id', String(filters.assigned_user_id));
    if (filters?.planting_batch_id) params.append('planting_batch_id', String(filters.planting_batch_id));
    if (filters?.due_before) params.append('due_before', filters.due_before);

    const query = params.toString();
    return this.request<TaskListResponse>(`/work-tasks${query ? `?${query}` : ''}`);
  }

  async getTodayTasks(): Promise<WorkTask[]> {
    const today = new Date().toISOString().split('T')[0];
    const userId = authService.getState().user?.id;
    if (!userId) throw new Error('Not authenticated');

    const response = await this.getTasks({ assigned_user_id: userId, due_before: today });
    return response.data.filter(task => !['done', 'cancelled'].includes(task.status));
  }

  async getMyTasks(): Promise<WorkTask[]> {
    const state = authService.getState();
    const userId = state.user?.id;
    if (!userId) throw new Error('Not authenticated');

    const response = await this.getTasks({ assigned_user_id: userId });
    return response.data;
  }

  async getTask(id: number): Promise<WorkTask> {
    const response = await this.request<ApiResponse<WorkTask>>(`/work-tasks/${id}`);
    return response.data;
  }

  async acceptTask(id: number): Promise<WorkTask> {
    return this.updateTaskStatus(id, 'assigned');
  }

  async startTask(id: number): Promise<WorkTask> {
    return this.updateTaskStatus(id, 'in_progress');
  }

  async completeTask(id: number, completionNote?: string): Promise<WorkTask> {
    const response = await fetch(`${getApiBaseUrl()}/work-tasks/${id}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...authService.getAuthHeader(),
      },
      body: JSON.stringify({
        status: 'done',
        completion_note: completionNote,
      }),
    });

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Request failed' }));
      throw new Error(error.message || 'Request failed');
    }

    const data: ApiResponse<WorkTask> = await response.json();
    return data.data;
  }

  private async updateTaskStatus(id: number, status: WorkTask['status']): Promise<WorkTask> {
    const payload: Record<string, unknown> = { status };

    if (status === 'assigned') {
      const state = authService.getState();
      payload.assigned_user_id = state.user?.id;
    }

    const response = await fetch(`${getApiBaseUrl()}/work-tasks/${id}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...authService.getAuthHeader(),
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Request failed' }));
      throw new Error(error.message || 'Request failed');
    }

    const data: ApiResponse<WorkTask> = await response.json();
    return data.data;
  }
}

export const workTaskService = new WorkTaskService();
