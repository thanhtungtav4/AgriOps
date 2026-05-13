# MVP-1 Demand-to-QR Smoke Test Checklist

**Generated:** 2026-05-12
**Agent:** E - QA/Evidence
**Project:** /Users/macbook/Herd/ariops
**Scope:** MVP-1 Production Trace Slice (Tasks T5-T11)
**Status:** 🔴 NOT YET IMPLEMENTED - This is a pre-implementation checklist

---

## Purpose

This checklist defines the smoke test scenarios that MUST pass before MVP-1 is considered complete. It covers the full vertical slice from demand input to public QR traceability.

**This is a living document.** It will be updated with actual test evidence as each task is implemented.

---

## Pre-Check: Prerequisites

Before running these tests, ensure:

```bash
# 1. MVP-0 is complete
php artisan test --filter=PlanningApiTest
# Expected: 5 tests, 52 assertions

# 2. PostgreSQL migration fresh
php artisan migrate:fresh

# 3. Canonical seed data loaded
php artisan db:seed --class=CanonicalSeeder
# Expected: 1 farm, 2 plots, 1 crop, norms, etc.

# 4. API server running
php artisan serve
```

---

## End-to-End Flow: Demand → QR

This is the PRIMARY smoke test that validates the entire MVP-1 vertical slice.

### Step 1: Create Demand/Contract

```bash
# Create supply contract
curl -X POST http://localhost:8000/api/v1/supply-contracts \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Siêu thị Xanh",
    "customer_type": "retail",
    "crop_id": 1,
    "quantity": 100,
    "unit": "kg",
    "frequency": "daily",
    "start_date": "2026-06-01",
    "end_date": "2026-12-31"
  }'

# Capture contract_id
```

**Expected:** HTTP 201 with contract data

**Evidence File:** `task-11-qr-e2e-step1.log`

---

### Step 2: Create Supply Demand

```bash
curl -X POST http://localhost:8000/api/v1/supply-demands \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "supply_contract_id": {contract_id},
    "crop_id": 1,
    "quantity": 10,
    "unit": "kg",
    "frequency": "daily",
    "target_date": "2026-06-20"
  }'

# Capture demand_id
```

**Expected:** HTTP 201 with demand data

**Evidence File:** `task-11-qr-e2e-step2.log`

---

### Step 3: Run Planning Calculation

```bash
curl -X POST http://localhost:8000/api/v1/planning/calculate \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 10,
    "unit": "kg",
    "frequency": "daily",
    "crop_id": 1,
    "variety_id": 1,
    "farm_id": 1,
    "target_date": "2026-06-20"
  }'
```

**Expected:**
- HTTP 200
- Output includes: `raw_harvest_quantity`, `plants_needed`, `area_m2`, `labor_hours`
- Assumptions present
- Fulfillment status present

**Evidence File:** `task-11-qr-e2e-step3.log`

---

### Step 4: Create Production Plan

```bash
curl -X POST http://localhost:8000/api/v1/production-plans \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "crop_id": 1,
    "variety_id": 1,
    "farm_id": 1,
    "quantity": 10,
    "unit": "kg",
    "target_delivery_date": "2026-06-20",
    "status": "approved"
  }'

# Capture plan_id
```

**Expected:** HTTP 201 with plan data

**Evidence File:** `task-11-qr-e2e-step4.log`

---

### Step 5: Create Planting Batch

```bash
curl -X POST http://localhost:8000/api/v1/planting-batches \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "production_plan_id": {plan_id},
    "crop_id": 1,
    "variety_id": 1,
    "farm_id": 1,
    "expected_start_date": "2026-05-15",
    "expected_harvest_date": "2026-06-20"
  }'

# Capture batch_id
```

**Expected:** HTTP 201, batch created with status "planning" or "approved"

**Evidence File:** `task-11-qr-e2e-step5.log`

---

### Step 6: Allocate Batch to Plot(s)

```bash
curl -X POST http://localhost:8000/api/v1/planting-batches/{batch_id}/allocate \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "plot_id": 1,
    "allocated_area_m2": 10
  }'
```

**Expected:** HTTP 201, allocation created

