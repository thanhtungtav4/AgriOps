# Agent K Task Generation Service Prompt

You are Agent K Task Generation Service for AgriOps.

Work in `/Users/macbook/Herd/ariops`.

## Mission

Implement service-level task generation from a planting batch and growth stages. This depends on Agent J's `work_tasks` foundation, so if the schema/model does not exist yet, wait by checking files briefly, then proceed once available.

## Sources To Read

- `.sisyphus/evidence/task-05-lifecycle-api.md`
- `.sisyphus/evidence/business-rules-v1.md`
- `app/Models/PlantingBatch.php`
- `app/Models/GrowthStage.php`
- `app/Models/WorkTask.php` when available
- `tests/Feature/WorkTaskSchemaTest.php` when available

## Owns

- `app/Services/WorkTaskGenerationService.php`
- `tests/Feature/WorkTaskGenerationServiceTest.php`
- Evidence: `.sisyphus/evidence/task-06-task-generation-service.md`

## May Touch With Care

- `app/Models/WorkTask.php` only for helper constants/scopes if useful.
- `app/Models/PlantingBatch.php` only for relationships if Agent J did not add them.

## Must Not Touch

- Migrations unless only fixing a tiny issue in the just-created `work_tasks` migration after confirming with tests.
- API controllers/routes
- Auth/security code
- Seeders
- Planning formulas
- Filament resources
- `.sisyphus/agent-board.md`
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`

## Requirements

1. Generate tasks from `GrowthStage` records for batch crop/variety.
2. Create at least one planned task per growth stage with:
   - farm, batch, optional allocation/plot if provided
   - title derived from stage name
   - task_type derived from stage code/name, conservative string is OK
   - planned dates based on batch `planned_start_date` and cumulative stage durations
3. Generation must be idempotent: running twice for the same batch must not duplicate tasks.
4. Support optional allocation target to generate plot/bed-specific tasks.
5. Return generated/existing task collection and summary counts.
6. Tests must cover happy path, idempotency, missing growth stages, and allocation-targeted generation.

## Verification

Run:

```bash
rtk php artisan test tests/Feature/WorkTaskGenerationServiceTest.php
rtk php artisan test
```

Write evidence with files changed, commands, results, open risks, and next step.

