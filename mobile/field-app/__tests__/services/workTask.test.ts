import { workTaskService } from '../../src/services/workTask';
import { authService } from '../../src/services/auth';
import AsyncStorage from '@react-native-async-storage/async-storage';

const mockFetch = global.fetch as jest.MockedFunction<typeof fetch>;

describe('WorkTaskService', () => {
  beforeEach(async () => {
    jest.clearAllMocks();
    await AsyncStorage.clear();
    await AsyncStorage.setItem('@ariops:auth_token', 'test-token');
    await AsyncStorage.setItem('@ariops:auth_user', JSON.stringify({
      id: 42,
      name: 'Field Worker',
      email: 'worker@farm.example',
      role: 'worker',
      is_admin: false,
      can_approve: false,
      farm_id: 1,
      farm: null,
      scope: 'farm' as const,
      last_login_at: null,
    }));
    await AsyncStorage.setItem('@ariops:auth_expiry', new Date(Date.now() + 3600000).toISOString());
    await authService.initialize();
  });

  describe('getTodayTasks', () => {
    it('should fetch tasks due today for the current user', async () => {
      mockFetch.mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve({
          data: [
            {
              id: 1,
              farm_id: 1,
              planting_batch_id: 1,
              planting_batch_allocation_id: null,
              plot_id: null,
              bed_id: null,
              assigned_user_id: 42,
              title: 'Morning harvest',
              instructions: 'Harvest ripe tomatoes',
              status: 'assigned',
              priority: 'normal' as const,
              planned_start_date: null,
              planned_due_date: new Date().toISOString(),
              started_at: null,
              completed_at: null,
              completion_note: null,
              planting_batch: null,
              plot: null,
              bed: null,
              assigned_user: null,
              growth_stage: null,
              metadata: {},
              created_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            },
          ],
          meta: { current_page: 1, last_page: 1, per_page: 20, total: 1 },
        }),
      } as Response);

      const tasks = await workTaskService.getTodayTasks();

      expect(tasks).toHaveLength(1);
      expect(tasks[0].id).toBe(1);
      expect(tasks[0].title).toBe('Morning harvest');
      expect(mockFetch).toHaveBeenCalledWith(
        expect.stringContaining('/work-tasks'),
        expect.any(Object)
      );
    });

    it('should filter out completed and cancelled tasks', async () => {
      mockFetch.mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve({
          data: [
            {
              id: 1,
              farm_id: 1,
              planting_batch_id: 1,
              planting_batch_allocation_id: null,
              plot_id: null,
              bed_id: null,
              assigned_user_id: 42,
              title: 'Done task',
              instructions: null,
              status: 'done' as const,
              priority: 'normal' as const,
              planned_start_date: null,
              planned_due_date: new Date().toISOString(),
              started_at: null,
              completed_at: null,
              completion_note: null,
              planting_batch: null,
              plot: null,
              bed: null,
              assigned_user: null,
              growth_stage: null,
              metadata: {},
              created_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            },
            {
              id: 2,
              farm_id: 1,
              planting_batch_id: 1,
              planting_batch_allocation_id: null,
              plot_id: null,
              bed_id: null,
              assigned_user_id: 42,
              title: 'Active task',
              instructions: null,
              status: 'assigned' as const,
              priority: 'normal' as const,
              planned_start_date: null,
              planned_due_date: new Date().toISOString(),
              started_at: null,
              completed_at: null,
              completion_note: null,
              planting_batch: null,
              plot: null,
              bed: null,
              assigned_user: null,
              growth_stage: null,
              metadata: {},
              created_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            },
          ],
          meta: { current_page: 1, last_page: 1, per_page: 20, total: 2 },
        }),
      } as Response);

      const tasks = await workTaskService.getTodayTasks();

      expect(tasks).toHaveLength(1);
      expect(tasks[0].id).toBe(2);
      expect(tasks[0].status).toBe('assigned');
    });

    it('should throw when not authenticated', async () => {
      await AsyncStorage.clear();
      await authService.initialize();

      await expect(workTaskService.getTodayTasks()).rejects.toThrow('Not authenticated');
    });
  });

  describe('getMyTasks', () => {
    it('should fetch all tasks for the current user', async () => {
      mockFetch.mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve({
          data: [
            {
              id: 10,
              farm_id: 1,
              planting_batch_id: 1,
              planting_batch_allocation_id: null,
              plot_id: null,
              bed_id: null,
              assigned_user_id: 42,
              title: 'Weeding',
              instructions: null,
              status: 'planned' as const,
              priority: 'normal' as const,
              planned_start_date: null,
              planned_due_date: new Date().toISOString(),
              started_at: null,
              completed_at: null,
              completion_note: null,
              planting_batch: null,
              plot: null,
              bed: null,
              assigned_user: null,
              growth_stage: null,
              metadata: { requires_photo: true },
              created_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            },
          ],
          meta: { current_page: 1, last_page: 1, per_page: 20, total: 1 },
        }),
      } as Response);

      const tasks = await workTaskService.getMyTasks();

      expect(tasks).toHaveLength(1);
      expect(tasks[0].id).toBe(10);
      expect(tasks[0].metadata.requires_photo).toBe(true);
    });
  });

  describe('startTask', () => {
    it('should update task status to in_progress', async () => {
      mockFetch.mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve({
          data: {
            id: 5,
            farm_id: 1,
            planting_batch_id: 1,
            planting_batch_allocation_id: null,
            plot_id: null,
            bed_id: null,
            assigned_user_id: 42,
            title: 'Spraying',
            instructions: null,
            status: 'in_progress' as const,
            priority: 'normal' as const,
            planned_start_date: null,
            planned_due_date: new Date().toISOString(),
            started_at: new Date().toISOString(),
            completed_at: null,
            completion_note: null,
            planting_batch: null,
            plot: null,
            bed: null,
            assigned_user: null,
            growth_stage: null,
            metadata: {},
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
          },
        }),
      } as Response);

      const task = await workTaskService.startTask(5);

      expect(task.status).toBe('in_progress');
      expect(mockFetch).toHaveBeenCalledWith(
        expect.stringContaining('/work-tasks/5/status'),
        expect.objectContaining({
          method: 'PATCH',
          body: expect.stringContaining('"status":"in_progress"'),
        })
      );
    });
  });

  describe('completeTask', () => {
    it('should mark task as done with completion note', async () => {
      mockFetch.mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve({
          data: {
            id: 7,
            farm_id: 1,
            planting_batch_id: 1,
            planting_batch_allocation_id: null,
            plot_id: null,
            bed_id: null,
            assigned_user_id: 42,
            title: 'Harvest row 3',
            instructions: null,
            status: 'done' as const,
            priority: 'normal' as const,
            planned_start_date: null,
            planned_due_date: new Date().toISOString(),
            started_at: null,
            completed_at: new Date().toISOString(),
            completion_note: 'Harvested 50kg',
            planting_batch: null,
            plot: null,
            bed: null,
            assigned_user: null,
            growth_stage: null,
            metadata: {},
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
          },
        }),
      } as Response);

      const task = await workTaskService.completeTask(7, 'Harvested 50kg');

      expect(task.status).toBe('done');
      expect(task.completion_note).toBe('Harvested 50kg');
    });
  });
});