**Evidence File:** `task-11-qr-e2e-step6.log`

---

### Step 7: Transition Batch Status (Planning → Preparing)

```bash
curl -X POST http://localhost:8000/api/v1/planting-batches/{batch_id}/transition \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "to_status": "preparing",
    "reason": "Starting soil preparation"
  }'
```

**Expected:** HTTP 200, status updated, audit log created

**Evidence File:** `task-11-qr-e2e-step7.log`

---

### Step 8: Create Work Tasks

```bash
curl -X POST http://localhost:8000/api/v1/work-tasks \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "planting_batch_id": {batch_id},
    "stage": "soil_preparation",
    "title": "Làm đất",
    "due_date": "2026-05-16",
    "assigned_to": {worker_id}
  }'
```

**Expected:** HTTP 201, task created

**Evidence File:** `task-11-qr-e2e-step8.log`

---

### Step 9: Worker Accepts Task

```bash
curl -X PATCH http://localhost:8000/api/v1/work-tasks/{task_id}/accept \
  -H "Authorization: Bearer {worker_token}"
```

**Expected:** HTTP 200, task status → "assigned" or "in_progress"

**Evidence File:** `task-11-qr-e2e-step9.log`

---

### Step 10: Worker Submits Farming Log

```bash
curl -X POST http://localhost:8000/api/v1/work-tasks/{task_id}/logs \
  -H "Authorization: Bearer {worker_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "completed",
    "notes": "Đã làm đất xong",
    "actual_duration_hours": 4,
    "photos": ["base64..."]
  }'
```

**Expected:** HTTP 201, log created

**Evidence File:** `task-11-qr-e2e-step10.log`

---

### Step 11: Record Incident (Optional but Testable)

```bash
curl -X POST http://localhost:8000/api/v1/incidents \
  -H "Authorization: Bearer {technician_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "planting_batch_id": {batch_id},
    "type": "pest",
    "description": "Sâu ăn lá",
    "severity": "medium"
  }'
```

**Expected:** HTTP 201

**Evidence File:** `task-11-qr-e2e-step11.log`

---

### Step 12: Record Chemical Usage (If Incident)

```bash
curl -X POST http://localhost:8000/api/v1/chemical-usages \
  -H "Authorization: Bearer {technician_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "incident_id": {incident_id},
    "product_name": "Bio-pesticide A",
    "dosage": "50ml/10L",
    "application_date": "2026-05-17",
    "isolation_days": 7
  }'
```

**Expected:** HTTP 201, chemical usage recorded

**Evidence File:** `task-11-qr-e2e-step12.log`

---

### Step 13: Pre-Harvest Inspection

```bash
curl -X POST http://localhost:8000/api/v1/pre-harvest-inspections \
  -H "Authorization: Bearer {technician_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "planting_batch_id": {batch_id},
    "checklist": {
      "size_acceptable": true,
      "color_acceptable": true,
      "no_pest_damage": true,
      "chemical_isolation_passed": true
    },
    "result": "pass",
    "notes": "Đạt chuẩn thu hoạch"
  }'
```

**Expected:** HTTP 201, inspection passed

**Evidence File:** `task-11-qr-e2e-step13.log`

---

### Step 14: Create Harvest Lot

```bash
curl -X POST http://localhost:8000/api/v1/harvest-lots \
  -H "Authorization: Bearer {manager_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "planting_batch_id": {batch_id},
    "harvest_date": "2026-06-20",
    "raw_quantity_kg": 15,
    "grade_a_kg": 10,
    "grade_b_kg": 3,
    "grade_c_kg": 1,
    "reject_kg": 1,
    "reject_reasons": ["Sâu bệnh nhẹ"]
  }'
```

**Expected:**
- HTTP 201
- Grade sum = raw_quantity (15 = 10 + 3 + 1 + 1)

**Evidence File:** `task-11-qr-e2e-step14.log`

---

### Step 15: Isolation Guard Test (ERROR CASE)

```bash
# Try to harvest BEFORE isolation period passes
# (If isolation is 7 days and we applied chemical 3 days ago)
curl -X POST http://localhost:8000/api/v1/harvest-lots \
  -H "Authorization: Bearer {manager_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "planting_batch_id": {batch_id},
    "harvest_date": "2026-05-20",  # Only 3 days after chemical
    "raw_quantity_kg": 10
  }'
```

