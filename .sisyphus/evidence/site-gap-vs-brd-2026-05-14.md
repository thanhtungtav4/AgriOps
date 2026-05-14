# Site Gap vs BRD - 2026-05-14

## Summary

The current backend API is broad, but the visible site is much thinner than the v1 BRD. The `/operations` React surface only exposes alerts, planting batches, and work tasks. The Filament admin surface exposes only 7 master-data resources. Many implemented API modules therefore exist without equivalent user-facing screens.

## Verified Inventory

- Backend routes: `rtk php artisan route:list` succeeds and shows 134 routes.
- Backend tests: `rtk php artisan test` passed with 369 tests and 1943 assertions.
- Frontend build: `rtk npm run build` passed.
- Filament resources currently exposed:
  - Farm
  - Plot
  - Bed
  - Crop
  - Crop Variety
  - Growth Stage
  - Product Standard
- `/operations` React pages currently exposed:
  - Login
  - Dashboard with tabs: alerts, planting batches, work tasks.
- Mobile app currently exposed:
  - Login
  - Today/task workflow
  - Work task and farming log services

## Immediate Fix Done

Filament `Resource` classes used navigation property types that crashed artisan route discovery under Filament v4. Updated resource property types to match the framework parent properties:

- `string | \UnitEnum | null $navigationGroup`
- `string | \BackedEnum | null $navigationIcon`

## Coverage Status By BRD Area

| BRD area | Backend/API | Visible web/admin | Gap |
|---|---:|---:|---|
| Farm / plot / bed | Yes | Partial admin | Need richer operational views and filters |
| Soil history | Yes | No | Need admin + operations screens |
| Crop catalog / varieties / standards / stages | Yes | Partial admin | Need norms screens for irrigation/fertilizer/labor/loss/harvest models |
| Supply contracts / demands | Yes | No | Need contract/demand CRUD and planning entry screen |
| Production planning calculator | Yes | No | Need BRD section 27 input/output screen |
| Planting batch lifecycle | Yes | Read-only operations list | Need detail page, transitions, allocation UI, generated tasks UI |
| Work tasks | Yes | Partial operations list | Need assignee flow, status actions, log entry, evidence/image upload |
| Farming logs | Yes | Mobile partial | Need web review/audit view and image support checks |
| Incidents / chemical usages / chemical products | Yes | No | Need safety workflow UI and stock/PHI visibility |
| Pre-harvest inspection | Yes | No | Need approval/rejection UI |
| Harvest / processing / packing | Yes | No | Need end-to-end lot flow screens |
| QR traceability | Yes | Public page exists | Need packing-lot QR creation/print action in UI |
| Delivery / acceptance / returns | Yes | No | Need delivery workflow and proof upload UI |
| Price / cost / margin / cost breakdown | Yes | No | Need dashboards and drilldowns |
| Post-season review / suggested norm updates | Yes | No | Need approve/reject screen and norm update proposal visibility |
| Alerts | Yes | Partial operations list | Need trigger/mark/read filters and source drilldown |
| User/role permissions | Partial | Filament default/auth only | Need admin user management and role/farm assignment screens |
| Reports | Partial APIs/services | No | Need production/yield/loss/cost/quality/post-season report pages |

## Priority Build Plan

### P0 - Make site navigable for the actual MVP

1. Expand `/operations` into a real app shell with modules:
   - Overview
   - Planning
   - Batches
   - Tasks
   - Harvest/packing/delivery
   - Safety
   - Reports
2. Add planning calculator screen matching BRD section 27:
   - Farm, crop/variety, demand quantity, unit, period, delivery frequency, date range
   - Output: finished quantity, raw harvest, plants, area, cycles, labor, water, loss, estimated margin
3. Add planting batch detail/action screens:
   - Lifecycle transition
   - Land allocation
   - Generate work tasks
   - Linked inspections, harvest lots, logs

### P1 - Expose operational workflows already backed by APIs

1. Safety workflow screens:
   - Incidents
   - Chemical usages
   - Chemical product stock and low-stock list
   - Pre-harvest inspections
2. Supply and fulfillment screens:
   - Supply contracts/demands
   - Harvest lots
   - Processing records
   - Packing lots and QR action
   - Deliveries, acceptance records, returns
3. Finance screens:
   - Price tables
   - Cost records
   - Cost breakdown dashboard/compare
   - Margin dashboard

### P2 - Complete admin/master-data resources

Add Filament resources for implemented models that have no admin UI:

- SupplyContract, SupplyDemand, ProductionPlan
- HarvestModel, IrrigationNorm, FertilizerNorm, LaborNorm, LossProfile
- PlantingBatch, PlantingBatchAllocation
- WorkTask, FarmingLog, Incident
- ChemicalProduct, ChemicalUsage
- PreHarvestInspection, HarvestLot, ProcessingRecord, PackingLot, DeliveryNote, ReturnRecord
- PriceTable, CostRecord, CostBreakdown
- SoilHistory, PostSeasonReview, Alert, AuditEvent, User

### P3 - Reporting and post-season improvement loop

1. Report pages:
   - Production
   - Yield
   - Loss
   - Cost
   - Quality/returns
   - Post-season improvement
2. Management approval UI:
   - Suggested norm adjustments
   - Approve/reject with reason
   - Apply approved updates to catalog norms

## Suggested Agent Split

- Agent UI-1: `/operations` app shell + planning calculator page.
- Agent UI-2: planting batch detail/actions + work task action/log entry screens.
- Agent UI-3: harvest-processing-packing-delivery-return screens + QR action.
- Agent Admin-1: Filament resources for planning/catalog/norms.
- Agent Admin-2: Filament resources for operations/safety/finance/reports.
- Agent QA: route/build/browser smoke + BRD coverage checklist.
