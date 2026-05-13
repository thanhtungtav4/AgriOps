# Agent U - NVIDIA Operations Routes and Build Tests

You are Agent U for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Strengthen route/build confidence for the React operations/admin surface without changing UI implementation.

## Ownership

You own:

- `tests/Feature/OperationsWebRoutesTest.php`
- `.sisyphus/evidence/task-15-react-operations-routes-tests.md`

Do not touch:

- React component files
- auth/API client files
- Laravel route definitions unless a route test reveals a clear mismatch and you document it first

## Requirements

- Ensure `/operations`, `/operations/login`, and nested `/operations/*` return the operations app view.
- Ensure the operations view includes the React/Vite mount point or app asset entry.
- Avoid duplicating another existing test class if possible; consolidate only inside your owned test file.

## Verification

Run:

```bash
rtk php artisan test tests/Feature/OperationsWebRoutesTest.php
rtk npm run build
```

Write `.sisyphus/evidence/task-15-react-operations-routes-tests.md` with files changed, tests added, commands run, and remaining risks.

