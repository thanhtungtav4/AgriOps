# Agent L - Work Task API

You are Agent L for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Implement the Task 6 mobile-facing Work Task API slice for listing, viewing, generating, and changing task status. Keep controllers thin and use existing domain/model conventions.

## Context

- This is a Laravel project.
- Use `rtk` before every command.
- Existing foundations:
  - `app/Models/WorkTask.php`
  - `app/Services/WorkTaskGenerationService.php`
  - `app/Models/PlantingBatch.php`
  - `routes/api.php`
  - `app/Http/Responses/ApiResponse.php`
  - farm scope middleware and existing API controllers.
- Follow current API response format: `{ data, meta }` on success, `{ error }` on failure.
- Non-admin users must only access tasks in their own `farm_id`.

## Ownership

You own:

- `app/Http/Controllers/Api/V1/WorkTaskController.php`
- `app/Services/WorkTaskStatusService.php` if a service is useful
- `tests/Feature/WorkTaskApiTest.php`
- small route additions in `routes/api.php`
- `.sisyphus/evidence/task-06-work-task-api.md`

Do not touch:

- farming log schema/model/controller
- migrations unless absolutely required for API status behavior
- seeders
- Filament resources
- planning/batch/allocation services except read-only inspection

## Required API Behavior

Add authenticated, farm-scoped routes:

- `GET /api/v1/work-tasks`
  - filters: `status`, `assigned_user_id`, `planting_batch_id`, `due_before`
  - default order: planned due date asc, then id asc
  - response must include enough fields for mobile "today task": task, batch/crop, plot/bed, assignment, planned dates, status, priority.
- `GET /api/v1/work-tasks/{id}`
- `POST /api/v1/planting-batches/{id}/generate-work-tasks`
  - optional `allocation_id`
  - calls `WorkTaskGenerationService`
  - returns generated/existing/skipped counts and tasks
- `PATCH /api/v1/work-tasks/{id}/status`
  - allowed transitions:
    - `planned` -> `assigned`, `cancelled`
    - `assigned` -> `in_progress`, `cancelled`
    - `in_progress` -> `done`, `cancelled`
    - `done` and `cancelled` are terminal
  - `assigned_user_id` required when transitioning to `assigned`
  - `completion_note` optional when transitioning to `done`
  - `reason` required when transitioning to `cancelled`
  - `started_at` set when entering `in_progress` if empty
  - `completed_at` set when entering `done` if empty

## Tests

Create feature tests covering:

- worker/farm manager can list only own farm tasks
- admin can list all farm tasks
- user cannot view/update another farm task
- batch generate endpoint creates tasks from growth stages
- generate endpoint is idempotent
- status happy transitions set assignment/start/complete timestamps
- invalid transition returns 422 domain error
- assigning requires `assigned_user_id`
- cancellation requires reason
- list response includes batch/crop and plot context

Run:

```bash
rtk php artisan test tests/Feature/WorkTaskApiTest.php
rtk php artisan test
```

## Evidence

Write `.sisyphus/evidence/task-06-work-task-api.md` with:

- files changed
- route contract
- tests run and counts
- remaining risks

