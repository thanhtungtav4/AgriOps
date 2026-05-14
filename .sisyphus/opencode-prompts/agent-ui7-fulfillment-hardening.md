# Agent UI7 - Fulfillment Hardening

Read first:

- `.sisyphus/AGENT_MISSION_BRIEF.md`
- `.sisyphus/opencode-prompts/STRICT_AGENT_TEMPLATE.md`
- `.sisyphus/evidence/site-flow-gap-review-round2-2026-05-14.md`
- `resources/js/pages/operations/fulfillment/FulfillmentFlow.jsx`
- `app/Http/Controllers/Api/V1/PreHarvestInspectionController.php`
- `app/Http/Controllers/Api/V1/HarvestLotController.php`
- `app/Http/Controllers/Api/V1/PackingLotController.php`
- `app/Http/Controllers/Api/V1/DeliveryController.php`
- `app/Http/Controllers/Api/V1/ReturnRecordController.php`
- `app/Http/Controllers/Api/V1/TraceabilityController.php`

Task:

Convert fulfillment from raw ID CRUD into a guided operational flow.

Scope:

- In `FulfillmentFlow.jsx`, replace manual ID-only fields with selects from loaded batches/harvest lots/packing lots/deliveries where practical.
- Add packing QR actions:
  - display QR code/value
  - copy public traceability URL
  - open public traceability endpoint or show URL
- Add clear empty states and next-step CTAs.
- Keep route `/operations/fulfillment`.

Constraints:

- Do not add `/api/v1` prefix; Axios base URL already includes it.
- Do not invent publish endpoints if not present.
- Keep file scoped; split components only if it reduces complexity.

Verification:

- Run `rtk npm run build`.
- Run focused tests: `rtk php artisan test tests/Feature/PreHarvestInspectionApiTest.php tests/Feature/HarvestLotApiTest.php tests/Feature/PackingLotApiTest.php tests/Feature/DeliveryReturnApiTest.php tests/Feature/PublicTraceabilityApiTest.php`.
- Write evidence to `.sisyphus/evidence/agent-ui7-fulfillment-hardening.md`.

