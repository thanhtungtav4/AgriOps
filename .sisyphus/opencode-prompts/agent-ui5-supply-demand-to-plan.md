# Agent UI5 - Supply Demand to Plan Flow

Read first:

- `.sisyphus/AGENT_MISSION_BRIEF.md`
- `.sisyphus/opencode-prompts/STRICT_AGENT_TEMPLATE.md`
- `.sisyphus/evidence/site-flow-gap-review-round2-2026-05-14.md`
- `app/Http/Controllers/Api/V1/SupplyContractController.php`
- `app/Http/Controllers/Api/V1/SupplyDemandController.php`
- `app/Http/Controllers/Api/V1/PlanningController.php`
- `resources/js/pages/operations/PlanningCalculator.jsx`

Task:

Expose supermarket/customer supply input and connect it to planning.

Scope:

- Add `resources/js/pages/operations/supply/SupplyContractsPage.jsx`.
- Add `resources/js/pages/operations/supply/SupplyDemandsPage.jsx`.
- Wire routes:
  - `/operations/supply/contracts`
  - `/operations/supply/demands`
- Add navigation entry or group for "Cung ứng".
- Support list/create for contracts and demands using existing API contracts.
- Add action from demand/contract row to `/operations/planning` with query params for crop, quantity, unit, frequency, target date when possible.
- Update `PlanningCalculator.jsx` to read query params and prefill fields if not already implemented.

Constraints:

- Do not invent update/delete routes.
- Do not add backend fields unless tests prove the store contract cannot link plans to demand/contract.
- Use existing crop/farm selects from API.

Verification:

- Run `rtk npm run build`.
- Run focused tests: `rtk php artisan test tests/Feature/SupplyInputApiTest.php tests/Feature/PlanningApiTest.php`.
- Write evidence to `.sisyphus/evidence/agent-ui5-supply-demand-to-plan.md`.

