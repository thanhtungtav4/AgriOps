# API Validation Contract Continuation

Date: 2026-05-13
Agent: Continuation Agent Z, integrator-corrected
Risk: MAJ-002 - API error contract inconsistent

## Decision

Validation normalization is implemented for JSON API requests, but MAJ-002 should still remain partial until future approval/controller endpoints and any non-validation framework errors are audited.

The integrator kept the safe parts and corrected the risky parts:

- Kept JSON `ValidationException` normalization to `error.code/message/details/trace_id`.
- Kept JSON throttle normalization to `AUTH_RATE_LIMITED`.
- Added top-level `errors` to validation responses so Laravel test helpers and field-level clients remain compatible.
- Removed generic `HttpException` normalization because it was too broad for this narrow pass.

## Files Changed

- `bootstrap/app.php`
- `tests/Feature/ApiErrorContractTest.php`
- `tests/Feature/AuthRateLimitTest.php`
- `tests/Feature/CostingPriceMarginApiTest.php`
- `tests/Feature/SupplyInputApiTest.php`

## Verification

Focused validation/API regression:

```bash
rtk php artisan test tests/Feature/ApiErrorContractTest.php tests/Feature/AuthRateLimitTest.php tests/Feature/CostingPriceMarginApiTest.php tests/Feature/SupplyInputApiTest.php
```

Full regression:

```bash
rtk php artisan test
```

Final results are recorded in the integrator summary for this wave.

Latest full regression result:

```text
298 tests passed, 1062 assertions
```

## Remaining Risks

- MAJ-002 is reduced, not fully closed.
- Generic 404/405/http framework exceptions are intentionally not normalized in this pass.
- New approval endpoints should use the shared `ApiResponse` helpers and receive their own contract tests.