**Expected:** HTTP 422 with message about isolation period not passed

**Evidence File:** `task-11-qr-e2e-step15-error.log`

---

### Step 16: Create Processing Record (Optional)

```bash
curl -X POST http://localhost:8000/api/v1/processing-records \
  -H "Authorization: Bearer {manager_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "harvest_lot_id": {harvest_lot_id},
    "before_quantity_kg": 15,
    "after_quantity_kg": 14,
    "process_type": "washing_grading"
  }'
```

**Expected:** HTTP 201

**Evidence File:** `task-11-qr-e2e-step16.log`

---

### Step 17: Create Packing Lot

```bash
curl -X POST http://localhost:8000/api/v1/packing-lots \
  -H "Authorization: Bearer {manager_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "packing_date": "2026-06-21",
    "total_quantity_kg": 10,
    "unit": "kg",
    "sources": [
      {
        "harvest_lot_id": {harvest_lot_id},
        "quantity_kg": 10,
        "grade_a_kg": 8,
        "grade_b_kg": 2
      }
    ]
  }'

# Capture qr_code from response
```

**Expected:**
- HTTP 201
- Packing lot created with QR code
- Sources linked correctly

**Evidence File:** `task-11-qr-e2e-step17.log`

---

### Step 18: Cross-Farm Packing Mix Test

```bash
# Create packing lot with sources from different farms
curl -X POST http://localhost:8000/api/v1/packing-lots \
  -H "Authorization: Bearer {manager_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "packing_date": "2026-06-21",
    "total_quantity_kg": 20,
    "sources": [
      {
        "harvest_lot_id": {harvest_lot_id_farm_a},
        "farm_id": 1,
        "quantity_kg": 12
      },
      {
        "harvest_lot_id": {harvest_lot_id_farm_b},
        "farm_id": 2,
        "quantity_kg": 8
      }
    ]
  }'
```

**Expected:**
- HTTP 201
- QR code includes both farm sources

**Evidence File:** `task-11-qr-e2e-step18.log`

---

### Step 19: Verify QR Privacy (PUBLIC - NO AUTH)

```bash
curl -X GET http://localhost:8000/api/v1/traceability/{qr_code}
# NO Authorization header!
```

**Expected:**
```json
{
  "data": {
    "crop": "Rau muống",
    "farm": "Trang trại Rau An Toàn",
    "harvest_date": "2026-06-20",
    "packing_date": "2026-06-21",
    "grade": "A",
    "safety_status": "Đạt chuẩn kiểm soát an toàn",
    "sources": [
      {"farm": "Farm A", "harvest_date": "2026-06-20"},
      {"farm": "Farm B", "harvest_date": "2026-06-19"}
    ]
  }
}
```

**MUST NOT contain:**
- Chemical names (e.g., "Bio-pesticide A")
- Dosage amounts
- Internal user names
- Costs/prices
- Margins
- Worker IDs

**Evidence File:** `task-11-qr-e2e-step19-privacy.log`

---

### Step 20: QR Mobile Readability Test

Open the QR code URL in a mobile browser:

1. Scan QR with phone camera
2. Verify page loads < 2 seconds
3. Verify text is readable on small screen
4. Verify all required information is visible
5. Verify NO sensitive data visible

**Evidence File:** `task-11-qr-e2e-step20-mobile.log`

---

## Individual Task Smoke Tests

### T5: Batch Lifecycle Tests

| Test | Command | Expected | Evidence |
|------|---------|----------|----------|
| Happy path: status transition | `POST /planting-batches/{id}/transition` | 200 + updated status | `task-05-batch-transition-happy.log` |
| Audit log created | Check audit_logs table | Log entry with user_id, timestamp | `task-05-batch-transition-happy.log` |
| Allocation to available plot | `POST /planting-batches/{id}/allocate` | 201 | `task-05-allocation-blocked.log` |
| Allocation to "rest" plot blocked | `POST /planting-batches/{id}/allocate` | 422 with warning | `task-05-allocation-blocked.log` |
| Allocation to "suspended" plot blocked | `POST /planting-batches/{id}/allocate` | 422 with rejection | `task-05-allocation-blocked.log` |
| Invalid transition blocked | batch → "completed" when not done | 422 | `task-05-batch-transition-happy.log` |

