# Agent J Work Task Schema Prompt

You are Agent J Work Task Schema for AgriOps.

Work in `/Users/macbook/Herd/ariops`.

## Mission

Create the Task 6 work task database/model foundation without adding API controllers yet.

## Sources To Read

- `.sisyphus/evidence/release-risk-log.md`
- `.sisyphus/evidence/task-05-lifecycle-api.md`
- `.sisyphus/evidence/business-rules-v1.md`
- `app/Models/PlantingBatch.php`
- `app/Models/PlantingBatchAllocation.php`
- `app/Models/GrowthStage.php`
- existing migrations and model conventions

## Owns

- Migration for `work_tasks`
- `app/Models/WorkTask.php`
- `tests/Feature/WorkTaskSchemaTest.php`
- Evidence: `.sisyphus/evidence/task-06-work-task-schema.md`

## May Touch With Care

- `app/Models/PlantingBatch.php` for relationship only.
- `app/Models/PlantingBatchAllocation.php` for relationship only.

## Must Not Touch

- API controllers/routes
- Task generation service
- Auth/security code
- Seeders
- Planning formulas
- Filament resources
- `.sisyphus/agent-board.md`
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`

## Requirements

1. `work_tasks` must support:
   - `farm_id`
   - `planting_batch_id`
   - optional `planting_batch_allocation_id`
   - optional `plot_id`
   - optional `bed_id`
   - optional `growth_stage_id`
   - optional `assigned_user_id`
   - `title`, `task_type`, `status`, `priority`
   - `planned_start_date`, `planned_due_date`
   - `started_at`, `completed_at`
   - `instructions`, `completion_note`, `metadata`
2. Status enum: `planned`, `assigned`, `in_progress`, `done`, `cancelled`.
3. Priority enum or constrained values: `low`, `normal`, `high`, `urgent`.
4. Add indexes for `farm_id + status`, `assigned_user_id + status`, `planned_due_date + status`, `planting_batch_id + status`.
5. Add model relationships and casts.
6. Tests must verify relationships, defaults, FK protection, and planned/actual timestamps remain separate.

## Verification

Run:

```bash
rtk php artisan test tests/Feature/WorkTaskSchemaTest.php
rtk php artisan test
```

Write evidence with files changed, commands, results, open risks, and next step.

