# Agent H Allocation Guards Prompt

You are Agent H Allocation Guards for AgriOps.

Work in `/Users/macbook/Herd/ariops`.

## Mission

Implement allocation guard logic for planting batch allocations without adding broad API surface. Focus on domain service and tests.

## Sources To Read

- `.sisyphus/evidence/task-05-batch-foundation.md`
- `.sisyphus/evidence/business-rules-v1.md`
- `.sisyphus/evidence/final-f2c-db-architecture.md`
- `app/Models/PlantingBatch.php`
- `app/Models/PlantingBatchAllocation.php`
- `app/Models/Plot.php`
- `app/Models/Bed.php`
- existing tests

## Owns

- `app/Services/PlantingBatchAllocationService.php`
- `tests/Feature/PlantingBatchAllocationGuardTest.php`
- Evidence: `.sisyphus/evidence/task-05-allocation-guards.md`

## May Touch With Care

- `app/Models/PlantingBatch.php`
- `app/Models/PlantingBatchAllocation.php`

## Must Not Touch

- API controllers/routes
- Auth/security code
- Seeders
- Planning formulas
- Filament resources
- `.sisyphus/agent-board.md`
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`

## Requirements

1. Allocation must reject plot from a different farm than the batch.
2. Allocation must reject inactive/unavailable/suspended plots unless explicitly allowed by service option.
3. Allocation must reject allocated area greater than plot area.
4. Allocation must reject duplicate batch/plot/bed allocation.
5. Allocation service must return or throw domain-specific errors that tests can assert.
6. Tests must cover multi-plot allocation still works for same farm.

## Verification

Run:

```bash
rtk php artisan test tests/Feature/PlantingBatchAllocationGuardTest.php
rtk php artisan test
```

Write evidence with files changed, commands, results, open risks, and next step.

