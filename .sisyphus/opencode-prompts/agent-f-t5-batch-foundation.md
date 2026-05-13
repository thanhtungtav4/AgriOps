# Agent F T5 Batch Foundation Prompt

You are Agent F Production Lifecycle Foundation for AgriOps.

Work in `/Users/macbook/Herd/ariops`.

## Mission

Prepare the MVP-1 Task 5 database/domain foundation for planting batches and allocations. Keep this as a narrow foundation slice; do not build the whole lifecycle UI/API unless explicitly necessary for tests.

## Sources To Read

- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`
- `.sisyphus/evidence/business-rules-v1.md`
- `.sisyphus/evidence/final-f2c-db-architecture.md`
- `.sisyphus/evidence/release-risk-log.md`
- existing models/migrations/tests

## Owns

- New migrations for:
  - `planting_batches`
  - `planting_batch_allocations`
- New models:
  - `app/Models/PlantingBatch.php`
  - `app/Models/PlantingBatchAllocation.php`
- Focused domain tests:
  - `tests/Feature/PlantingBatchFoundationTest.php`
- Evidence:
  - `.sisyphus/evidence/task-05-batch-foundation.md`

## Must Not Touch

- API controllers
- Auth/security code
- Seeders
- Planning formula internals
- Filament resources unless a model relationship requires no UI change
- `.sisyphus/agent-board.md`
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`

## Implementation Requirements

1. Support one planting batch allocated to many plots/beds.
2. Keep planned data separate from actual data.
3. Include farm, crop, optional variety, optional production plan references.
4. Add status fields using conservative enum/check strategy that fits existing migrations.
5. Add FK indexes and composite indexes needed for farm/status/date filters.
6. Add tests verifying:
   - batch can have multiple allocations
   - allocation cannot orphan from batch/farm/plot
   - planned quantity/area remains separate from actual fields

## Verification

Run:

```bash
rtk php artisan test tests/Feature/PlantingBatchFoundationTest.php
rtk php artisan test
```

Write evidence with commands, results, files changed, and open risks.

