# Security Scope Implementation Evidence

**Date:** 2026-05-12
**Agent:** C2 Security/API Implementation
**Project:** /Users/macbook/Herd/ariops
**Status:** COMPLETE

---

## Commands Run

### Security Tests
```bash
rtk php artisan test tests/Feature/FarmScopeApiTest.php tests/Feature/AuthTokenPolicyTest.php
```

**Result:** 20/20 passed, 74 assertions, 313ms

### Full Test Suite
```bash
rtk php artisan test
```

**Result:** 44/44 passed, 183 assertions, 547ms

---

## Files Changed

### New Files Created

| File | Purpose |
|------|---------|
| `app/Http/Responses/ApiResponse.php` | Trait for consistent JSON error/success responses |
| `app/Http/Middleware/FarmScopeMiddleware.php` | Middleware for farm scope isolation |
| `app/Support/FarmScopable.php` | Trait for farm-scoped query filtering |
| `tests/Feature/FarmScopeApiTest.php` | 13 tests for cross-farm isolation |
| `tests/Feature/AuthTokenPolicyTest.php` | 7 tests for auth/token policy |
| `database/migrations/2026_05_12_000300_add_farm_id_to_supply_contracts_and_demands.php` | Add farm_id FK to contracts/demands |
| `database/migrations/2024_xx_xx_xx_create_personal_access_tokens_table.php` | Sanctum token table |

### Modified Files

| File | Changes |
|------|---------|
| `bootstrap/app.php` | Registered `farm.scope` middleware alias |
| `routes/api.php` | Applied `farm.scope` middleware to protected routes |
| `app/Http/Controllers/Api/V1/AuthController.php` | Standard errors, token metadata, scope info, safe logout |
| `app/Http/Controllers/Api/V1/FarmApiController.php` | Farm scope filtering, forbidden responses |
| `app/Http/Controllers/Api/V1/PlotApiController.php` | Farm scope filtering, forbidden responses |
| `app/Http/Controllers/Api/V1/SupplyContractController.php` | Farm scope filtering, auto-assign farm_id on create |
| `app/Http/Controllers/Api/V1/SupplyDemandController.php` | Farm scope filtering, auto-assign farm_id on create |
| `app/Http/Controllers/Api/V1/PlanningController.php` | Farm scope filtering, ownership validation |
| `app/Models/SupplyContract.php` | Added farm_id to fillable |
| `app/Models/SupplyDemand.php` | Added farm_id to fillable |
| `tests/Feature/SupplyInputApiTest.php` | Added farm_id to user setup |
| `tests/Feature/PlanningApiTest.php` | Added farm_id to user setup |

---

## Implementation Details

### 1. Farm Scope Isolation

- `FarmScopeMiddleware` validates user authentication and farm assignment
- Admin users bypass farm filtering (global access)
- Non-admin users without `farm_id` receive 403 AUTH_FORBIDDEN
- All farm-scoped routes wrapped in `farm.scope` middleware group

### 2. RBAC Enforcement

Controllers now enforce farm scope:

| Controller | Farm Filter | Forbidden Response |
|------------|-------------|-------------------|
| FarmApiController | List: own farm only / Show: ownership check | AUTH_FORBIDDEN |
| PlotApiController | Query: `farm_id` filter | AUTH_FORBIDDEN |
| SupplyContractController | Query: `farm_id` filter / Create: auto-assign | AUTH_FORBIDDEN |
| SupplyDemandController | Query: `farm_id` filter / Create: auto-assign | AUTH_FORBIDDEN |
| PlanningController | Query: `farm_id` filter / Create: ownership check | AUTH_FORBIDDEN |

### 3. API Error Contract

All controllers now use `ApiResponse` trait:

```json
{
  "error": {
    "code": "AUTH_FORBIDDEN",
    "message": "You do not have permission to access this resource.",
    "details": {
      "required_role": "admin or own farm",
      "current_role": "farm_manager"
    },
    "trace_id": "req_abc123"
  }
}
```

### 4. Token Policy

- Login returns token with metadata: `token_type`, `expires_at`
- Token expires in 7 days
- Logout safely handles missing tokens
- `/auth/me` returns scope info (`global` for admin, `farm` for others)

---

## Open Risks Addressed

| Risk ID | Risk | Status |
|---------|------|--------|
| BLK-001 | Farm scope isolation NOT implemented | ✅ RESOLVED |
| CRIT-001 | RBAC controller enforcement missing | ✅ RESOLVED |
| CRIT-004 | Token policy incomplete | ✅ RESOLVED |
| MAJ-002 | API error contract inconsistent | ✅ RESOLVED |

---

## Still Outstanding (Outside Scope)

| Risk ID | Risk | Reason Not Addressed |
|---------|------|---------------------|
| BLK-002 | QR privacy not implemented | MVP-1 task |
| MAJ-003 | Batch table migration missing | MVP-1 task |
| MAJ-001 | No automated regression tests | Already have tests |

---

## Test Coverage

### FarmScopeApiTest (13 tests)
- `test_farm_manager_sees_only_own_farm_on_farm_list`
- `test_admin_sees_all_farms_on_farm_list`
- `test_farm_manager_cannot_view_other_farm`
- `test_admin_can_view_any_farm`
- `test_farm_manager_sees_only_own_plots`
- `test_farm_manager_cannot_view_other_farm_plot`
- `test_supply_contracts_scoped_to_farm`
- `test_supply_demands_scoped_to_farm`
- `test_production_plans_scoped_to_farm`
- `test_worker_role_also_scoped_to_farm`
- `test_unauthenticated_user_gets_401`
- `test_user_without_farm_gets_403`

### AuthTokenPolicyTest (7 tests)
- `test_login_returns_token_with_metadata`
- `test_login_with_invalid_credentials_returns_standard_error`
- `test_login_with_nonexistent_user_returns_standard_error`
- `test_logout_revokes_token`
- `test_me_returns_user_with_scope_info`
- `test_admin_me_returns_global_scope`
- `test_worker_cannot_approve`
- `test_protected_endpoint_requires_authentication`

---

**End of Evidence**