---

### T6: Work Task Tests

| Test | Command | Expected | Evidence |
|------|---------|----------|----------|
| Task created from template | `POST /work-tasks` | 201 | `task-06-task-log-happy.log` |
| Worker accepts task | `PATCH /work-tasks/{id}/accept` | 200 | `task-06-task-log-happy.log` |
| Worker submits log | `POST /work-tasks/{id}/logs` | 201 | `task-06-task-log-happy.log` |
| Log with photo | `POST /work-tasks/{id}/logs` + photo | 201, photo stored | `task-06-task-log-happy.log` |
| Photo required validation | Log without required photo | 422 | `task-06-task-log-validation.log` |
| Missing required fields | Log without notes/photos | 422 | `task-06-task-log-validation.log` |

---

### T7: Incident + Chemical Tests

| Test | Command | Expected | Evidence |
|------|---------|----------|----------|
| Create incident | `POST /incidents` | 201 | `task-07-chemical-trace.log` |
| Link chemical usage | `POST /chemical-usages` | 201, linked | `task-07-chemical-trace.log` |
| Isolation days calculated | Check harvest_lot creation response | isolation_days field | `task-07-isolation-block.log` |
| Harvest before isolation blocked | `POST /harvest-lots` (3 days after chemical) | 422 | `task-07-isolation-block.log` |
| Harvest after isolation allowed | `POST /harvest-lots` (8 days after chemical) | 201 | `task-07-isolation-block.log` |

---

### T8: Inspection + Approval Tests

| Test | Command | Expected | Evidence |
|------|---------|----------|----------|
| Inspection pass | `POST /pre-harvest-inspections` with pass | 201 | `task-08-inspection-pass.log` |
| Inspection fail | `POST /pre-harvest-inspections` with fail | 201 + warning | `task-08-inspection-fail.log` |
| Harvest after inspection pass | `POST /harvest-lots` | 201 | `task-08-inspection-pass.log` |
| Harvest blocked after inspection fail | `POST /harvest-lots` | 422 or 403 | `task-08-inspection-fail.log` |
| Approval required for sensitive action | Role-based approval test | 403 for non-approver | `task-08-inspection-pass.log` |

---

### T9: Harvest Tests

| Test | Command | Expected | Evidence |
|------|---------|----------|----------|
| Harvest with grade breakdown | `POST /harvest-lots` | 201, grades recorded | `task-09-harvest-happy.log` |
| Grade sum validation | grade_a + b + c + reject = raw | Math correct | `task-09-harvest-validation.log` |
| Grade sum exceeded | grades sum > raw_quantity | 422 | `task-09-harvest-validation.log` |
| Harvest lot linked to batch | Check response | batch_id present | `task-09-harvest-happy.log` |
| Multiple harvests per batch | Create 2nd harvest lot | Both linked to same batch | `task-09-harvest-happy.log` |

---

### T10: Packing Tests

| Test | Command | Expected | Evidence |
|------|---------|----------|----------|
| Packing from single harvest | `POST /packing-lots` | 201 | `task-10-packing-mix.log` |
| Packing from multiple harvests | 2+ sources | 201, sources array | `task-10-packing-mix.log` |
| Cross-farm mixing | Sources from 2 farms | 201 | `task-10-packing-mix.log` |
| Invalid harvest lot rejected | Source already packed | 422 | `task-10-packing-source-invalid.log` |
| QR code generated | Check response | qr_code present | `task-10-packing-mix.log` |

---

### T11: QR Traceability Tests

