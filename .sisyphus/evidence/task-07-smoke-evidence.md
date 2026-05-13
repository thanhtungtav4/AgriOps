# Task 07 Smoke Evidence - Incident / Chemical Usage / Isolation Guard

## QA Scenario Format
**Tool**: REST API (Laravel API v1)
**Date**: 2026-05-13
**API Base**: `http://localhost:8000/api/v1/`

---

## Happy Path: Incident + Chemical Usage + Isolation Guard

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
    "expires_at": "2026-05-20T10:33:26+00:00",
    "user": {
      "id": 4,
      "name": "Bà Trần Thị B",
      "email": "manager@agriops.test",
      "role": "farm_manager",
      "farm_id": 1,
      "farm": {
        "id": 1,
        "name": "Trang trại Rau An Toàn",
        "code": "FARM001"
      }
    }
  },
  "meta": {
    "trace_id": "6a04542e9d4e8"
  }
}
```

**Status**: ✅ PASS - Auth token received

---

### Step 2: Create Incident (Pest Outbreak)

**cURL Command:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/incidents" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "planting_batch_id": 1,
    "incident_type": "pest",
    "severity": "high",
    "status": "open",
    "description": "Phát hiện sâu ăn lá trên cây con rau muống tại khu vực BATCH-001. Cần xử lý khẩn cấp.",
    "treatment_note": "Sẽ phun thuốc trừ sâu theo hướng dẫn kỹ thuật."
  }'
```

**Request Payload:**
```json
{
  "planting_batch_id": 1,
  "incident_type": "pest",
  "severity": "high",
  "status": "open",
  "description": "Phát hiện sâu ăn lá trên cây con rau muống tại khu vực BATCH-001. Cần xử lý khẩn cấp.",
  "treatment_note": "Sẽ phun thuốc trừ sâu theo hướng dẫn kỹ thuật."
}
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 1,
    "farm_id": 1,
    "planting_batch_id": 1,
    "incident_type": "pest",
    "severity": "high",
    "status": "open",
    "description": "Phát hiện sâu ăn lá trên cây con rau muống tại khu vực BATCH-001. Cần xử lý khẩn cấp.",
    "treatment_note": "Sẽ phun thuốc trừ sâu theo hướng dẫn kỹ thuật.",
    "reported_by_user_id": 4,
    "detected_at": "2026-05-13T10:36:20.000000Z",
    "planting_batch": {
      "id": 1,
      "code": "BATCH-001",
      "crop": {
        "id": 1,
        "name": "Rau muống"
      }
    },
    "reported_by_user": {
      "id": 4,
      "name": "Bà Trần Thị B",
      "role": "farm_manager"
    }
  },
  "meta": {
    "trace_id": "6a04542471d04"
  }
}
```

**Status**: ✅ PASS - Incident created with ID 1

---

### Step 3: Create Chemical Usage with Isolation Period

**cURL Command:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/chemical-usages" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "incident_id": 1,
    "planting_batch_id": 1,
    "product_name": "Thuốc trừ sâu Biolife",
    "product_type": "chemical",
    "active_ingredient": "Cypermethrin",
    "dosage_value": 50,
    "dosage_unit": "ml/phối nước",
    "quantity_value": 5,
    "quantity_unit": "lít",
    "isolation_days": 14,
    "applied_at": "2026-05-13T09:00:00+07:00",
    "notes": "Phun đều trên toàn bộ diện tích lô BATCH-001"
  }'
