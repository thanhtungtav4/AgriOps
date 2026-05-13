# Task 08 Smoke Evidence - Pre-Harvest Inspection + Approval Gate

## QA Scenario Format
**Tool**: REST API (Laravel API v1)
**Date**: 2026-05-13
**API Base**: `http://localhost:8000/api/v1/`

---

## Happy Path: Pre-Harvest Inspection + Approval → Harvest Unlock

### Step 1: Login as Farm Manager

**cURL Command:**
```bash
curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"manager@agriops.test","password":"password"}'
```

**Response (200 OK):**
```json
{
  "data": {
    "token": "4|nioXYP0Z7QssdQ27oO72cbXZO9iikj8DQHeAlWyK12968ef0",
    "token_type": "Bearer",
    "user": {
      "id": 4,
      "name": "Bà Trần Thị B",
      "role": "farm_manager",
      "farm_id": 1
    }
  }
}
```

**Status**: ✅ PASS - Auth token received

---

### Step 2: Create New Planting Batch for Testing

**cURL Command:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/planting-batches" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "crop_id": 1,
    "code": "BATCH-002",
    "planned_quantity": 100,
    "planned_unit": "kg",
    "planned_area_m2": 100,
    "planned_start_date": "2026-05-01",
    "planned_harvest_date": "2026-06-01",
    "status": "growing"
  }'
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 2,
    "code": "BATCH-002",
    "status": "growing",
    "farm_id": 1
  }
}
```

**Status**: ✅ PASS - Batch BATCH-002 created with ID 2

---

### Step 3: Create Pre-Harvest Inspection (All Checks Pass)

**cURL Command:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/pre-harvest-inspections" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "planting_batch_id": 2,
    "inspected_at": "2026-05-13T08:00:00+07:00",
    "checklist": [
      {"key": "pest_check", "label": "Kiểm tra sâu bệnh", "passed": true, "note": "Không phát hiện sâu bệnh"},
      {"key": "residue_check", "label": "Kiểm tra dư lượng thuốc", "passed": true, "note": "Đạt tiêu chuẩn an toàn"},
      {"key": "maturity_check", "label": "Kiểm tra độ chín", "passed": true, "note": "Cây đạt độ chín thu hoạch"},
      {"key": "quality_check", "label": "Kiểm tra chất lượng", "passed": true, "note": "Đạt tiêu chuẩn loại A"}
    ],
    "notes": "Kiểm tra toàn diện lô BATCH-002. Đạt tất cả các tiêu chí."
  }'
```

**Request Payload:**
```json
{
  "planting_batch_id": 2,
  "inspected_at": "2026-05-13T08:00:00+07:00",
  "checklist": [
    {"key": "pest_check", "label": "Kiểm tra sâu bệnh", "passed": true, "note": "Không phát hiện sâu bệnh"},
    {"key": "residue_check", "label": "Kiểm tra dư lượng thuốc", "passed": true, "note": "Đạt tiêu chuẩn an toàn"},
    {"key": "maturity_check", "label": "Kiểm tra độ chín", "passed": true, "note": "Cây đạt độ chín thu hoạch"},
    {"key": "quality_check", "label": "Kiểm tra chất lượng", "passed": true, "note": "Đạt tiêu chuẩn loại A"}
  ],
  "notes": "Kiểm tra toàn diện lô BATCH-002. Đạt tất cả các tiêu chí."
}
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 1,
    "status": "approved",
    "farm_id": 1,
    "planting_batch_id": 2,
    "inspector_user_id": 4,
    "approved_by_user_id": 4,
    "inspected_at": "2026-05-13T08:00:00.000000Z",
    "approved_at": "2026-05-13T10:38:53.000000Z",
    "checklist": [
      {"key": "pest_check", "label": "Kiểm tra sâu bệnh", "passed": true, "note": "Không phát hiện sâu bệnh"},
      {"key": "residue_check", "label": "Kiểm tra dư lượng thuốc", "passed": true, "note": "Đạt tiêu chuẩn an toàn"},
      {"key": "maturity_check", "label": "Kiểm tra độ chín", "passed": true, "note": "Cây đạt độ chín thu hoạch"},
      {"key": "quality_check", "label": "Kiểm tra chất lượng", "passed": true, "note": "Đạt tiêu chuẩn loại A"}
    ],
    "notes": "Kiểm tra toàn diện lô BATCH-002. Đạt tất cả các tiêu chí.",
    "planting_batch": {
      "id": 2,
      "code": "BATCH-002"
    },
    "inspector": {
      "id": 4,
      "name": "Bà Trần Thị B"
    },
    "approved_by": {
      "id": 4,
      "name": "Bà Trần Thị B"
    }
  },
  "meta": {
    "trace_id": "6a0454bd08f02"
  }
}
```

**Status**: ✅ PASS - Inspection auto-approved when all checklist items pass. Note: `approved_at` set to `inspected_at` (or immediate), `approved_by_user_id` populated.

---

### Step 4: Create Harvest Lot (After Approved Inspection)

**cURL Command:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/harvest-lots" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "planting_batch_id": 2,
    "harvest_date": "2026-05-14",
    "raw_quantity": 50,
    "unit": "kg",
    "notes": "Harvest after approved inspection"
  }'
