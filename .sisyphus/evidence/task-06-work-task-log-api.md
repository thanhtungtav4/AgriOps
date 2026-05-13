# Task 6 Evidence: Work Task Log API

## Files Changed

| File | Action |
|------|--------|
| `app/Http/Controllers/Api/V1/WorkTaskLogController.php` | Created log submission endpoint |
| `routes/api.php` | Added `POST /api/v1/work-tasks/{id}/logs` |
| `tests/Feature/WorkTaskLogApiTest.php` | Created API coverage |

## Route Contract

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/v1/work-tasks/{id}/logs` | Submit actual farming log for a work task | farm-scoped |

## Behavior

- Prefills `farm_id`, `work_task_id`, `planting_batch_id`, `allocation_id`, `plot_id`, `bed_id`, and `reported_by_user_id` from the task and authenticated user.
- Accepts `notes`, `actual_start_at`, `actual_end_at`, `photo_paths`, multipart `photos[]`, and `metadata`.
- Rejects cross-farm log submission.
- Rejects logs for cancelled tasks.
- Stores uploaded photos on the `public` disk under `work-task-logs/{task_id}` and records `/storage/...` paths.
- Requires either `photo_paths` or uploaded `photos[]` when task metadata has `requires_photo=true`.
- Marks the task `done`, sets `completed_at`, and copies notes into `completion_note`.

## Tests Run

```bash
rtk php artisan test tests/Feature/WorkTaskLogApiTest.php
```

Result: `8 tests, 29 assertions`.

```bash
rtk php artisan test
```

Result: `191 tests, 588 assertions`.

## Remaining Risks

- No approval/rejection workflow for submitted logs yet.
- No audit event table yet; log creation currently serves as operational evidence only.
