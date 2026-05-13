# Agent M - Farming Log Foundation

You are Agent M for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Implement the Task 6 farming log database/model foundation for worker task completion logs and photo metadata. This prepares the mobile log submission API without blocking Agent L's work task API.

## Context

- This is a Laravel project.
- Use `rtk` before every command.
- Existing foundations:
  - `app/Models/WorkTask.php`
  - `database/migrations/2026_05_13_000001_create_work_tasks_table.php`
  - `app/Models/User.php`
  - `app/Models/Farm.php`, `Plot.php`, `Bed.php`, `PlantingBatch.php`
- Follow existing migration/model/test style.

## Ownership

You own:

- migration for `farming_logs`
- `app/Models/FarmingLog.php`
- relationship additions:
  - `WorkTask::farmingLogs()`
  - `User::farmingLogs()` if needed
- `tests/Feature/FarmingLogFoundationTest.php`
- `.sisyphus/evidence/task-06-farming-log-foundation.md`

Do not touch:

- routes/api.php
- API controllers
- WorkTaskGenerationService
- WorkTask API controller/service
- seeders
- Filament resources

## Required Schema

Create `farming_logs` with fields:

- `id`
- `farm_id` required FK cascade
- `work_task_id` required FK cascade
- `planting_batch_id` nullable FK cascade
- `planting_batch_allocation_id` nullable FK set null
- `plot_id` nullable FK cascade
- `bed_id` nullable FK cascade
- `reported_by_user_id` required FK users set null or restrict if nullable is needed
- `logged_at` timestamp required
- `status` string default `submitted`
- `actual_start_at` nullable timestamp
- `actual_end_at` nullable timestamp
- `notes` nullable text
- `photo_paths` nullable json
- `metadata` nullable json
- timestamps

Indexes:

- `[farm_id, logged_at]`
- `[work_task_id, logged_at]`
- `[reported_by_user_id, logged_at]`
- `[planting_batch_id, logged_at]`

## Model Behavior

- Fillable for all log fields.
- Cast `logged_at`, actual timestamps to datetime; `photo_paths` and `metadata` to array.
- Constants:
  - statuses: `draft`, `submitted`, `approved`, `rejected`
- Relationships:
  - farm, workTask, plantingBatch, allocation, plot, bed, reportedByUser
- `WorkTask::farmingLogs()` hasMany.

## Tests

Create feature tests covering:

- farming log can be created with required fields
- defaults `status=submitted`
- JSON photo paths and metadata cast to arrays
- belongs to farm/task/reporter/batch/plot
- deleting work task deletes logs
- deleting allocation nullifies allocation on logs if FK allows set null
- WorkTask has many farming logs
- indexes/migration pass via `migrate:fresh --seed`

Run:

```bash
rtk php artisan test tests/Feature/FarmingLogFoundationTest.php
rtk php artisan test
```

## Evidence

Write `.sisyphus/evidence/task-06-farming-log-foundation.md` with:

- files changed
- schema summary
- tests run and counts
- remaining risks

