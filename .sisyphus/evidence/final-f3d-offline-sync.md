# F3d Offline Sync Contract Gate Analysis

## Overview
This document analyzes the Expo offline queue/client_uuid behavior and Laravel WorkTaskLog API implementation for the offline sync contract gate.

## Key Findings

### 1. Offline Queue Implementation (Mobile App)
The mobile field application implements an offline queue system using React Native AsyncStorage:

- **Queue Structure**: Uses `@ariops:log_queue` key to store serialized queued logs
- **Log Structure**: `QueuedLog` type includes `localId` (client UUID), `taskId`, `notes`, `photoUris`, timestamps, metadata, and sync status
- **Duplicate Prevention**: The `enqueue` method checks for existing logs with same `localId` and prevents duplicates
- **Sync Status Tracking**: Logs maintain status (`pending`, `uploading`, `synced`, `failed`, `conflict`)
- **Retry Logic**: Implements exponential backoff with configurable maximum retries

### 2. Client UUID/Local ID Usage
The mobile app uses the `localId` (which serves as client UUID) in the following ways:

- **Enqueue Process**: Each log entry gets a unique localId upon queuing
- **API Submission**: The `client_uuid` parameter is passed to the server API endpoint
- **Duplicate Detection**: The localId serves as the primary identifier for preventing duplicates

### 3. Laravel WorkTaskLog API Behavior
Initial review found that the mobile app sent `client_uuid`, but the Laravel API ignored it during validation/persistence. This was fixed during the final gate.

- **Endpoint**: `/api/v1/work-tasks/{id}/logs` (POST)
- **Client UUID Parameter**: Validated as nullable string max 120 chars
- **Idempotency Key Handling**: API checks existing `farming_logs` by `(work_task_id, client_uuid)` before creating a new log
- **Duplicate Prevention**: Database unique constraint on `(work_task_id, client_uuid)` and controller returns the existing log with HTTP 200 for duplicate retries

### 4. Conflict Behavior
The system has limited conflict handling:

- **Status Change Conflicts**: No specific handling for task status changes during offline submission
- **Race Conditions**: Potential for race conditions between online and offline submissions
- **Data Consistency**: No explicit conflict resolution strategy

### 5. Testing Coverage
The existing tests cover:

- Basic enqueue functionality
- Duplicate prevention
- Queue retrieval
- Status updates (synced, failed)
- Queue cleaning (removing synced logs)
- Retry mechanism

Added during final gate:
- `WorkTaskLogApiTest::test_client_uuid_makes_work_task_log_submission_idempotent`
- Focused result: PASS, 9 tests / 35 assertions for `WorkTaskLogApiTest`
- Full regression: PASS, 281 tests / 963 assertions after conflict hardening

Remaining tests not covered:
- Conflict scenarios when task status changes before retry
- Race conditions between concurrent submissions

## Recommendations

### Immediate Actions:
1. Done: server-side idempotency key handling in `WorkTaskLogController`
2. Done: database unique constraint for `(work_task_id, client_uuid)`
3. Follow-up: add richer conflict response when task status changes before retry

### Future Enhancements:
1. Add conflict resolution strategies for different scenarios
2. Implement more sophisticated retry policies
3. Add better monitoring and alerting for sync failures
