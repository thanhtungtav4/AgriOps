# Agent A Planning Prompt

You are Agent A Planning for `/Users/macbook/Herd/ariops`.

You are not alone in the codebase. Do not revert edits made by others. Keep your write scope narrow.

## Goal

Close Task 4 planning gaps from:

- `.sisyphus/agent-board.md`
- `.sisyphus/evidence/task-04-planning-formula-spec.md`
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`

## Ownership

You may edit:

- `app/Services/PlanningService.php`
- `tests/Feature/PlanningApiTest.php`
- `app/Http/Controllers/Api/V1/PlanningController.php` only if needed for validation/response shape
- `.sisyphus/evidence/task-04-*.log`
- `.sisyphus/evidence/task-04-tdd-trace.md`

Do not touch:

- auth/RBAC
- unrelated migrations
- QR
- delivery/returns
- React/Expo UI
- `routes/api.php` unless you stop and explain why

## Required Implementation

- Planning response includes `assumptions`.
- Planning response includes `fulfillment`.
- Plant counts include estimate and execution-safe whole-number values.
- Unsupported/non-canonical unit behavior returns a clear domain error.
- Existing happy path and missing norm behavior remain green.

## Required Tests

Add or update tests for:

- happy path includes assumptions and fulfillment
- missing norms returns `planning_error`
- plant execution quantities round up
- unsupported unit returns field-level planning error

## Commands

Use project conventions. Prefix shell commands with `rtk`.

Suggested:

```bash
rtk php artisan test tests/Feature/PlanningApiTest.php
```

## Evidence

Update:

- `.sisyphus/evidence/task-04-green.log`
- `.sisyphus/evidence/task-04-refactor.log`
- `.sisyphus/evidence/task-04-tdd-trace.md`

## Final Response Required

Report:

- files changed
- tests run
- evidence paths
- risks/open questions
- next recommended step
