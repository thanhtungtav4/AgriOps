# Agent Admin1 - Filament Resources for Missing MVP Modules

You are Agent Admin1 for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Expose implemented backend models through Filament admin resources so the site covers more of the v1 BRD. Focus on CRUD/admin visibility, not custom workflows.

## Hard Rules

- Prefix commands with `rtk`.
- Do not revert unrelated work.
- Own only:
  - new files under `app/Filament/Resources/`
  - `.sisyphus/evidence/agent-admin1-filament-resources.md`
- Do not edit:
  - existing controllers/models/migrations unless absolutely blocked
  - React `/operations`
  - mobile app
  - routes

## Context

- Current Filament resources: Farm, Plot, Bed, Crop, CropVariety, GrowthStage, ProductStandard.
- Gap evidence: `.sisyphus/evidence/site-gap-vs-brd-2026-05-14.md`.
- Filament is v4. Resource property types must match:
  - `protected static string | \UnitEnum | null $navigationGroup`
  - `protected static string | \BackedEnum | null $navigationIcon`

## Scope

Create Filament resources for these implemented models, prioritizing simple usable CRUD:

P0:
- SupplyContract
- SupplyDemand
- ProductionPlan
- PlantingBatch
- WorkTask
- SoilHistory
- ChemicalProduct
- ChemicalUsage
- PreHarvestInspection
- HarvestLot
- PackingLot
- DeliveryNote
- ReturnRecord
- PostSeasonReview
- CostBreakdown

P1 if time remains:
- HarvestModel
- IrrigationNorm
- FertilizerNorm
- LaborNorm
- LossProfile
- PlantingBatchAllocation
- FarmingLog
- Incident
- ProcessingRecord
- PriceTable
- CostRecord
- Alert
- AuditEvent

## Implementation Requirements

- Follow existing resource style.
- Use labels in Vietnamese where straightforward.
- Use relationship selects for `farm`, `crop`, `variety`, `batch`, `plot`, `bed`, etc.
- Keep forms pragmatic: include important scalar fields and notes/status fields.
- Tables should include searchable IDs/codes/names/status/farm/date columns.
- Add status filters where useful.
- Avoid destructive bulk actions on operational records if risky; edit/view is enough.
- If a model has no obvious fillable fields, inspect the model/migration and build from that.

## Verification

- Run `rtk php artisan route:list`.
- Run `rtk php artisan test` if resource generation did not take too long.
- Write evidence with:
  - resources created
  - commands run and results
  - resources intentionally deferred.

