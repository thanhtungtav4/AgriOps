# API Error Contract Continuation

Date: 2026-05-13
Agent: Continuation Agent W, integrator-corrected
Risk: MAJ-002 - API error contract inconsistent

## Scope Audited

Continuation Agent W audited 23 controllers under `app/Http/Controllers/Api/V1/`.

The useful fixes retained by the integrator are:

- `CropApiController`: converted list/show responses to the shared `ApiResponse::success()` shape.
- `CropVarietyApiController`: converted list/show responses to the shared `ApiResponse::success()` shape.
- `PlanningController::calculate()`: converted domain errors to the shared `error.code/message/details/trace_id` shape while preserving the existing raw success payload.

## Integrator Correction

The agent attempted to change `WorkTaskController::generateWorkTasks()` success counters from `meta` into `data`. That broke the established API contract and existing tests, so the integrator reverted that part. Work task generation intentionally keeps `created_count`, `existing_count`, and `skipped_count` in `meta`.

## Files Changed

- `app/Http/Controllers/Api/V1/CropApiController.php`
- `app/Http/Controllers/Api/V1/CropVarietyApiController.php`
- `app/Http/Controllers/Api/V1/PlanningController.php`
- `tests/Feature/ApiErrorContractTest.php`
- `tests/Feature/PlanningApiTest.php`

## Verification

Focused API regression:

```bash
rtk php artisan test tests/Feature/ApiErrorContractTest.php tests/Feature/PlanningApiTest.php tests/Feature/WorkTaskApiTest.php
```

Result:

```text
37 tests passed, 170 assertions
```

Full Laravel regression:

```bash
rtk php artisan test
```

Result:

```text
295 tests passed, 1036 assertions
```

## Remaining Risks

- MAJ-002 is materially reduced, not fully closed.
- Laravel framework validation errors still use the default validation payload unless normalized in a later pass.
- Future controllers should be checked for `ApiResponse` usage before being considered API-contract complete.
