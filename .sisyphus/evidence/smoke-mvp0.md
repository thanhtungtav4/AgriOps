# MVP-0 Smoke Test Checklist

**Generated:** 2026-05-12
**Agent:** E - QA/Evidence
**Project:** /Users/macbook/Herd/ariops
**Scope:** MVP-0 Backend Nucleus (Tasks T1-T4)

---

## Pre-Check: Environment

```bash
# Verify PostgreSQL is running
pg_isready -h 127.0.0.1 -p 5432

# Verify Laravel artisan works
php artisan --version
# Expected: Laravel Framework 11.x.x

# Verify .env has correct DB connection
grep DB_CONNECTION .env
# Expected: DB_CONNECTION=pgsql
```

---

## Phase 1: Database & Migrations

### ✅ 1.1 Migration Fresh Run

```bash
php artisan migrate:fresh
```

**Expected:**
- All 18 migrations run successfully
- No errors
- Exit code 0

**Pass Criteria:** ✅ All migrations ran | ❌ Any error = FAIL

**Evidence:** `task-01-migration.log`

---

### ✅ 1.2 Migration Status Check

```bash
php artisan migrate:status
```

**Expected:**
- All 18 migrations show `[X] Ran` (or `[Y] Batch`)

**Pass Criteria:** All 18 show Ran | ❌ Any showing No means not migrated

---

### ✅ 1.3 FK Constraint Verification

```sql
-- Via psql or Laravel DB
psql -h 127.0.0.1 -p 5432 -d ariops -c "\d+ plots"
psql -h 127.0.0.1 -p 5432 -d ariops -c "\d+ crop_varieties"
psql -h 127.0.0.1 -p 5432 -d ariops -c "\d+ supply_demands"
```

**Expected:**
- FK constraints present for `farm_id`, `crop_id`, etc.
- ON DELETE behavior correct (CASCADE, SET NULL)

**Evidence:** `task-01-db-architecture-qc.log`

---

### ✅ 1.4 Unique Constraint Verification

```sql
-- Verify unique constraints
psql -h 127.0.0.1 -p 5432 -d ariops -c "\d+ farms"
# Should show: farms_code_unique
psql -h 127.0.0.1 -p 5432 -d ariops -c "\d+ plots"
# Should show: plots_farm_id_code_unique
psql -h 127.0.0.1 -p 5432 -d ariops -c "\d+ crop_varieties"
# Should show: crop_varieties_crop_id_code_unique
```

---

## Phase 2: Auth + RBAC

### ✅ 2.1 Login Success (admin)

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@agriops.test","password":"password"}'
```

**Expected:**
```json
{
  "data": {
    "token": "...",
    "token_type": "Bearer"
  }
}
```

**Pass Criteria:** HTTP 200 + token returned | ❌ HTTP 401 = FAIL

**Evidence:** `task-02-auth-happy.log`

---

### ✅ 2.2 Login Fail (wrong password)

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@agriops.test","password":"wrongpassword"}'
```

**Expected:** HTTP 401 with error message

---

### ✅ 2.3 Me Endpoint (authenticated)

```bash
# Get token first, then:
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer {token}"
```

**Expected:** HTTP 200 with user data including `role`

---

### ✅ 2.4 Logout

```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer {token}"
```

**Expected:** HTTP 204 or 200

---

## Phase 3: Master Data CRUD

### ✅ 3.1 List Farms

```bash
curl -X GET http://localhost:8000/api/v1/farms \
  -H "Authorization: Bearer {token}"
```

**Expected:** HTTP 200 with farms array

---

### ✅ 3.2 Create Farm

```bash
curl -X POST http://localhost:8000/api/v1/farms \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Test Farm",
    "code":"TF001",
    "total_area_m2":1000,
    "status":"active"
  }'
```

**Expected:** HTTP 201 with created farm data

**Evidence:** `task-03-master-happy.log`

---

### ✅ 3.3 Validation: Negative Area Rejected

```bash
curl -X POST http://localhost:8000/api/v1/farms \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Bad Farm",
    "code":"BF001",
    "total_area_m2":-100,
    "status":"active"
  }'
```

**Expected:** HTTP 422 with validation error for `total_area_m2`

**Evidence:** `task-03-master-validation.log`

---

### ✅ 3.4 List Crops

```bash
curl -X GET http://localhost:8000/api/v1/crops \
  -H "Authorization: Bearer {token}"
```

**Expected:** HTTP 200 with crops array

---

### ✅ 3.5 List Crop Varieties

```bash
curl -X GET http://localhost:8000/api/v1/crop-varieties \
  -H "Authorization: Bearer {token}"
```

**Expected:** HTTP 200 with varieties array

---

### ✅ 3.6 List Plots

```bash
curl -X GET http://localhost:8000/api/v1/plots \
  -H "Authorization: Bearer {token}"
```

**Expected:** HTTP 200 with plots array

---

