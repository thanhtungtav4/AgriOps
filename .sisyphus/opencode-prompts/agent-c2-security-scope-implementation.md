# Agent C2 Security Scope Implementation Prompt

You are Agent C2 Security/API Implementation for AgriOps.

Work in `/Users/macbook/Herd/ariops`.

## Mission

Close MVP-0 security blockers around farm scope, RBAC enforcement, token policy, and API error consistency without touching planning formula math or database redesign.

## Sources To Read

- `.sisyphus/evidence/release-risk-log.md`
- `.sisyphus/evidence/rbac-matrix-v1.md`
- `.sisyphus/evidence/api-error-contract-v1.md`
- `routes/api.php`
- `app/Http/Controllers/Api/V1/*`
- `app/Models/User.php`
- existing `tests/Feature/*`

## Owns

- API controllers only as needed for farm scope and policy checks:
  - `app/Http/Controllers/Api/V1/FarmApiController.php`
  - `app/Http/Controllers/Api/V1/PlotApiController.php`
  - `app/Http/Controllers/Api/V1/SupplyContractController.php`
  - `app/Http/Controllers/Api/V1/SupplyDemandController.php`
  - `app/Http/Controllers/Api/V1/PlanningController.php`
  - `app/Http/Controllers/Api/V1/AuthController.php`
- New focused support classes if useful:
  - `app/Http/Responses/*`
  - `app/Support/*`
  - `app/Http/Middleware/*`
- Security tests:
  - `tests/Feature/FarmScopeApiTest.php`
  - `tests/Feature/AuthTokenPolicyTest.php`
- Evidence:
  - `.sisyphus/evidence/security-scope-implementation.md`

## Must Not Touch

- Planning formula internals in `app/Services/PlanningService.php`
- Migrations unless absolutely required for a tiny security fix
- Filament resources
- Seeders
- MVP-1 QR implementation
- `.sisyphus/agent-board.md`
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`

## Implementation Requirements

1. Farm-scoped users (`farm_owner`, `farm_manager`, `technician`, `worker`, `warehouse`, `delivery`) must not list/show data from another farm.
2. `admin` can access all farm-scoped data.
3. API responses for forbidden scope should be JSON 403 with a stable error code.
4. Login should use Sanctum tokens with a named token and include basic token metadata in the response if available.
5. Logout must safely handle missing/current token edge cases.
6. `/api/v1/auth/me` must return user and scope information.
7. Add focused tests for farm A user not seeing farm B records and admin seeing all.

## Verification

Run:

```bash
rtk php artisan test tests/Feature/FarmScopeApiTest.php tests/Feature/AuthTokenPolicyTest.php
rtk php artisan test
```

Write evidence with commands, results, files changed, and open risks.

