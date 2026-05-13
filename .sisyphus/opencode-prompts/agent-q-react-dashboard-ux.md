# Agent Q - React Operations Dashboard UX

You are Agent Q for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Improve the React operations/admin dashboard so farm operators can see urgent work and act with fewer steps.

## Context

- Laravel + React/Vite app.
- Use `rtk` before every command.
- The operations SPA lives under `/operations`.
- Current dashboard file: `resources/js/pages/OperationsDashboard.jsx`.
- Keep the UI operational and data-dense, not a marketing page.

## Ownership

You own:

- `resources/js/pages/OperationsDashboard.jsx`
- `.sisyphus/evidence/task-15-react-dashboard-ux.md`

Do not touch:

- auth files
- API client files
- Laravel routes/controllers
- tests unless absolutely needed for your owned UI file
- Expo/mobile files

## Requirements

- Preserve existing API calls: `/alerts`, `/planting-batches`, `/work-tasks`.
- Add a compact overview area that surfaces:
  - critical/warning alert counts
  - overdue/open task count where data allows
  - active/in-progress batch count where data allows
- Improve tab labels/copy in Vietnamese.
- Add clear error and retry state if dashboard API loading fails.
- Keep search + sort, and make it obvious which list is being filtered.
- Improve mobile responsiveness for tables; do not allow table text to overflow incoherently.
- Keep controls low-friction: no extra screens, no landing page, no explanatory feature text.

## Verification

Run:

```bash
rtk npm run build
rtk php artisan test tests/Feature/OperationsWebRoutesTest.php
```

Write `.sisyphus/evidence/task-15-react-dashboard-ux.md` with files changed, UX changes, commands run, and remaining risks.

