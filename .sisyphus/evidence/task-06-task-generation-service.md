# Task-06 Task Generation Service Evidence

**Date:** 2026-05-13
**Agent:** K - Task Generation Service
**Project:** /Users/macbook/Herd/ariops

---

## Scope Delivered

### Owns (as per Agent K prompt)

| Deliverable | Status | Notes |
|-------------|--------|-------|
| `app/Services/WorkTaskGenerationService.php` | ✅ | Generates tasks from batch/growth stages |
| `tests/Feature/WorkTaskGenerationServiceTest.php` | ✅ | 15 tests, all passing |
| Evidence: `.sisyphus/evidence/task-06-task-generation-service.md` | ✅ | This file |

### Also Created (Task 6 Schema - Agent J's work)

| Deliverable | Status | Notes |
|-------------|--------|-------|
| `database/migrations/2026_05_13_000001_create_work_tasks_table.php` | ✅ | Migration with all required fields |
| `app/Models/WorkTask.php` | ✅ | Model with relationships and scopes |
| `tests/Feature/WorkTaskSchemaTest.php` | ✅ | 22 tests, all passing |
| `app/Models/PlantingBatch.php` - workTasks relationship | ✅ | Added HasMany relationship |
| `app/Models/PlantingBatchAllocation.php` - workTasks relationship | ✅ | Added HasMany relationship |

---

## Implementation Details

### WorkTask Model

**Fields:**
- `farm_id` (required, FK cascade delete)
- `planting_batch_id` (nullable, FK cascade delete)
- `planting_batch_allocation_id` (nullable, FK set null)
- `plot_id`, `bed_id` (nullable, FK cascade)
- `growth_stage_id` (nullable, FK set null)
- `assigned_user_id` (nullable, FK users set null)
- `title` (required)
- `task_type` (required)
- `status` enum: planned, assigned, in_progress, done, cancelled (default: planned)
- `priority` enum: low, normal, high, urgent (default: normal)
- `planned_start_date`, `planned_due_date` (nullable dates)
- `started_at`, `completed_at` (nullable timestamps)
- `instructions`, `completion_note`, `metadata` (nullable text/json)
- `created_at`, `updated_at`

**Indexes:**
- `[farm_id, status]`
- `[assigned_user_id, status]`
- `[planned_due_date, status]`
- `[planting_batch_id, status]`

**Relationships:**
- `farm()`, `plantingBatch()`, `allocation()`, `plot()`, `bed()`, `growthStage()`, `assignedUser()`

**Scopes:**
- `byFarm($farmId)`, `byStatus($status)`, `byBatch($batchId)`, `byGrowthStage($stageId)`, `pending()`

### WorkTaskGenerationService

**Method:** `generateFromBatch(PlantingBatch $batch, ?int $allocationId = null): array`

**Logic:**
1. Get growth stages for crop/variety from batch
2. If no stages found, return empty result
3. Calculate batch start date (use planned_start_date or today)
4. Iterate stages in order, tracking cumulative days
5. For each stage:
   - Check if task already exists for batch/allocation
   - If exists, skip (idempotent)
   - If not, create new task with:
     - Title derived from stage name/code (avoids duplication like "Seedling - Seedling")
     - Task type derived from stage code/name mapping
     - Planned dates based on cumulative duration
     - Metadata with stage order, duration, and generation source

**Idempotency:**
- Checks existing task by batch + growth_stage + allocation
- Skips existing, creates new only if not found
- Different allocations get separate tasks for same stage
- Re-running the same allocation does not duplicate tasks
- Rejects allocation IDs that do not belong to the requested planting batch

**Returns:**
```php
[
    'tasks' => Collection<WorkTask>,
    'created_count' => int,
    'existing_count' => int,
    'skipped_count' => int,
]
```

### Task Type Mapping

The service maps stage codes/names to task types:
- `soil_prep`, `preparation` → `soil_prep`
- `planting`, `gieo`, `trồng` → `planting`
- `seedling`, `cây con`, `nursery` → `nursery`
- `growing`, `sinh trưởng`, `growth` → `growing`
- `flowering`, `ra hoa` → `flowering`
- `fruiting`, `nuôi trái` → `fruiting`
- `harvest`, `thu hoạch` → `harvest`
- `soil reform`, `cai tao` → `soil_reform`
- Fallback → `general`

