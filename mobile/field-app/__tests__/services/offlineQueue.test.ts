import { offlineQueueService } from '../../src/services/farmingLog';
import AsyncStorage from '@react-native-async-storage/async-storage';

describe('OfflineQueueService', () => {
  beforeEach(async () => {
    jest.clearAllMocks();
    await AsyncStorage.clear();
  });

  describe('enqueue', () => {
    it('should add new log to queue', async () => {
      const log = {
        localId: 'test-uuid-1',
        taskId: 1,
        notes: 'Test note',
        photoUris: [],
        loggedAt: new Date().toISOString(),
        actualStartAt: null,
        actualEndAt: null,
        metadata: {},
      };

      const result = await offlineQueueService.enqueue(log);

      expect(result.localId).toBe('test-uuid-1');
      expect(result.syncStatus).toBe('pending');
      expect(result.retryCount).toBe(0);
    });

    it('should not duplicate if localId already exists', async () => {
      const log = {
        localId: 'test-uuid-1',
        taskId: 1,
        notes: 'Test note',
        photoUris: [],
        loggedAt: new Date().toISOString(),
        actualStartAt: null,
        actualEndAt: null,
        metadata: {},
      };

      await offlineQueueService.enqueue(log);
      const result = await offlineQueueService.enqueue(log);

      expect(result.localId).toBe('test-uuid-1');
      const queue = await offlineQueueService.getQueue();
      expect(queue.length).toBe(1);
    });
  });

  describe('getQueue', () => {
    it('should return empty array when no queue exists', async () => {
      const queue = await offlineQueueService.getQueue();
      expect(queue).toEqual([]);
    });

    it('should return queued logs from storage', async () => {
      const stored = [
        {
          localId: 'test-1',
          taskId: 1,
          notes: null,
          photoUris: [],
          loggedAt: new Date().toISOString(),
          actualStartAt: null,
          actualEndAt: null,
          metadata: {},
          syncStatus: 'pending' as const,
          retryCount: 0,
          lastAttempt: null,
          errorMessage: null,
          serverLogId: null,
        },
      ];
      await AsyncStorage.setItem('@ariops:log_queue', JSON.stringify(stored));

      const queue = await offlineQueueService.getQueue();
      expect(queue.length).toBe(1);
      expect(queue[0].localId).toBe('test-1');
    });
  });

  describe('markSynced', () => {
    it('should update log status to synced', async () => {
      const log = {
        localId: 'test-1',
        taskId: 1,
        notes: null,
        photoUris: [],
        loggedAt: new Date().toISOString(),
        actualStartAt: null,
        actualEndAt: null,
        metadata: {},
        syncStatus: 'pending' as const,
        retryCount: 0,
        lastAttempt: null,
        errorMessage: null,
        serverLogId: null,
      };
      await AsyncStorage.setItem('@ariops:log_queue', JSON.stringify([log]));

      await offlineQueueService.markSynced('test-1', 123);

      const queue = await offlineQueueService.getQueue();
      expect(queue[0].syncStatus).toBe('synced');
      expect(queue[0].serverLogId).toBe(123);
    });
  });

  describe('markFailed', () => {
    it('should update log status to failed and increment retry count', async () => {
      const log = {
        localId: 'test-1',
        taskId: 1,
        notes: null,
        photoUris: [],
        loggedAt: new Date().toISOString(),
        actualStartAt: null,
        actualEndAt: null,
        metadata: {},
        syncStatus: 'pending' as const,
        retryCount: 0,
        lastAttempt: null,
        errorMessage: null,
        serverLogId: null,
      };
      await AsyncStorage.setItem('@ariops:log_queue', JSON.stringify([log]));

      await offlineQueueService.markFailed('test-1', 'Network error');

      const queue = await offlineQueueService.getQueue();
      expect(queue[0].syncStatus).toBe('failed');
      expect(queue[0].retryCount).toBe(1);
      expect(queue[0].errorMessage).toBe('Network error');
    });
  });

  describe('cleanSynced', () => {
    it('should remove synced logs from queue', async () => {
      const logs = [
        {
          localId: 'synced-1',
          taskId: 1,
          notes: null,
          photoUris: [],
          loggedAt: new Date().toISOString(),
          actualStartAt: null,
          actualEndAt: null,
          metadata: {},
          syncStatus: 'synced' as const,
          retryCount: 1,
          lastAttempt: new Date().toISOString(),
          errorMessage: null,
          serverLogId: 1,
        },
        {
          localId: 'pending-1',
          taskId: 2,
          notes: null,
          photoUris: [],
          loggedAt: new Date().toISOString(),
          actualStartAt: null,
          actualEndAt: null,
          metadata: {},
          syncStatus: 'pending' as const,
          retryCount: 0,
          lastAttempt: null,
          errorMessage: null,
          serverLogId: null,
        },
      ];
      await AsyncStorage.setItem('@ariops:log_queue', JSON.stringify(logs));

      await offlineQueueService.cleanSynced();

      const queue = await offlineQueueService.getQueue();
      expect(queue.length).toBe(1);
      expect(queue[0].localId).toBe('pending-1');
    });
  });
});