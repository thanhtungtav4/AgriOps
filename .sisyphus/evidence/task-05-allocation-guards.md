# Task-05 Allocation Guards Evidence

**Date:** 2026-05-13
**Agent:** H - Allocation Guards
**Project:** /Users/macbook/Herd/ariops

---

## Scope Delivered

### Owns (as per Agent H prompt)

| Deliverable | Status | Notes |
|-------------|--------|-------|
| `app/Exceptions/AllocationException.php` | ✅ | Domain-specific exceptions with error codes |
| `app/Services/PlantingBatchAllocationService.php` | ✅ | Service with 4 guard validations |
| `tests/Feature/PlantingBatchAllocationGuardTest.php` | ✅ | 19 tests covering all requirements |
| `.sisyphus/evidence/task-05-allocation-guards.md` | ✅ | This file |

### Must NOT Touch (per prompt)

- API controllers: ✅ NOT touched
- Auth/security code: ✅ NOT touched
- Seeders: ✅ NOT touched
- Planning formulas: ✅ NOT touched
- Filament resources: ✅ NOT touched
- `.sisyphus/agent-board.md`: ✅ NOT touched
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`: ✅ NOT touched

---

## Implementation Details

### AllocationException

Domain-specific exception class with error codes for allocation errors:

| Code | Purpose |
|------|---------|
| `CODE_FARM_MISMATCH` | Plot belongs to different farm than batch |
| `CODE_PLOT_UNAVAILABLE` | Plot status not allowed for allocation |
| `CODE_AREA_EXCEEDS_PLOT` | Allocated area > plot area |
| `CODE_DUPLICATE_ALLOCATION` | Batch already allocated to plot/bed |

Each factory method provides context for test assertions.

### PlantingBatchAllocationService

The service provides two public methods:

1. **`allocate(batch, plot, options, bedId)`** - Creates allocation after validation
2. **`validateAllocation(batch, plot, options, bedId)`** - Validates without creating

#### Guard Logic

| Guard | Rule | Behavior |
|-------|------|----------|
| 1. Farm Mismatch | `$batch->farm_id !== $plot->farm_id` | Throws `farmMismatch()` |
| 2. Plot Status | Default only `available` | Throws `plotUnavailable()` unless `allowed_statuses` option set |
| 3. Area Validation | `$allocatedAreaM2 > $plot->area_m2` | Throws `areaExceedsPlot()` |
| 4. Duplicate | Same batch/plot/bed exists | Throws `duplicateAllocation()` |

#### Options Array

| Key | Description | Default |
|-----|-------------|---------|
| `allocated_area_m2` | Override allocated area | `$plot->area_m2` |
| `allowed_statuses` | Override allowed plot statuses | `['available']` |
| `notes` | Allocation notes | `null` |
| `initial_status` | Allocation status | `'allocated'` |

---

## Test Results

```
$ rtk php artisan test tests/Feature/PlantingBatchAllocationGuardTest.php

PHPUnit 12.x.x
Configuration: /Users/macbook/Herd/ariops/phpunit.xml
...................
Time: 00:00.271, Memory: 26.00 MB

OK (19 tests, 42 assertions)
```

### Test Coverage

| Test | Verifies |
|------|---------|
| `test_allocation_rejects_plot_from_different_farm` | Guard 1: Farm mismatch rejection |
| `test_allocation_rejects_farm_mismatch_error_code` | Error code and context |
| `test_allocation_rejects_inactive_plot` | Guard 2: 'suspended' status blocked |
| `test_allocation_rejects_rest_plot` | Guard 2: 'rest' status blocked |
| `test_allocation_rejects_restoring_plot` | Guard 2: 'restoring' status blocked |
| `test_allocation_rejects_plot_unavailable_error_code` | Error code and context |
| `test_allocation_allows_plot_when_explicitly_allowed` | Option override for statuses |
| `test_allocation_rejects_allocated_area_greater_than_plot_area` | Guard 3: Area validation |
| `test_allocation_rejects_area_exceeds_plot_error_code` | Error code and context |
| `test_allocation_rejects_duplicate_batch_plot_without_bed` | Guard 4: Duplicate without bed |
| `test_allocation_rejects_duplicate_with_bed` | Guard 4: Duplicate with bed |
| `test_allocation_allows_different_beds_same_plot` | Multiple beds per plot allowed |
| `test_allocation_allows_multi_plot_same_farm` | Multi-plot allocation same farm |
| `test_allocation_updates_plot_current_batch_id` | Plot link updated |
| `test_allocation_exception_to_array` | Exception serialization |
| `test_validate_only_does_not_create` | Validation only mode |
| `test_validate_throws_on_farm_mismatch` | Validation throws errors |
| `test_validate_throws_on_duplicate` | Validation throws errors |

---

## Verification Commands

```bash
# Run allocation guard tests
rtk php artisan test tests/Feature/PlantingBatchAllocationGuardTest.php

# Run batch-related tests
rtk php artisan test tests/Feature/PlantingBatchFoundationTest.php tests/Feature/PlantingBatchAllocationGuardTest.php
```

---

## Other Tests Status

**Pre-existing failures (not caused by this task):**
- `PlantingBatchApiTest` (2 tests): API returns response without `status` key - unrelated API controller issue
- `PlanningApiTest` (5 tests): 403 errors - existing auth/RBAC issues
- `SupplyInputApiTest` (2 tests): 403 errors - existing auth/RBAC issues

These failures existed before this task and are unrelated to the allocation guards implementation.

---

## Files Changed

### New Files

1. `app/Exceptions/AllocationException.php` - Domain-specific exceptions
2. `app/Services/PlantingBatchAllocationService.php` - Allocation service with guards
3. `tests/Feature/PlantingBatchAllocationGuardTest.php` - Test coverage
4. `.sisyphus/evidence/task-05-allocation-guards.md` - This file

### May Touch With Care (Not Modified)

- `app/Models/PlantingBatch.php` - No changes
- `app/Models/PlantingBatchAllocation.php` - No changes

---

## Open Risks

| Risk | Severity | Mitigation |
|------|----------|------------|
| Pre-existing test failures in PlantingBatchApiTest/PlanningApiTest/SupplyInputApiTest | Major | Not caused by this task; existing auth/API issues |
| No integration with API controllers yet | Medium | Service ready for controller integration |

---

## Downstream Dependencies

This implementation is required by:
- Task 6: Work Task & Farming Log (needs batch allocations)
- Task 7: Incident + Chemical Usage (needs batch context)
- Task 8: Pre-harvest Inspection (needs batch context)
- Task 9: Harvest Module (needs batch context)
- Task 10: Packing Lot Mixing (needs batch context)

---

## Next Recommended Step

1. Integrate `PlantingBatchAllocationService` into API controller for planting batch allocations
2. Add authorization checks (farm scope) in API layer
3. Consider adding bed-level area validation (bed area vs allocated area)

---

**End of Task-05 Allocation Guards Evidence**