# Final Flow Hardening - Allocation, QR, Photos

Date: 2026-05-14

## Outcome

Closed three important gaps from the BRD flow review:

1. Planting batch land/bed allocation is now exposed through API and UI.
2. Work task logs now support real image uploads from the React operations UI.
3. Packing QR rows now expose public traceability actions.

## Backend Added

- `app/Http/Controllers/Api/V1/PlantingBatchAllocationController.php`
- Routes:
  - `POST /api/v1/planting-batches/{id}/allocations`
  - `DELETE /api/v1/planting-batches/{id}/allocations/{allocationId}`
- Test:
  - `tests/Feature/PlantingBatchAllocationApiTest.php`

## Frontend Updated

- `resources/js/pages/operations/BatchDetailPage.jsx`
  - Added land/bed allocation panel.
  - Loads farm plots and plot beds.
  - Creates/removes allocations.
  - Can generate work tasks for a specific allocation.
- `resources/js/pages/operations/tasks/TaskDetailPage.jsx`
  - Added real image upload using multipart `photos[]`.
  - Keeps existing `photo_paths` fallback.
- `resources/js/pages/operations/fulfillment/FulfillmentFlow.jsx`
  - Added public traceability URL open/copy actions for packing lot QR codes.
- `app/Http/Controllers/Api/V1/PlantingBatchController.php`
  - Batch show now includes allocation beds as well as plots.

## Verification

- `rtk npm run build` passed.
- Focused tests passed:
  - `tests/Feature/PlantingBatchAllocationApiTest.php`
  - `tests/Feature/PlantingBatchAllocationGuardTest.php`
  - `tests/Feature/WorkTaskLogApiTest.php`
  - `tests/Feature/PublicTraceabilityApiTest.php`
  - Result: 36 tests, 117 assertions.
- Full backend suite passed:
  - `rtk php artisan test`
  - Result: 372 tests, 1952 assertions.