### ✅ 3.7 List Supply Contracts

```bash
curl -X GET http://localhost:8000/api/v1/supply-contracts \
  -H "Authorization: Bearer {token}"
```

**Expected:** HTTP 200

---

### ✅ 3.8 List Supply Demands

```bash
curl -X GET http://localhost:8000/api/v1/supply-demands \
  -H "Authorization: Bearer {token}"
```

**Expected:** HTTP 200

---

### ✅ 3.9 List Production Plans

```bash
curl -X GET http://localhost:8000/api/v1/production-plans \
  -H "Authorization: Bearer {token}"
```

**Expected:** HTTP 200

---

## Phase 4: Planning API

### ✅ 4.1 Happy Path - Complete Norms

```bash
# First create test data via API or check existing:
curl -X POST http://localhost:8000/api/v1/planning/calculate \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 10,
    "unit": "kg",
    "frequency": "daily",
    "crop_id": 1,
    "variety_id": 1,
    "farm_id": 1,
    "target_date": "2026-06-30"
  }'
```

**Expected:**
```json
{
  "output": {
    "delivery_quantity": 10,
    "raw_harvest_quantity": 12.5,
    "plants_needed_estimate": 25,
    "plants_needed_execution": 25,
    "plants_to_plant_estimate": 27.78,
    "plants_to_plant_execution": 28,
    "area_m2": 6.94,
    "labor_hours": 1.39,
    "days_to_first_harvest": 35
  },
  "assumptions": {
    "formula_version": "...",
    "loss_model": "..."
  },
  "fulfillment": {
    "status": "warning",
    "warnings": ["season_not_specified"]
  }
}
```

**Pass Criteria:** HTTP 200 + all required output fields | ❌ HTTP 422 = FAIL

**Evidence:** `task-04-green.log`, `tests/Feature/PlanningApiTest.php`

---

### ✅ 4.2 Domain Error - Missing Norms

```bash
# Create crop WITHOUT loss_profile:
curl -X POST http://localhost:8000/api/v1/planning/calculate \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 10,
    "unit": "kg",
    "frequency": "daily",
    "crop_id": 999,  # Non-existent or crop without norms
    "target_date": "2026-06-30"
  }'
```

**Expected:** HTTP 422 with `error: "planning_error"` and field-level message

**Evidence:** `task-04-tdd-trace.md`

---

### ✅ 4.3 Unit Validation Error

```bash
curl -X POST http://localhost:8000/api/v1/planning/calculate \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 10,
    "unit": "unsupported_unit",
    "frequency": "daily",
    "crop_id": 1,
    "target_date": "2026-06-30"
  }'
```

**Expected:** HTTP 422 with `"field": "unit"` in response

**Evidence:** `task-04-tdd-trace.md`

---

### ✅ 4.4 Planning Formula Spec Verification

Verify the formula spec exists and matches implementation:

```bash
cat .sisyphus/evidence/task-04-planning-formula-spec.md
```

**Expected:** Spec document exists with:
- Formula definitions
- Unit conversion rules
- Rounding profile
- Assumptions contract
- Fulfillment status contract

---

## Phase 5: Automated Test Suite

### ✅ 5.1 Run All Tests

```bash
php artisan test
```

**Expected:**
```
✓ 9 tests, 52 assertions, passed in 255ms
```

**Evidence:** `task-04-green.log`

---

### ✅ 5.2 Run Planning Tests Only

```bash
php artisan test --filter=PlanningApiTest
```

**Expected:** 5 tests passed

---

### ✅ 5.3 Run Supply Input Tests

```bash
php artisan test --filter=SupplyInputApiTest
```

**Expected:** 2 tests passed

---

## Phase 6: API Route Verification

### ✅ 6.1 List All API Routes

```bash
php artisan route:list --path=api
```

**Expected:** At least 19 routes registered:

| # | Method | URI | Controller |
|---|--------|-----|-------------|
| 1 | POST | api/v1/auth/login | AuthController@login |
| 2 | POST | api/v1/auth/logout | AuthController@logout |
| 3 | GET | api/v1/auth/me | AuthController@me |
| 4 | GET | api/v1/crops | CropApiController@index |
| 5 | GET | api/v1/crops/{id} | CropApiController@show |
| 6 | GET | api/v1/crop-varieties | CropVarietyApiController@index |
| 7 | GET | api/v1/crop-varieties/{id} | CropVarietyApiController@show |
| 8 | GET | api/v1/farms | FarmApiController@index |
| 9 | GET | api/v1/farms/{id} | FarmApiController@show |
| 10 | GET | api/v1/plots | PlotApiController@index |
| 11 | GET | api/v1/plots/{id} | PlotApiController@show |
| 12 | POST | api/v1/planning/calculate | PlanningController@calculate |
| 13 | GET | api/v1/production-plans | PlanningController@index |
| 14 | POST | api/v1/production-plans | PlanningController@store |
| 15 | GET | api/v1/supply-contracts | SupplyContractController@index |
| 16 | POST | api/v1/supply-contracts | SupplyContractController@store |
| 17 | GET | api/v1/supply-contracts/{id} | SupplyContractController@show |
| 18 | GET | api/v1/supply-demands | SupplyDemandController@index |
| 19 | POST | api/v1/supply-demands | SupplyDemandController@store |
| 20 | GET | api/v1/supply-demands/{id} | SupplyDemandController@show |

