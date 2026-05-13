export interface Farm {
  id: number;
  name: string;
  code: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  is_admin: boolean;
  can_approve: boolean;
  farm_id: number | null;
  farm: Farm | null;
  scope: 'global' | 'farm';
  last_login_at: string | null;
}

export interface AuthResponse {
  token: string;
  token_type: string;
  expires_at: string;
  user: User;
}

export interface Crop {
  id: number;
  name: string;
  code: string | null;
}

export interface PlantingBatch {
  id: number;
  code: string;
  crop: Crop | null;
  status: string;
  planned_start_date: string | null;
  planned_harvest_date: string | null;
}

export interface Plot {
  id: number;
  name: string;
  code: string | null;
}

export interface Bed {
  id: number;
  name: string;
  code: string | null;
}

export interface WorkTask {
  id: number;
  farm_id: number;
  planting_batch_id: number;
  planting_batch_allocation_id: number | null;
  plot_id: number | null;
  bed_id: number | null;
  assigned_user_id: number | null;
  title: string;
  instructions: string | null;
  status: WorkTaskStatus;
  priority: 'low' | 'normal' | 'high' | 'urgent';
  planned_start_date: string | null;
  planned_due_date: string;
  started_at: string | null;
  completed_at: string | null;
  completion_note: string | null;
  planting_batch: PlantingBatch | null;
  plot: Plot | null;
  bed: Bed | null;
  assigned_user: User | null;
  growth_stage: { id: number; name: string } | null;
  metadata: {
    requires_photo?: boolean;
    [key: string]: unknown;
  };
  created_at: string;
  updated_at: string;
}

export type WorkTaskStatus = 'planned' | 'assigned' | 'in_progress' | 'done' | 'cancelled';

export interface FarmingLog {
  id: number;
  farm_id: number;
  work_task_id: number;
  planting_batch_id: number;
  planting_batch_allocation_id: number | null;
  plot_id: number | null;
  bed_id: number | null;
  reported_by_user_id: number;
  logged_at: string;
  actual_start_at: string | null;
  actual_end_at: string | null;
  notes: string | null;
  photo_paths: string[];
  metadata: Record<string, unknown>;
  created_at: string;
  updated_at: string;
}

export type SyncStatus = 'pending' | 'uploading' | 'synced' | 'failed' | 'conflict';

export interface QueuedLog {
  localId: string;
  taskId: number;
  notes: string | null;
  photoUris: string[];
  loggedAt: string;
  actualStartAt: string | null;
  actualEndAt: string | null;
  metadata: Record<string, unknown>;
  syncStatus: SyncStatus;
  retryCount: number;
  lastAttempt: string | null;
  errorMessage: string | null;
  serverLogId: number | null;
}

export interface ApiError {
  message: string;
  code?: string;
  errors?: Record<string, string[]>;
}

export interface ApiResponse<T> {
  data: T;
  meta?: {
    trace_id?: string;
    [key: string]: unknown;
  };
}