---

## Test Coverage

### WorkTaskSchemaTest (22 tests)

| Category | Count | Coverage |
|----------|-------|----------|
| Model creation | 3 | required fields, status default, priority default |
| Relationships | 6 | farm, batch, allocation, plot, bed, growth stage, user |
| Data types | 5 | dates, timestamps, JSON metadata |
| Enums | 2 | status values, priority values |
| FK behavior | 2 | cascade delete, set null |
| Batch relationships | 2 | hasMany tasks, allocation hasMany tasks |
| Scopes | 1 | all scopes work |
| Timestamp separation | 1 | planned vs actual remain distinct |
| Model methods | 1 | all relationship methods exist |

### WorkTaskGenerationServiceTest (15 tests)

| Category | Count | Coverage |
|----------|-------|----------|
| Happy path | 3 | generates tasks, one per stage, correct fields |
| Date calculation | 1 | planned dates based on cumulative duration |
| Idempotency | 3 | no duplicates, same allocation no duplicate, different allocation = separate task |
| Allocation targeting | 1 | plot-specific tasks from allocation |
| Allocation guard | 1 | rejects allocation from another batch |
| Edge cases | 3 | no growth stages, task type from name, uses today if no start |
| Variety handling | 1 | variety-specific growth stages |
| Metadata | 1 | stage info in task metadata |
| Defaults | 1 | all tasks have planned status/normal priority |

---

## Test Results

### WorkTaskSchemaTest
```
$ rtk php artisan test tests/Feature/WorkTaskSchemaTest.php

OK (22 tests, 64 assertions)
```

### WorkTaskGenerationServiceTest
```
$ rtk php artisan test tests/Feature/WorkTaskGenerationServiceTest.php

OK (15 tests, 47 assertions)
```

### Full Test Suite
```
$ rtk php artisan test

OK (136 tests, 427 assertions)
```

**No regressions from previous test suite (99 tests).**

---

## Files Changed

### New Files

1. `database/migrations/2026_05_13_000001_create_work_tasks_table.php` - Work tasks migration
2. `app/Models/WorkTask.php` - WorkTask model with relationships and scopes
3. `tests/Feature/WorkTaskSchemaTest.php` - Schema validation tests
4. `app/Services/WorkTaskGenerationService.php` - Task generation service
5. `tests/Feature/WorkTaskGenerationServiceTest.php` - Generation service tests
6. `.sisyphus/evidence/task-06-task-generation-service.md` - This evidence file

### Modified Files

1. `app/Models/PlantingBatch.php` - Added `workTasks()` HasMany relationship
2. `app/Models/PlantingBatchAllocation.php` - Added `workTasks()` HasMany relationship

---

## Open Risks

| Risk | Severity | Mitigation |
|------|----------|------------|
| Growth stage data dependency | Medium | Service returns empty result if no stages; upstream must ensure stages exist |
| Task type mapping may be incomplete | Low | Conservative mapping with `general` fallback; can extend typeMap as needed |
| Date calculation edge case (0 duration) | Low | Due date = start date if duration is 1; works for duration >= 1 |

---

## Release Risk Log Impact

- **RES-016:** ✅ Work task schema/model foundation added
- **RES-017:** ✅ Work task generation service added
- **Gate M3 (Task generation):** ✅ UNBLOCKED - schema + service tests pass

---

## Next Steps

1. **Incident + Chemical Usage** (T7) - Link to batch for traceability
2. **Pre-harvest Inspection** (T8) - Batch context for inspection
3. **Harvest Module** (T9) - Multi-harvest aggregation per business rules
4. **Work Task API** - Expose task generation via controller (not this agent's scope)
5. **Task Assignment Service** - Assign generated tasks to workers (downstream)
6. **Farming Log API** - Worker completion/log/photo submission for Expo contract

---

**End of Task-06 Task Generation Service Evidence**