| Test | Command | Expected | Evidence |
|------|---------|----------|----------|
| QR public access (no auth) | `GET /traceability/{qr}` | 200 | `task-11-qr-public.log` |
| QR contains required fields | Check response | crop, farm, dates | `task-11-qr-public.log` |
| QR contains source info | Sources array | ≥1 source | `task-11-qr-public.log` |
| NO chemical names | Check response | Field absent | `task-11-qr-privacy.log` |
| NO dosage amounts | Check response | Field absent | `task-11-qr-privacy.log` |
| NO internal user names | Check response | Field absent | `task-11-qr-privacy.log` |
| NO costs/prices | Check response | Field absent | `task-11-qr-privacy.log` |
| Safety status shown | If chemical used | Status present | `task-11-qr-privacy.log` |

---

## Error Path Tests

### E1: Batch Cannot Be Deleted If Harvests Exist

```bash
curl -X DELETE http://localhost:8000/api/v1/planting-batches/{batch_id} \
  -H "Authorization: Bearer {admin_token}"
```

**Expected:** HTTP 409 Conflict (cannot delete batch with harvests)

**Evidence File:** `task-11-qr-e2e-error-batch-delete.log`

---

### E2: Packing Lot Cannot Be Modified After Published

```bash
curl -X PATCH http://localhost:8000/api/v1/packing-lots/{lot_id} \
  -H "Authorization: Bearer {manager_token}" \
  -H "Content-Type: application/json" \
  -d '{"total_quantity_kg": 15}'
```

**Expected:** HTTP 422 (published lot is immutable)

**Evidence File:** `task-11-qr-e2e-error-lot-immutable.log`

---

### E3: Worker Cannot Approve Harvest

```bash
curl -X POST http://localhost:8000/api/v1/harvest-lots/{id}/approve \
  -H "Authorization: Bearer {worker_token}"
```

**Expected:** HTTP 403 Forbidden

**Evidence File:** `task-11-qr-e2e-error-worker-approve.log`

---

## MVP-1 Smoke Summary

| Task | Scenario | Status | Evidence |
|------|----------|--------|----------|
| T5 | Batch lifecycle happy | 🔴 | Not created |
| T5 | Batch allocation guard | 🔴 | Not created |
| T6 | Task accept + log | 🔴 | Not created |
| T7 | Incident + chemical trace | 🔴 | Not created |
| T7 | Isolation blocking | 🔴 | Not created |
| T8 | Inspection → harvest gate | 🔴 | Not created |
| T9 | Harvest grade breakdown | 🔴 | Not created |
| T10 | Packing multi-source | 🔴 | Not created |
| T11 | QR public access | 🔴 | Not created |
| T11 | QR privacy whitelist | 🔴 | Not created |
| **E2E** | **Full demand → QR path** | 🔴 | **Not created** |

---

## MVP-1 Release Gate Decision

| Gate | Criteria | Status | Evidence Required |
|------|----------|--------|-------------------|
| M1 | All task evidence logs created | 🔴 | 14 log files |
| M2 | Batch lifecycle E2E test passes | 🔴 | `task-11-qr-e2e-step5-7.log` |
| M3 | Allocation guards tested | 🔴 | `task-05-allocation-blocked.log` |
| M4 | Mobile log submission tested | 🔴 | `task-06-task-log-happy.log` |
| M5 | Isolation blocking tested | 🔴 | `task-07-isolation-block.log` |
| M6 | Inspection harvest gate tested | 🔴 | `task-08-inspection-pass.log` |
| M7 | Harvest grade math tested | 🔴 | `task-09-harvest-validation.log` |
| M8 | Packing multi-source tested | 🔴 | `task-10-packing-mix.log` |
| M9 | QR public accessible (no auth) | 🔴 | `task-11-qr-public.log` |
| M10 | QR privacy whitelist verified | 🔴 | `task-11-qr-privacy.log` |
| M11 | **Full E2E smoke test passes** | 🔴 | `task-11-qr-e2e-complete.log` |

**Current Status:** 🔴 **NOT READY - ALL GATES BLOCKED**

---

## Next Steps Before MVP-1 Smoke

1. **Implement T5-T11** per plan
2. **Run each smoke test** after implementation
3. **Save evidence logs** to `.sisyphus/evidence/`
4. **Update this document** with actual results
5. **Run E2E smoke** when all individual tests pass

---

**End of MVP-1 Demand-to-QR Smoke Test Checklist**
