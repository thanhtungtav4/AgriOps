# Agent QA1 - Site Gap Review and Smoke Check

You are Agent QA1 for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Review the current site against the v1 BRD and the latest working tree. Do not implement feature code. Produce a precise QA report that helps the coordinator decide the next fixes.

## Hard Rules

- Prefix commands with `rtk`.
- Read-only except writing `.sisyphus/evidence/agent-qa1-site-gap-review.md`.
- Do not edit app code.
- Do not revert changes.

## Context

- BRD: `.docs/yeu_cau_crm_nong_nghiep_v1.md`
- Gap evidence: `.sisyphus/evidence/site-gap-vs-brd-2026-05-14.md`
- Current site:
  - `/operations` React app
  - Filament admin at `/admin`
  - API under `/api/v1`
  - mobile field app under `mobile/field-app`

## Review Tasks

1. Run inventory:
   - `rtk php artisan route:list`
   - `rtk npm run build`
   - `rtk php artisan test` if reasonable
2. Inspect:
   - `resources/js/OperationsApp.jsx`
   - `resources/js/pages/OperationsDashboard.jsx`
   - `app/Filament/Resources`
   - `routes/api.php`
3. Compare with BRD scope and classify gaps:
   - P0 user-facing blocker
   - P1 MVP workflow missing
   - P2 admin/reporting polish
4. Look specifically for:
   - API modules with no UI
   - UI pages calling fields/routes that do not exist
   - Filament resources that would crash route discovery
   - role/farm scope concerns visible in UI/API wiring
   - places where tests pass but the site is not usable.

## Output

Write `.sisyphus/evidence/agent-qa1-site-gap-review.md` with:

- commands run and results
- findings ordered by severity
- file/route references
- recommended next agent tasks

