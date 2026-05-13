# Task 06 - Farming Log Foundation - Evidence

## Files Changed

| File | Action |
|------|--------|
| `database/migrations/2026_05_13_000002_create_farming_logs_table.php` | Created |
| `app/Models/FarmingLog.php` | Created |
| `app/Models/WorkTask.php` | Modified (added farmingLogs relationship) |
| `tests/Feature/FarmingLogFoundationTest.php` | Created |

## Schema Summary

### `farming_logs` Table

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| farm_id | bigint | FK → farms(id) ON DELETE CASCADE |
| work_task_id | bigint | FK → work_tasks(id) ON DELETE CASCADE |
| planting_batch_id | bigint | FK → planting_batches(id) ON DELETE CASCADE, nullable |
| planting_batch_allocation_id | bigint | FK → planting_batch_allocations(id) ON DELETE SET NULL, nullable |
| plot_id | bigint | FK → plots(id) ON DELETE CASCADE, nullable |
| bed_id | bigint | FK → beds(id) ON DELETE CASCADE, nullable |
| reported_by_user_id | bigint | FK → users(id) ON DELETE SET NULL, nullable |
| logged_at | timestamp | NOT NULL |
| status | varchar(20) | DEFAULT 'submitted' |
| actual_start_at | timestamp | nullable |
| actual_end_at | timestamp | nullable |
| notes | text | nullable |
| photo_paths | json | nullable |
| metadata | json | nullable |
| timestamps | timestamp | created_at, updated_at |

### Indexes

- `[farm_id, logged_at]`
- `[work_task_id, logged_at]`
- `[reported_by_user_id, logged_at]`
- `[planting_batch_id, logged_at]`

### FarmingLog Model Behavior

- Fillable: all log fields
- Casts: `logged_at`, `actual_start_at`, `actual_end_at` → datetime; `photo_paths`, `metadata` → array
- Constants: `STATUSES = ['draft', 'submitted', 'approved', 'rejected']`
- Default status: `submitted`
- Relationships: farm, workTask, plantingBatch, allocation, plot, bed, reportedByUser

### WorkTask Relationship

Added `farmingLogs(): HasMany` relationship to `WorkTask` model.

## Tests Run

### FarmingLogFoundationTest: 18 tests, 18 passed, 45 assertions

```
php artisan test tests/Feature/FarmingLogFoundationTest.php
PASSED (18 tests, 45 assertions, 267ms)
```

### Test Coverage

| Test | Status |
|------|--------|
| test_farming_log_can_be_created_with_required_fields | PASS |
| test_farming_log_defaults_status_to_submitted | PASS |
| test_farming_log_has_json_cast_on_photo_paths | PASS |
| test_farming_log_has_json_cast_on_metadata | PASS |
| test_farming_log_belongs_to_farm | PASS |
| test_farming_log_belongs_to_work_task | PASS |
| test_farming_log_belongs_to_reported_by_user | PASS |
| test_farming_log_belongs_to_planting_batch | PASS |
| test_farming_log_belongs_to_plot | PASS |
| test_farming_log_belongs_to_bed | PASS |
| test_farming_log_has_datetime_cast_on_logged_at | PASS |
| test_farming_log_has_actual_timestamps | PASS |
| test_farming_log_status_enum_values | PASS |
| test_farming_log_cascade_deletes_when_work_task_deleted | PASS |
| test_farming_log_nullifies_when_allocation_deleted | PASS |
| test_work_task_has_many_farming_logs | PASS |
| test_farming_log_model_has_relationships | PASS |
| test_work_task_model_has_farming_logs_relationship | PASS |

## Full Test Suite

```
php artisan test
PASSED (191 tests, 588 assertions)
```

The earlier `WorkTaskApiTest` fixture issue was resolved during integrator review. Final verification is clean.

## Remaining Risks

1. **No log submission API yet**: This implementation provides the model foundation only. `POST /api/v1/work-tasks/{id}/logs` remains the next slice.

2. **No Filament resources**: No admin panel resources created as per scope.

3. **Photo storage contract only stores paths**: Actual upload/storage validation still belongs to the log submission API slice.

## Verification

- Migration created and runs successfully
- Model has all required fillable, casts, constants, relationships
- WorkTask::farmingLogs() relationship works
- All 18 tests pass with 45 assertions
- Indexes created on migration
- FK cascade/set null behavior verified in tests
