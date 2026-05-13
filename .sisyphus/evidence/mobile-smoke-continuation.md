# Mobile Smoke Continuation - MAJ-006 Evidence

## Evidence for: task receive → log submit → photo/offline handling

## Commands Run

### 1. TypeScript Type Check
```
cd mobile/field-app && npm run lint
```
**Result**: PASS - No type errors.

### 2. Jest Unit Tests
```
cd mobile/field-app && npm test -- --runInBand
```
**Result**: PASS - 3 test suites, 22 tests total.

```
PASS __tests__/services/workTask.test.ts
PASS __tests__/services/offlineQueue.test.ts
PASS __tests__/services/auth.test.ts

Test Suites: 3 passed, 3 total
Tests:       22 passed, 22 total
Snapshots:   0 total
Time:        0.519 s, estimated 1 s
```

## What This Proves

### Task Receive (MAJ-006.1)
The `workTaskService.getTodayTasks()` correctly:
- Fetches tasks for authenticated user via `/api/v1/work-tasks?assigned_user_id={userId}&due_before={date}`
- Filters out `done` and `cancelled` tasks from results
- Throws `NotAuthenticated` when no session exists

### Task Status Transitions (MAJ-006.2)
The service properly handles task lifecycle:
- `startTask(id)` → PATCH `/work-tasks/{id}/status` with `{ status: "in_progress" }`
- `completeTask(id, note)` → PATCH `/work-tasks/{id}/status` with `{ status: "done", completion_note: note }`
- Both include `Authorization: Bearer {token}` header

### Log Submit + Photo Handling (MAJ-006.3)
`farmingLogService.submitLog()` correctly:
- Builds `FormData` with `work_task_id`, `notes`, timestamps, and photo URIs
- Photos sent as `{ uri, type: 'image/jpeg', name: 'photo.jpg' }` blobs
- Endpoint: `POST /work-tasks/{taskId}/logs`

### Offline Conflict Handling (MAJ-006.4)
`offlineQueueService` covers:
- `enqueue()` → stores to AsyncStorage, prevents duplicates by `localId`
- `retry()` → submits queued log, handles `TASK_TERMINAL_STATE_CONFLICT` as conflict (no retry increment)
- `markConflict()` → sets `syncStatus: 'conflict'`, preserves `retryCount`
- `markFailed()` → increments `retryCount` on network/server errors
- `cleanSynced()` → removes successfully synced items from queue

## Files Changed

1. **`mobile/field-app/__tests__/services/workTask.test.ts`** (NEW)
   - 5 tests covering task receive, filtering, status transitions
   - Follows existing test patterns from `offlineQueue.test.ts`

2. **`.sisyphus/evidence/mobile-smoke-continuation.md`** (NEW)
   - This file

## What Still Requires Real Device/Emulator

| Gap | Why It Matters |
|-----|----------------|
| Camera access via `expo-camera` | Photo URI format may differ on device |
| `expo-image-picker` permission flow | Permission prompts not testable in Jest |
| Actual offline → online sync | Network state simulation not available |
| AsyncStorage on real device | iOS/Android storage limits unknown |
| Push notification delivery | Not testable without Expo Push service |
| Screen navigation (`@react-navigation`) | `TodayScreen` component not tested in isolation |

**Conclusion**: Unit-level smoke passes. Integration smoke requires Expo dev build or emulator run with `npm start`.