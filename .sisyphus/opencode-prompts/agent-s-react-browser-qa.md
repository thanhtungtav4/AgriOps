# Agent S - React Operations Browser QA

You are Agent S for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Run browser/build QA for the React operations/admin surface and produce actionable evidence.

## Ownership

You own only:

- `.sisyphus/evidence/task-15-react-browser-qa.md`
- optional screenshots under `.sisyphus/evidence/`

Do not edit application code.

## Checks

- Run build and route tests.
- If a local server is available or easy to start, inspect `/operations` and `/operations/login`.
- Capture console errors, broken assets, layout overlap, and mobile/desktop obvious issues.
- Note whether auth blocks dashboard as expected when no token exists.

## Verification

Preferred commands:

```bash
rtk npm run build
rtk php artisan test tests/Feature/OperationsWebRoutesTest.php
```

Write `.sisyphus/evidence/task-15-react-browser-qa.md` with commands, results, console/UI findings, and recommended fixes.

