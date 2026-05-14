# Agent UI2 - Plan to Batch to Tasks Flow

You are Agent UI2 for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Required Reading

Before coding, read:

- `.sisyphus/AGENT_MISSION_BRIEF.md`
- `.sisyphus/opencode-prompts/STRICT_AGENT_TEMPLATE.md`
- `.docs/yeu_cau_crm_nong_nghiep_v1.md`
- `app/Http/Controllers/Api/V1/PlanningController.php`
- `app/Http/Controllers/Api/V1/PlantingBatchController.php`
- `app/Http/Controllers/Api/V1/WorkTaskController.php`
- `tests/Feature/PlanningApiTest.php`
- `tests/Feature/PlantingBatchApiTest.php`
- `tests/Feature/WorkTaskApiTest.php`
- current React files under `resources/js/OperationsApp.jsx`, `resources/js/components/operations/OperationsLayout.jsx`, `resources/js/pages/operations/PlanningCalculator.jsx`

## Product Task

The planning calculator exists, but the flow after planning is missing.

Implement the operational flow:

1. From planning calculation result, allow creating a `ProductionPlan` with the real `POST /production-plans` contract.
2. Add a Production Plans page to list saved plans.
3. From a production plan, allow creating a `PlantingBatch` with the real `POST /planting-batches` contract.
4. Add a real Batches page, not just the dashboard reused.
5. Add batch detail actions:
   - view batch info
   - lifecycle transition using `PATCH /planting-batches/{id}/transition`
   - generate work tasks using `POST /planting-batches/{id}/generate-work-tasks`
   - show generated/listed work tasks for that batch if API supports filtering, otherwise show all tasks and document the gap.

## Ownership

You may edit only:

- `resources/js/OperationsApp.jsx`
- `resources/js/components/operations/OperationsLayout.jsx`
- `resources/js/pages/operations/PlanningCalculator.jsx`
- new files under `resources/js/pages/operations/`
- new files under `resources/js/components/operations/`
- `.sisyphus/evidence/agent-ui2-plan-to-batch-flow.md`

Do not edit Laravel PHP files, Filament resources, migrations, tests, mobile app, or unrelated docs.

## API Contract Guardrails

Do not invent request keys. Use only what controllers/tests validate.

Known contracts to verify in code:

### Create production plan

`POST /api/v1/production-plans`

Required:

- `crop_id`
- `farm_id`
- `quantity`
- `unit`
- `target_delivery_date`

Optional:

- `variety_id`
- `supply_contract_id`
- `supply_demand_id`
- `estimated_cost`
- `estimated_revenue`
- `margin_percent`
- `status`
- `notes`

### Create planting batch

`POST /api/v1/planting-batches`

Required:

- `crop_id`

Optional:

- `farm_id`
- `variety_id`
- `production_plan_id`
- `code`
- `planned_quantity`
- `planned_unit`
- `planned_area_m2`
- `planned_start_date`
- `planned_harvest_date`
- `notes`
- `metadata`

### Transition batch

`PATCH /api/v1/planting-batches/{id}/transition`

Required:

- `to_status`

Optional:

- `reason`

## UI Requirements

- Keep operational UI dense, restrained, and responsive.
- Add navigation items only for pages you implement.
- Do not add decorative landing pages.
- Reuse existing API service.
- Use clear status badges and action buttons.
- Make error states visible.
- Make it possible to continue work after a successful action without reloading the whole app.

## Verification

Run:

- `rtk npm run build`
- `rtk php artisan test tests/Feature/PlanningApiTest.php tests/Feature/PlantingBatchApiTest.php tests/Feature/WorkTaskApiTest.php`

Write evidence with changed files, real API contract used, commands/results, and remaining gaps.

