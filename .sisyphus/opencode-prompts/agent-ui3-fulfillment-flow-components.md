# Agent UI3 - Fulfillment Flow Pages

You are Agent UI3 for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Required Reading

Before coding, read:

- `.sisyphus/AGENT_MISSION_BRIEF.md`
- `.sisyphus/opencode-prompts/STRICT_AGENT_TEMPLATE.md`
- `.docs/yeu_cau_crm_nong_nghiep_v1.md`
- `app/Http/Controllers/Api/V1/PreHarvestInspectionController.php`
- `app/Http/Controllers/Api/V1/HarvestLotController.php`
- `app/Http/Controllers/Api/V1/PackingLotController.php`
- `app/Http/Controllers/Api/V1/DeliveryController.php`
- `app/Http/Controllers/Api/V1/ReturnRecordController.php`
- feature tests for these controllers:
  - `tests/Feature/PreHarvestInspectionApiTest.php`
  - `tests/Feature/HarvestLotApiTest.php`
  - `tests/Feature/PackingLotApiTest.php`
  - `tests/Feature/DeliveryReturnApiTest.php`

## Product Task

Prepare the fulfillment flow after work tasks/batches:

Inspection → Harvest Lot → Packing Lot / QR → Delivery → Returns.

This task creates pages/components, but does not wire routes in `OperationsApp.jsx` or edit `OperationsLayout.jsx`. The coordinator will wire routes after review to avoid conflicts with UI2.

## Ownership

You may edit only:

- new files under `resources/js/pages/operations/fulfillment/`
- new files under `resources/js/components/operations/fulfillment/`
- `.sisyphus/evidence/agent-ui3-fulfillment-flow-components.md`

Do not edit:

- `resources/js/OperationsApp.jsx`
- `resources/js/components/operations/OperationsLayout.jsx`
- existing React pages
- Laravel PHP files
- Filament resources
- mobile app

## Implementation Requirements

Create a `FulfillmentFlow.jsx` page component that can be routed later. It should:

1. Load and show:
   - pre-harvest inspections
   - harvest lots
   - packing lots
   - deliveries
   - returns
2. Provide simple creation forms/actions using real API contracts only:
   - create inspection
   - approve/reject inspection
   - create harvest lot
   - create packing lot from harvest lots
   - create delivery
   - create return record
3. If a request contract is too complex, implement read-only list plus document exactly what is missing.
4. Show QR code/public traceability value if returned by packing lot API.
5. Use lightweight local state and existing `api` service.

## Guardrails

- Do not invent request keys.
- Do not invent response keys.
- Do not add fake fields that are not accepted by the controller.
- Prefer a working thin flow over a pretty but broken large form.

## Verification

Run:

- `rtk npm run build`
- Focused tests:
  - `rtk php artisan test tests/Feature/PreHarvestInspectionApiTest.php tests/Feature/HarvestLotApiTest.php tests/Feature/PackingLotApiTest.php tests/Feature/DeliveryReturnApiTest.php`

Write evidence with changed files, API contracts used, commands/results, and remaining gaps.

