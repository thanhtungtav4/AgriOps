# Task 6 Evidence: Work Task API

## Files Changed

### New Files Created
- `app/Http/Controllers/Api/V1/WorkTaskController.php` - Main controller with all endpoints
- `app/Services/WorkTaskStatusService.php` - Domain service for status transitions
- `tests/Feature/WorkTaskApiTest.php` - Feature tests covering all API behavior

### Modified Files
- `routes/api.php` - Added routes for work-tasks endpoints

## Route Contract

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/work-tasks` | List tasks with filters | farm-scoped |
| GET | `/api/v1/work-tasks/{id}` | View single task | farm-scoped |
| PATCH | `/api/v1/work-tasks/{id}/status` | Update task status | farm-scoped |
| POST | `/api/v1/planting-batches/{id}/generate-work-tasks` | Generate work tasks from batch | farm-scoped |

### List Filters
- `status` - Filter by task status (planned, assigned, in_progress, done, cancelled)
- `assigned_user_id` - Filter by assigned user
- `planting_batch_id` - Filter by planting batch
- `due_before` - Filter tasks due before date

### Status Transition Rules
| From | Allowed To |
|------|------------|
| planned | assigned, cancelled |
| assigned | in_progress, cancelled |
| in_progress | done, cancelled |
| done | (terminal) |
| cancelled | (terminal) |

### Status Transition Requirements
- `planned -> assigned`: requires `assigned_user_id`
- `in_progress -> done`: optionally sets `completed_at`, allows `completion_note`
- `* -> cancelled`: requires `reason`

### Automatic Timestamps
- `started_at` set when entering `in_progress` (if empty)
- `completed_at` set when entering `done` (if empty)

## Tests Run

```
php artisan test tests/Feature/WorkTaskApiTest.php
```

**Results:**
- Tests: 29
- Passed: 29
- Assertions: 87
- Duration: 609ms

### Test Coverage
- `test_worker_can_list_only_own_farm_tasks` - Farm scope enforcement
- `test_admin_can_list_all_farm_tasks` - Admin bypasses farm scope
- `test_list_includes_batch_crop_and_plot_context` - Mobile "today task" data included
- `test_list_filter_by_status` - Status filtering
- `test_list_filter_by_assigned_user` - User filtering
- `test_list_filter_by_planting_batch` - Batch filtering
- `test_list_filter_by_due_before` - Due date filtering
- `test_list_default_order` - planned_due_date ASC, id ASC
- `test_user_can_view_own_farm_task` - Show endpoint access
- `test_user_cannot_view_another_farm_task` - 403 for cross-farm access
- `test_admin_can_view_any_task` - Admin access
- `test_show_returns_404_for_nonexistent_task` - 404 handling
- `test_batch_generate_creates_tasks_from_growth_stages` - Generation creates tasks
- `test_generate_endpoint_is_idempotent` - Re-running doesn't duplicate
- `test_generate_with_allocation_id` - Allocation linking
- `test_generate_rejects_allocation_from_another_batch` - Batch/allocation guard
- `test_generate_requires_existing_batch` - 404 for missing batch
- `test_planned_to_assigned_requires_assigned_user_id` - Validation
- `test_planned_to_assigned_success` - Happy path
- `test_assignment_rejects_user_from_another_farm` - Assignee farm isolation
- `test_assigned_to_in_progress_sets_started_at` - Timestamp auto-set
- `test_in_progress_to_done_sets_completed_at` - Completion timestamp
- `test_planned_to_cancelled_requires_reason` - Cancellation validation
- `test_cancelled_success` - Cancellation happy path
- `test_invalid_transition_returns_422` - Domain error handling
- `test_done_is_terminal` - Done cannot transition again
- `test_cancelled_is_terminal` - Cancelled cannot transition again
- `test_user_cannot_update_another_farm_task_status` - Farm isolation on update
- `test_admin_can_update_any_task_status` - Admin update bypass

### Full Suite
```
php artisan test
```
- Tests: 189
- Passed: 189
- Assertions: 588
- Duration: 1988ms

## Remaining Risks

1. **No factory for test data** - Using direct Model::create() in tests. Consider extracting to factory if test setup grows.

2. **Performance** - No pagination on list endpoint. Large farms with many tasks may experience slow responses.

3. **Concurrent status updates** - No optimistic locking or version checking. Two simultaneous status transitions could cause race conditions.

4. **Audit trail** - Status changes don't create audit records. Consider farming_logs for compliance tracking.

5. **Binary photo upload pending** - Log endpoint stores validated photo paths, but does not yet upload/store files.
