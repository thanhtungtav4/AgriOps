# Agent R - React Operations Auth and API Resilience

You are Agent R for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Harden the React operations login/auth/API layer so admin users get clear errors and fewer dead ends.

## Context

- Laravel API uses `/api/v1`.
- Auth response/error shape may be either `data` or `error`.
- Current files:
  - `resources/js/context/AuthContext.jsx`
  - `resources/js/services/api.js`
  - `resources/js/pages/LoginPage.jsx`

## Ownership

You own:

- `resources/js/context/AuthContext.jsx`
- `resources/js/services/api.js`
- `resources/js/pages/LoginPage.jsx`
- `.sisyphus/evidence/task-15-react-auth-api-resilience.md`

Do not touch:

- `resources/js/pages/OperationsDashboard.jsx`
- Laravel routes/controllers
- backend API contracts
- tests unless absolutely needed for auth/API behavior

## Requirements

- Normalize API error messages from both `error.message` and legacy `message`.
- Make stored user JSON parsing safe if localStorage is corrupted.
- Ensure logout clears axios Authorization header and localStorage.
- Ensure 401 redirects to `/operations/login` without infinite loops.
- Improve login UX in Vietnamese without adding extra steps.
- Keep login form compact and task-oriented.

## Verification

Run:

```bash
rtk npm run build
rtk php artisan test tests/Feature/OperationsWebRoutesTest.php
```

Write `.sisyphus/evidence/task-15-react-auth-api-resilience.md` with files changed, behavior summary, commands run, and remaining risks.

