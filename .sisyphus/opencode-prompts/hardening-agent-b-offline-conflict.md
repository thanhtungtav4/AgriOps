# AgriOps Hardening Agent B - Offline Conflict UX/API

You are working in `/Users/macbook/Herd/ariops`.

Follow local repo conventions and do not revert changes by other agents. This is a coding task.

## Goal

Improve offline retry conflict handling beyond duplicate prevention.

Current state:

- Mobile sends `client_uuid`.
- Laravel now enforces idempotency for duplicate work-log retries.
- Remaining gap: when a queued mobile log retries after the task becomes cancelled/done/otherwise terminal, the API response and mobile queue state should expose a clear conflict state.

## Ownership

You may edit:

- `app/Http/Controllers/Api/V1/WorkTaskLogController.php`
- `app/Http/Responses/ApiResponse.php` only if needed
- `tests/Feature/WorkTaskLogApiTest.php`
- `mobile/field-app/src/services/farmingLog.ts`
- `mobile/field-app/src/types/index.ts`
- `mobile/field-app/__tests__/services/offlineQueue.test.ts`
- `.sisyphus/evidence/hardening-b-offline-conflict.md`

Do not edit lifecycle/allocation services or database migrations unless absolutely required.

## Required Behavior

1. Duplicate `client_uuid` retry must keep working.
2. If task is cancelled before retry, API should return a conflict/domain response with enough current task state and next action guidance for the mobile UI.
3. Mobile offline queue should mark this case as `conflict`, not generic `failed`, while keeping other network/server errors as `failed`.
4. Add Laravel and mobile Jest tests.
5. Run focused Laravel test, mobile lint/test if feasible, and full Laravel suite if feasible.

## Evidence

Write `.sisyphus/evidence/hardening-b-offline-conflict.md` with:

- files changed
- behavior implemented
- tests run and results
- any limitations

