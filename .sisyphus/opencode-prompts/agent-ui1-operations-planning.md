# Agent UI1 - Operations App Shell + Planning Calculator

You are Agent UI1 for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Make the visible `/operations` site meaningfully closer to the BRD by adding a real operations app shell and a planning calculator screen matching BRD section 27.

## Hard Rules

- Prefix commands with `rtk`.
- Do not revert unrelated work.
- Own only:
  - `resources/js/OperationsApp.jsx`
  - `resources/js/pages/OperationsDashboard.jsx`
  - new files under `resources/js/pages/operations/`
  - new files under `resources/js/components/operations/`
  - `resources/js/services/api.js` only if necessary for small helper methods
  - tests only if this repo already has frontend tests for these files
  - `.sisyphus/evidence/agent-ui1-operations-planning.md`
- Do not edit:
  - Laravel controllers/models/migrations/routes
  - Filament resources
  - mobile app

## Context

- Gap evidence: `.sisyphus/evidence/site-gap-vs-brd-2026-05-14.md`
- BRD: `.docs/yeu_cau_crm_nong_nghiep_v1.md`, especially section 27.
- Current `/operations` has only login + dashboard tabs for alerts/batches/tasks.
- API routes available:
  - `GET /api/v1/farms`
  - `GET /api/v1/crops`
  - `GET /api/v1/crop-varieties`
  - `POST /api/v1/planning/calculate`
  - `POST /api/v1/production-plans`
  - `GET /api/v1/production-plans`
  - plus alerts, planting-batches, work-tasks.

## Implementation Requirements

1. Add an operations shell with navigation:
   - Tổng quan
   - Lập kế hoạch
   - Lứa trồng
   - Công việc
2. Preserve existing dashboard behavior.
3. Add Planning page:
   - Load farms/crops/varieties.
   - Input fields:
     - farm
     - crop
     - variety optional
     - required quantity
     - unit
     - period type or frequency
     - start/end date if backend expects it
   - Call `POST /planning/calculate`.
   - Render output cards for the BRD section 27 outputs:
     - finished quantity
     - raw harvest needed
     - plants needed
     - area needed
     - cycles/batches
     - labor estimate
     - water estimate
     - loss estimate
     - cost/margin if returned
   - Provide a save/create production plan action only if the API contract is clear from `PlanningController`.
4. Use restrained operational UI, dense but readable. No landing page.
5. Keep mobile responsive.

## Verification

- Run `rtk npm run build`.
- If feasible, run `rtk php artisan test tests/Feature/PlanningApiTest.php`.
- Write evidence with:
  - changed files
  - API assumptions
  - commands run and results
  - remaining gaps.

