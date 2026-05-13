# Task 06 Smoke Evidence - Work Task / Log

## QA Scenario Format
**Tool**: REST API (Laravel API v1)
**Date**: 2026-05-13
**API Base**: `http://localhost:8000/api/v1/`

---

## Happy Path: Work Task Lifecycle

### Step 1: Login as Farm Manager

**cURL Command:**
```bash
curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"manager@agriops.test","password":"password"}'
```

**Request Payload:**
```json
{
  "email": "manager@agriops.test",
  "password": "password"
}
```

**Response (200 OK):**
```json
{
  "data": {
    "token": "3|apqntIXinWX1fo97sTr3mgumAm8fYWQQPEo6v0Uj66c19d1e",
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
    "trace_id": "6a045376738f2"
  }
}
```

**Status**: ✅ PASS - Auth token received, farm association verified

---

### Step 2: List Work Tasks

**cURL Command:**
```bash
curl -s "http://localhost:8000/api/v1/work-tasks" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "farm_id": 1,
      "planting_batch_id": 1,
      "title": "Gieo giống",
      "task_type": "planting",
      "status": "planned",
      "priority": "normal",
      "planned_due_date": "2026-05-05T00:00:00.000000Z",
      "metadata": {
        "growth_stage_order": 1,
        "stage_duration_days": 5,
        "generated_from": "WorkTaskGenerationService"
      },
      "planting_batch": {
        "id": 1,
        "code": "BATCH-001",
        "crop": {
          "id": 1,
          "name": "Rau muống"
        }
      },
      "assigned_user": null
    },
    ...
  ],
  "meta": {
    "trace_id": "6a0453763d343"
  }
}
```

**Status**: ✅ PASS - 4 tasks returned for BATCH-001

---

### Step 3: Accept Task (Assign to Technician)

**cURL Command:**
```bash
curl -s -X PATCH "http://localhost:8000/api/v1/work-tasks/1/status" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "assigned",
    "assigned_user_id": 5
  }'
```

**Request Payload:**
```json
{
  "status": "assigned",
  "assigned_user_id": 5
}
```

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "status": "assigned",
    "assigned_user_id": 5,
    "assigned_user": {
      "id": 5,
      "name": "Anh Lê Văn C",
      "email": "tech@agriops.test",
      "role": "technician"
    }
  },
  "meta": {
    "trace_id": "6a04538ce44e0"
  }
}
```

**Status**: ✅ PASS - Task assigned to technician

---

### Step 4: Transition to In Progress

**cURL Command:**
```bash
curl -s -X PATCH "http://localhost:8000/api/v1/work-tasks/1/status" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"status": "in_progress"}'
```

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "status": "in_progress",
    "started_at": "2026-05-13T10:34:01.000000Z"
  },
  "meta": {
    "trace_id": "6a0453990ca23"
  }
}
```

**Status**: ✅ PASS - Task transitioned to in_progress with started_at timestamp

---

### Step 5: Submit Work Log with Photo Upload

**cURL Command:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/work-tasks/1/logs" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -F "notes=Gieo giống hoàn tất. Đã gieo hạt giống rau muống Nhật trên luống đã chuẩn bị." \
  -F "actual_start_at=2026-05-13T08:00:00+07:00" \
  -F "actual_end_at=2026-05-13T10:30:00+07:00" \
  -F "photos[]=@/tmp/test-photo.png;type=image/png;filename=test-photo.png"
```

**Response (201 Created):**
```json
{
  "data": {
    "id": 1,
    "status": "submitted",
    "work_task_id": 1,
    "reported_by_user_id": 4,
    "logged_at": "2026-05-13T10:34:39.000000Z",
    "actual_start_at": "2026-05-13T08:00:00.000000Z",
    "actual_end_at": "2026-05-13T10:30:00.000000Z",
    "notes": "Gieo giống hoàn tất. Đã gieo hạt giống rau muống Nhật trên luống đã chuẩn bị.",
    "photo_paths": [
      "/storage/work-task-logs/1/md6l9oDRwo4dd5tsHNocweIR3NbAax70lBwTsrrR.png"
    ],
    "work_task": {
      "id": 1,
      "status": "done",
      "completed_at": "2026-05-13T10:30:00.000000Z",
      "completion_note": "Gieo giống hoàn tất. Đã gieo hạt giống rau muống Nhật trên luống đã chuẩn bị."
    }
  },
  "meta": {
    "trace_id": "6a0453bfdc035"
  }
}
```

**Status**: ✅ PASS - Log created, photo stored, task marked as done

---

### Step 6: Verify Photo is Accessible

**cURL Command:**
```bash
curl -s -I "http://localhost:8000/storage/work-task-logs/1/md6l9oDRwo4dd5tsHNocweIR3NbAax70lBwTsrrR.png"
```

**Response:**
```
HTTP/1.1 200 OK
Content-Type: image/png
```

**Status**: ✅ PASS - Photo file accessible via storage URL

---

## Negative Path: Submit Log for Completed Task

### Attempt: Submit Second Log for Done Task (with client_uuid)

**cURL Command:**
```bash
curl -s -X POST "http://localhost:8000/api/v1/work-tasks/1/logs" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "notes": "Second log attempt",
    "client_uuid": "test-duplicate-123"
  }'
```

**Response (422 Unprocessable Entity):**
```json
{
  "error": {
    "code": "TASK_TERMINAL_STATE_CONFLICT",
    "message": "Cannot submit a log for a task that is in a terminal state.",
    "details": {
      "current_status": "done",
      "task_id": 1,
      "task_title": "Gieo giống",
      "next_action": "Fetch latest task state or contact supervisor"
    },
    "trace_id": "6a0453dfbdb9e"
  }
}
```

**Status**: ✅ PASS - Properly blocked with TASK_TERMINAL_STATE_CONFLICT error

---

## Summary

| Test Case | Expected Result | Actual Result | Status |
|-----------|-----------------|---------------|--------|
| Login | Returns auth token with user/farm info | Token + user data | ✅ PASS |
| List work tasks | Returns tasks for farm | 4 tasks for BATCH-001 | ✅ PASS |
| Accept task (assign) | Status → assigned, user set | assigned_user_id = 5 | ✅ PASS |
| Transition to in_progress | Status → in_progress, started_at set | started_at timestamp set | ✅ PASS |
| Submit log with photo | Log created, task → done | Log ID 1, task status done | ✅ PASS |
| Photo stored | Photo accessible via storage URL | HTTP 200, Content-Type: image/png | ✅ PASS |
| Submit log for done task | Blocked with error | TASK_TERMINAL_STATE_CONFLICT | ✅ PASS |

**Overall Status**: ✅ ALL TESTS PASSED

---

## Evidence Path
`.sisyphus/evidence/task-06-smoke-evidence.md`