```

**Request Payload:**
```json
{
  "incident_id": 1,
  "planting_batch_id": 1,
  "product_name": "Thuốc trừ sâu Biolife",
  "product_type": "chemical",
  "active_ingredient": "Cypermethrin",
  "dosage_value": 50,
  "dosage_unit": "ml/phối nước",
  "quantity_value": 5,
  "quantity_unit": "lít",
  "isolation_days": 14,
  "applied_at": "2026-05-13T09:00:00+07:00",
  "notes": "Phun đều trên toàn bộ diện tích lô BATCH-001"
}
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 1,
    "farm_id": 1,
    "incident_id": 1,
    "planting_batch_id": 1,
    "product_name": "Thuốc trừ sâu Biolife",
    "product_type": "chemical",
    "active_ingredient": "Cypermethrin",
    "dosage_value": "50.000",
    "dosage_unit": "ml/phối nước",
    "quantity_value": "5.000",
    "quantity_unit": "lít",
    "isolation_days": 14,
    "applied_at": "2026-05-13T09:00:00.000000Z",
    "isolation_ends_at": "2026-05-27T09:00:00.000000Z",
    "incident": {
      "id": 1,
      "incident_type": "pest",
      "status": "open"
    },
    "planting_batch": {
      "id": 1,
      "code": "BATCH-001"
    },
    "applied_by_user": {
      "id": 4,
      "name": "Bà Trần Thị B"
    }
  },
  "meta": {
    "trace_id": "6a045437ce045"
  }
}
```

**Status**: ✅ PASS - Chemical usage created with `isolation_ends_at` = 2026-05-27 (14 days from application)

---

### Step 4a: Add Pre-Harvest Inspection (Required for Harvest)

**cURL Command:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/pre-harvest-inspections" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "planting_batch_id": 1,
    "checklist": [
      {"key": "check1", "label": "General check", "passed": true}
    ]
  }'
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 3,
    "status": "approved",
    "planting_batch_id": 1,
    "approved_by_user_id": 4
  },
  "meta": {
    "trace_id": "6a045526e4c4f"
  }
}
```

**Status**: ✅ PASS - Inspection approved

---

### Step 4b: Attempt Harvest During Isolation (Should Be Blocked)

**cURL Command:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/harvest-lots" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "planting_batch_id": 1,
    "harvest_date": "2026-05-15",
    "raw_quantity": 30,
    "unit": "kg"
  }'
```

**Request Payload:**
```json
{
  "planting_batch_id": 1,
  "harvest_date": "2026-05-15",
  "raw_quantity": 30,
  "unit": "kg"
}
```

**Response (422 Unprocessable Entity):**
```json
{
  "error": {
    "code": "HARVEST_ISOLATION_BLOCKED",
    "message": "Cannot harvest before chemical isolation period ends on 2026-05-27.",
    "trace_id": "6a045536a4041"
  }
}
```

**Status**: ✅ PASS - Harvest properly blocked with HARVEST_ISOLATION_BLOCKED error, indicating isolation end date

---

## Negative Path: Harvest Attempt Without Inspection (First Block)

### Attempt: Harvest Before Inspection

**cURL Command:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/harvest-lots" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "planting_batch_id": 1,
    "harvest_date": "2026-05-14",
    "raw_quantity": 50,
    "unit": "kg"
  }'
```

**Response (422 Unprocessable Entity):**
```json
{
  "error": {
    "code": "HARVEST_INSPECTION_BLOCKED",
    "message": "Pre-harvest inspection is required before harvest.",
    "trace_id": "6a04551d9c50b"
  }
}
```

**Status**: ✅ PASS - Inspection requirement enforced first (before isolation check)

---

## Summary

| Test Case | Expected Result | Actual Result | Status |
|-----------|-----------------|---------------|--------|
| Create incident | Incident created with type, severity | Incident ID 1 created | ✅ PASS |
| Create chemical usage with isolation | isolation_ends_at calculated | isolation_ends_at = 2026-05-27 | ✅ PASS |
| Add pre-harvest inspection | Inspection approved | Inspection ID 3 approved | ✅ PASS |
| Harvest during isolation | HARVEST_ISOLATION_BLOCKED | Code: HARVEST_ISOLATION_BLOCKED | ✅ PASS |
| Harvest without inspection | HARVEST_INSPECTION_BLOCKED | Code: HARVEST_INSPECTION_BLOCKED | ✅ PASS |

**Overall Status**: ✅ ALL TESTS PASSED

---

## Isolation Guard Verification

The isolation system correctly enforces a 14-day waiting period after chemical application:

1. **Chemical Usage Created**: 2026-05-13 at 09:00:00
2. **Isolation Period**: 14 days
3. **Isolation Ends**: 2026-05-27 at 09:00:00
4. **Harvest Attempt**: 2026-05-15 (before isolation ends)
5. **Result**: HARVEST_ISOLATION_BLOCKED with clear message

---

## Evidence Path
`.sisyphus/evidence/task-07-smoke-evidence.md`