```

**Request Payload:**
```json
{
  "planting_batch_id": 2,
  "harvest_date": "2026-05-14",
  "raw_quantity": 50,
  "unit": "kg",
  "notes": "Harvest after approved inspection"
}
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 1,
    "status": "available",
    "farm_id": 1,
    "planting_batch_id": 2,
    "pre_harvest_inspection_id": 1,
    "harvested_by_user_id": 4,
    "harvest_date": "2026-05-14T00:00:00.000000Z",
    "raw_quantity": "50.000",
    "unit": "kg",
    "notes": "Harvest after approved inspection",
    "planting_batch": {
      "id": 2,
      "code": "BATCH-002",
      "actual_harvest_date": "2026-05-14",
      "actual_quantity": "50.00",
      "status": "harvesting"
    },
    "pre_harvest_inspection": {
      "id": 1,
      "status": "approved",
      "checklist": [
        {"key": "pest_check", "passed": true},
        {"key": "residue_check", "passed": true},
        {"key": "maturity_check", "passed": true},
        {"key": "quality_check", "passed": true}
      ]
    },
    "harvested_by": {
      "id": 4,
      "name": "Bà Trần Thị B"
    }
  },
  "meta": {
    "trace_id": "6a0454d19008e"
  }
}
```

**Status**: ✅ PASS - Harvest lot created successfully, linked to approved inspection (ID 1). Batch status updated to "harvesting".

---

## Negative Path 1: Rejected Inspection Blocks Harvest

### Create Batch with Failed Inspection

**cURL Command:**
```bash
# Create new batch
curl -s -X POST "http://localhost:8000/api/v1/planting-batches" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"crop_id": 1, "code": "BATCH-003", "planned_quantity": 100, "planned_unit": "kg", "planned_area_m2": 100, "planned_start_date": "2026-05-01", "planned_harvest_date": "2026-06-01", "status": "growing"}'
```

**cURL Command - Create Rejected Inspection:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/pre-harvest-inspections" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "planting_batch_id": 3,
    "inspected_at": "2026-05-13T08:00:00+07:00",
    "checklist": [
      {"key": "pest_check", "label": "Kiểm tra sâu bệnh", "passed": false, "note": "Phát hiện sâu ăn lá"},
      {"key": "residue_check", "label": "Kiểm tra dư lượng thuốc", "passed": true}
    ],
    "notes": "Có vấn đề sâu bệnh cần xử lý trước khi thu hoạch"
  }'
```

**Response - Rejected Inspection:**
```json
{
  "data": {
    "id": 2,
    "status": "rejected",
    "planting_batch_id": 3,
    "inspector_user_id": 4,
    "approved_by_user_id": null,
    "approved_at": null,
    "rejected_at": "2026-05-13T10:39:56.000000Z",
    "checklist": [
      {"key": "pest_check", "label": "Kiểm tra sâu bệnh", "passed": false, "note": "Phát hiện sâu ăn lá"},
      {"key": "residue_check", "label": "Kiểm tra dư lượng thuốc", "passed": true}
    ],
    "rejection_reason": "One or more inspection criteria failed."
  },
  "meta": {
    "trace_id": "6a0454fc6d36e"
  }
}
```

**Status**: ✅ PASS - Inspection auto-rejected when any checklist item fails

---

### Attempt Harvest on Rejected Batch

**cURL Command:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/harvest-lots" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "planting_batch_id": 3,
    "harvest_date": "2026-05-14",
    "raw_quantity": 50,
    "unit": "kg",
    "notes": "Attempting harvest on rejected batch"
  }'
```

**Response (422 Unprocessable Entity):**
```json
{
  "error": {
    "code": "HARVEST_INSPECTION_BLOCKED",
    "message": "Latest pre-harvest inspection is not approved.",
    "trace_id": "6a045508b5eaa"
  }
}
```

**Status**: ✅ PASS - Harvest blocked with HARVEST_INSPECTION_BLOCKED error indicating inspection is not approved

---

## Summary

| Test Case | Expected Result | Actual Result | Status |
|-----------|-----------------|---------------|--------|
| Create batch for inspection | Batch created | BATCH-002, ID 2 | ✅ PASS |
| Create inspection (all pass) | status → approved | status: approved, approved_at set | ✅ PASS |
| Harvest after approved inspection | Harvest lot created | Lot ID 1 created, linked to inspection | ✅ PASS |
| Create inspection (one fails) | status → rejected | status: rejected, rejection_reason set | ✅ PASS |
| Harvest after rejected inspection | HARVEST_INSPECTION_BLOCKED | Code: HARVEST_INSPECTION_BLOCKED | ✅ PASS |

**Overall Status**: ✅ ALL TESTS PASSED

---

## Approval Gate Behavior

The pre-harvest inspection approval system works as follows:

1. **All checks pass** → Inspection auto-approved, harvest allowed
2. **Any check fails** → Inspection auto-rejected, harvest blocked
3. **Harvest Lot Service** checks latest approved inspection before allowing harvest

---

## Evidence Path
`.sisyphus/evidence/task-08-smoke-evidence.md`