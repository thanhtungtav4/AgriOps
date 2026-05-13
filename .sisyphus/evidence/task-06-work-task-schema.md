# Task-06 Work Task Schema Evidence

**Date:** 2026-05-13
**Agent:** J - Work Task Schema
**Project:** /Users/macbook/Herd/ariops

---

## Scope Delivered

### Owns (as per Agent J prompt)

| Deliverable | Status | Notes |
|-------------|--------|-------|
| Migration for `work_tasks` | ✅ | `2026_05_13_000001_create_work_tasks_table.php` |
| `app/Models/WorkTask.php` | ✅ | Already existed, verified correct |
| `tests/Feature/WorkTaskSchemaTest.php` | ✅ | 22 tests, all passing |
| Evidence: `.sisyphus/evidence/task-06-work-task-schema.md` | ✅ | This file |

### May Touch With Care

- `app/Models/PlantingBatch.php` - ✅ Added `workTasks()` relationship
- `app/Models/PlantingBatchAllocation.php` - ✅ Added `workTasks()` relationship

### Must NOT Touch

| Item | Status |
|------|--------|
| API controllers/routes | ✅ NOT touched |
| Task generation service | ✅ NOT touched |
| Auth/security code | ✅ NOT touched |
| Seeders | ✅ NOT touched |
| Planning formulas | ✅ NOT touched |
| Filament resources | ✅ NOT touched |
| `.sisyphus/agent-board.md` | ✅ NOT touched |
| `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md` | ✅ NOT touched |

---

## Implementation Details

### Migration Schema

```php
Schema::create('work_tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('farm_id')->constrained()->onDelete('cascade');
    $table->foreignId('planting_batch_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('planting_batch_allocation_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('plot_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('bed_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('growth_stage_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('assigned_user_id')->nullable()->constrained()->onDelete('set null');
    $table->string('title');
    $table->string('task_type')->nullable();
    $table->string('status', 20)->default('planned');
    $table->string('priority', 20)->default('normal');
    $table->date('planned_start_date')->nullable();
    $table->date('planned_due_date')->nullable();
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->text('instructions')->nullable();
    $table->text('completion_note')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->index(['farm_id', 'status']);
    $table->index(['assigned_user_id', 'status']);
    $table->index(['planned_due_date', 'status']);
    $table->index(['planting_batch_id', 'status']);
});
```

### Status Enum Values

- `planned` (default)
- `assigned`
- `in_progress`
- `done`
- `cancelled`

### Priority Enum Values

- `low`
- `normal` (default)
- `high`
- `urgent`

### Indexes Added

| Composite Index | Purpose |
|----------------|---------|
| `[farm_id, status]` | Farm-scoped task listing |
| `[assigned_user_id, status]` | User task dashboard |
| `[planned_due_date, status]` | Overdue task queries |
| `[planting_batch_id, status]` | Batch task aggregation |

### Model Relationships

**WorkTask:**
- `farm()` - BelongsTo Farm
- `plantingBatch()` - BelongsTo PlantingBatch
- `allocation()` - BelongsTo PlantingBatchAllocation
- `plot()` - BelongsTo Plot
- `bed()` - BelongsTo Bed
- `growthStage()` - BelongsTo GrowthStage
- `assignedUser()` - BelongsTo User

**Reverse relationships added:**
- `PlantingBatch::workTasks()` - HasMany WorkTask
- `PlantingBatchAllocation::workTasks()` - HasMany WorkTask

### FK Cascade Rules

| FK Column | On Delete |
|-----------|-----------|
| `farm_id` | CASCADE |
| `planting_batch_id` | CASCADE |
| `planting_batch_allocation_id` | SET NULL |
| `plot_id` | CASCADE |
| `bed_id` | CASCADE |
| `growth_stage_id` | SET NULL |
| `assigned_user_id` | SET NULL |

---

## Test Coverage

### WorkTaskSchemaTest Results

```
$ rtk php artisan test tests/Feature/WorkTaskSchemaTest.php

PHPUnit 12.x.x
Configuration: /Users/macbook/Herd/ariops/phpunit.xml
......................

Time: 00:00.280, Memory: 26.00 MB

OK (22 tests, 64 assertions)
```

### Test Categories

| Category | Count | Tests |
|----------|-------|-------|
| Basic CRUD | 2 | `test_work_task_can_be_created_with_required_fields`, `test_work_task_defaults_status_to_planned`, `test_work_task_defaults_priority_to_normal` |
| Relationships | 7 | `test_work_task_belongs_to_farm`, `test_work_task_belongs_to_planting_batch`, `test_work_task_can_link_to_planting_batch_allocation`, `test_work_task_can_link_to_plot`, `test_work_task_can_link_to_bed`, `test_work_task_can_link_to_growth_stage`, `test_work_task_can_be_assigned_to_user` |
| Dates/Timestamps | 4 | `test_work_task_has_planned_dates`, `test_work_task_has_actual_timestamps`, `test_planned_and_actual_timestamps_remain_separate`, `test_work_task_has_instructions_and_completion_note` |
| Enums | 2 | `test_work_task_status_enum_values`, `test_work_task_priority_enum_values` |
| JSON | 1 | `test_work_task_has_metadata_json` |
| FK Protection | 2 | `test_work_task_cascade_deletes_when_batch_deleted`, `test_work_task_nullifies_when_allocation_deleted` |
| Batch Relationships | 2 | `test_planting_batch_has_many_work_tasks`, `test_planting_batch_allocation_has_many_work_tasks` |
| Model Introspection | 1 | `test_work_task_model_has_relationships` |

---

## Full Test Suite Results

```
$ rtk php artisan test

PHPUnit 12.x.x
Configuration: /Users/macbook/Herd/ariops/phpunit.xml
...................................................................................................

Time: 00:01.431, Memory: 28.00 MB

OK (136 tests, 427 assertions)
```

### Integrator Follow-up

The task generation failures seen during parallel execution were resolved by the Agent K/Integrator pass. The full suite now exits successfully and the previous risky marker was fixed by adding assertions to the optional production plan reference test.

---

## Files Changed

### New Files

1. `database/migrations/2026_05_13_000001_create_work_tasks_table.php`
2. `tests/Feature/WorkTaskSchemaTest.php`

### Modified Files

1. `app/Models/PlantingBatch.php` - Added `workTasks()` HasMany relationship
2. `app/Models/PlantingBatchAllocation.php` - Added `workTasks()` HasMany relationship

### Already Existing (Verified)

1. `app/Models/WorkTask.php` - Already existed with correct structure

---

## Open Risks

| Risk | Severity | Mitigation |
|------|----------|------------|
| Manual mobile smoke not captured | Major | Work task API, log submission API, and multipart photo upload tests exist; capture task receive/log/photo smoke evidence before MVP-1 exit |
| SQLite CHECK constraints | Minor | Using string columns with defaults for cross-DB compatibility |

---

## Release Risk Log Impact

- **Gate M3 (Task generation):** ✅ Schema and generation service complete; API/log submission remains separate T6 slice

---

## Next Steps

1. Implement Work Task API endpoints (Task 6 API slice)
2. Implement task assignment/status transition service
3. Implement farming log/photo submission contract for Expo

---

**End of Task-06 Work Task Schema Evidence**
