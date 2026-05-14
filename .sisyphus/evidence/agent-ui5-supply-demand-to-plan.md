# Agent UI5 - Supply Demand to Plan Evidence

Date: 2026-05-14

## Outcome

Implemented the missing supply input to production planning flow.

New routes:

- `/operations/supply/contracts`
- `/operations/supply/demands`

New files:

- `resources/js/pages/operations/supply/SupplyContractsPage.jsx`
- `resources/js/pages/operations/supply/SupplyDemandsPage.jsx`

Updated:

- `resources/js/OperationsApp.jsx`
- `resources/js/components/operations/OperationsLayout.jsx`
- `resources/js/pages/operations/PlanningCalculator.jsx`

## Flow Added

- Users can list and create supply contracts.
- Users can list and create one-off or contract-linked supply demands.
- Contract/demand rows can open `/operations/planning` prefilled with crop, farm, quantity, unit, frequency, and target date.
- `PlanningCalculator` now reads query params and saves `supply_contract_id` / `supply_demand_id` into the production plan when present.

## Constraints Followed

- No endpoints invented.
- Used existing API contracts:
  - `GET/POST /supply-contracts`
  - `GET/POST /supply-demands`
  - `POST /planning/calculate`
  - `POST /production-plans`
- Axios calls do not prefix `/api/v1` because `resources/js/services/api.js` already sets the base URL.

## Verification

- `rtk npm run build` passed.
- `rtk php artisan test tests/Feature/SupplyInputApiTest.php tests/Feature/PlanningApiTest.php` passed: 7 tests, 66 assertions.

