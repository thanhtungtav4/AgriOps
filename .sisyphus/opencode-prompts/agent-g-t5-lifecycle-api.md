# Agent G T5 Lifecycle API Prompt

You are Agent G T5 Lifecycle API for AgriOps.

Work in `/Users/macbook/Herd/ariops`.

## Mission

Implement the narrow PlantingBatch lifecycle API/service slice on top of the existing Task 5 schema foundation.

## Sources To Read

- `.sisyphus/evidence/task-05-batch-foundation.md`
- `.sisyphus/evidence/business-rules-v1.md`
- `.sisyphus/evidence/release-risk-log.md`
- `app/Models/PlantingBatch.php`
- `app/Models/PlantingBatchAllocation.php`
- `routes/api.php`
- existing API controllers/tests

## Owns

- `app/Services/PlantingBatchLifecycleService.php`
- `app/Http/Controllers/Api/V1/PlantingBatchController.php`
- `tests/Feature/PlantingBatchApiTest.php`
- Evidence: `.sisyphus/evidence/task-05-lifecycle-api.md`

## May Touch With Care

- `routes/api.php` only to add PlantingBatch routes under existing `auth:sanctum` + `farm.scope`.
- `app/Models/PlantingBatch.php` only for constants/helper methods needed by lifecycle service.

## Must Not Touch

- Planning formulas
- Seeders
- Auth/token middleware
- Existing Filament resources
- Allocation guard service implementation beyond using existing relationships
- `.sisyphus/agent-board.md`
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`

## Requirements

1. Add list/show/create endpoints for planting batches.
2. Enforce farm scope: non-admin users can only list/show/create for their own farm.
3. Add lifecycle transition endpoint or action method with conservative allowed transitions:
   - `planned -> approved`
   - `approved -> soil_prep`
   - `soil_prep -> planting`
   - `planting -> growing`
   - `growing -> flowering`
   - `flowering -> fruiting`
   - `fruiting -> harvesting`
   - `harvesting -> completed`
   - any non-terminal active state -> `cancelled`
4. Invalid transitions return JSON 422 with stable error code.
5. Sensitive transitions should include a required `reason` when moving to `cancelled`.
6. Tests must cover happy path create/list/show, farm scope denial, valid transition, invalid transition, and cancellation reason.

## Verification

Run:

```bash
rtk php artisan test tests/Feature/PlantingBatchApiTest.php
rtk php artisan test
```

Write evidence with files changed, commands, results, open risks, and next step.