**Evidence:** `task-04-routes.log`

---

## Phase 7: Data Dictionary Verification

### ✅ 7.1 Verify All MVP-0 Tables Exist

```sql
psql -h 127.0.0.1 -p 5432 -d ariops -c "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name;"
```

**Expected Tables (18 total):**

| # | Table | Module |
|---|-------|--------|
| 1 | beds | Master Data |
| 2 | cache | Platform |
| 3 | crop_varieties | Master Data |
| 4 | crops | Master Data |
| 5 | farms | Identity |
| 6 | fertilizer_norms | Planning Norms |
| 7 | growth_stages | Master Data |
| 8 | harvest_models | Planning Norms |
| 9 | irrigation_norms | Planning Norms |
| 10 | jobs | Platform |
| 11 | labor_norms | Planning Norms |
| 12 | loss_profiles | Planning Norms |
| 13 | personal_access_tokens | Auth |
| 14 | plots | Master Data |
| 15 | product_standards | Master Data |
| 16 | production_plans | Planning |
| 17 | supply_contracts | Planning |
| 18 | supply_demands | Planning |
| 19 | users | Identity |

**Evidence:** `data-dictionary-baseline.md`

---

## Phase 8: Security Checks

### ⚠️ 8.1 Farm Scope Check (KNOWN GAP)

```bash
# As worker role, try to access all farms
curl -X GET http://localhost:8000/api/v1/farms \
  -H "Authorization: Bearer {worker_token}"
```

**CURRENT BEHAVIOR (GAP):** Worker sees ALL farms ❌

**EXPECTED BEHAVIOR:** Worker should only see their assigned farm ✅

**This is a KNOWN GAP documented in release-risk-log.md**

---

### ⚠️ 8.2 RBAC Enforcement Check (KNOWN GAP)

```bash
# As worker, try to create production plan
curl -X POST http://localhost:8000/api/v1/production-plans \
  -H "Authorization: Bearer {worker_token}" \
  -H "Content-Type: application/json" \
  -d '...'
```

**CURRENT BEHAVIOR (GAP):** May succeed without role check ❌

**EXPECTED BEHAVIOR:** HTTP 403 Forbidden ✅

**This is a KNOWN GAP documented in release-risk-log.md**

---

## MVP-0 Smoke Checklist Summary

| Phase | Check | Status | Evidence |
|-------|-------|--------|----------|
| 1 | Migration fresh pass | ✅ PASS | `task-01-migration.log` |
| 1 | FK constraints present | ✅ PASS | `task-01-db-architecture-qc.log` |
| 2 | Auth login/logout | ✅ PASS | `task-02-auth-happy.log` |
| 2 | Role separation | 🟡 PARTIAL | Design exists, enforcement GAP |
| 3 | Master data CRUD | ✅ PASS | `task-03-master-happy.log` |
| 3 | Validation errors | ✅ PASS | `task-03-master-validation.log` |
| 4 | Planning happy path | ✅ PASS | `task-04-green.log` |
| 4 | Planning domain errors | ✅ PASS | `task-04-tdd-trace.md` |
| 5 | All tests pass | ✅ PASS | 9 tests, 52 assertions |
| 6 | API routes registered | ✅ PASS | 20 routes confirmed |
| 7 | All tables exist | ✅ PASS | 19 tables confirmed |
| 8 | Farm scope isolation | ⚠️ GAP | Not implemented |
| 8 | RBAC controller enforcement | ⚠️ GAP | Not implemented |

---

## MVP-0 Smoke Test Decision

| Decision | Condition |
|----------|-----------|
| **✅ MVP-0 READY** | All ✅ items pass, gaps documented |
| **⚠️ PROCEED WITH CAUTION** | Known gaps acknowledged, fix scheduled |
| **❌ MVP-0 NOT READY** | Any ❌ item fails |

**Current Status:** ⚠️ **PROCEED WITH CAUTION**

**Rationale:** Farm scope isolation (BLK-001) is a critical security gap but documented with mitigation plan. MVP-0 core functionality is operational.

---

## Next Steps After MVP-0 Smoke

1. **Verify PostgreSQL staging migration** (critical gap)
2. **Add farm scope isolation tests** before MVP-1
3. **Implement FarmScopeMiddleware** (fix BLK-001)
4. **Add RBAC policy checks** to controllers (fix CRIT-001)

---

**End of MVP-0 Smoke Test Checklist**
