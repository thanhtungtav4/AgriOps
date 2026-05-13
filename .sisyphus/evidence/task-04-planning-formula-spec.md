# Task 04 Planning Formula Spec

Date: 2026-05-12
Owner: Product/CTO/Domain
Scope: MVP-0 planning blocker for Demand & Contract Input + Planning API v1

## Purpose

This spec defines the planning calculation contract for AgriOps before downstream modules create planting batches, allocations, work tasks, harvest lots, packing lots, and QR traceability.

The planning engine must answer:

- How much finished quantity is needed for the customer.
- How much raw harvest is needed before losses.
- How many productive plants are needed.
- How many plants must be planted after survival loss.
- How many square meters are required.
- How many labor hours/workers are estimated.
- Whether the plan is feasible or has shortages/warnings.
- Which assumptions and norms were used.

## Current Implementation Baseline

Current code path:

- Service: `app/Services/PlanningService.php`
- API: `POST /api/v1/planning/calculate`
- Persistence: `supply_contracts`, `supply_demands`, `production_plans`
- Evidence already present:
  - `.sisyphus/evidence/task-04-green.log`
  - `.sisyphus/evidence/task-04-migration.log`
  - `.sisyphus/evidence/task-04-routes.log`

Current implemented inputs:

- `quantity`
- `unit`
- `frequency`
- `crop_id`
- `variety_id`
- `farm_id`
- `target_date`

Current implemented norms:

- `lossProfiles`
- `harvestModels`
- `laborNorms`

Current implemented outputs:

- `delivery_quantity`
- `raw_harvest_quantity`
- `gross_yield`
- `plants_needed`
- `plants_to_plant`
- `area_m2`
- `labor_hours`
- `estimated_workers`
- `estimated_cost`
- `estimated_revenue`
- `margin_percent`
- `days_to_first_harvest`
- `estimated_planting_date`
- `estimated_first_harvest_date`
- `norms_used`
- `safety_warnings`

## MVP-0 Contract

### Required Inputs

Minimum UX input should remain 3 fields when enough defaults exist:

- `crop_id` or `variety_id`
- `quantity`
- `frequency`

API-level calculation requires:

- `quantity`: numeric, greater than 0.
- `unit`: canonical unit.
- `frequency`: `once`, `daily`, `weekly`, `monthly`, or `seasonal`.
- `crop_id`: required.
- `variety_id`: optional.
- `farm_id`: optional but required before creating an approved production plan.
- `target_date`: required for date calculation.

### Recommended Inputs Before MVP-1

These fields may be explicit request fields or derived from related records:

- `season`: current season or target crop season.
- `climate_zone`: from selected farm, or explicit override.
- `customer_name` / `customer_id`: from contract or demand.
- `customer_channel`: supermarket, restaurant, wholesale, retail, export, other.
- `delivery_tolerance_percent`: accepted over/under supply tolerance.
- `price_reference` or `price_snapshot`: for margin estimate.
- `planning_horizon_days`: for recurring demand expansion.

If any recommended input is missing, the response must include the default assumption used.

## Canonical Units

### Product Quantity Units

MVP units:

- `kg`
- `trái`
- `bó`
- `thùng`

Future-safe units:

- `g`
- `tấn`
- `khay`
- `túi`

### Area Units

Internal canonical area unit:

- `m2`

Optional display conversion:

- `ha = m2 / 10000`

### Labor Units

Internal canonical labor units:

- `labor_hours`
- `worker_day = labor_hours / 8`

### Unit Conversion Rule

The planning engine must not silently convert product units unless a conversion profile exists.

Examples:

- `kg -> raw kg`: allowed for weight-based crops.
- `trái -> kg`: only allowed if average weight per fruit exists.
- `bó -> kg`: only allowed if bunch weight standard exists.
- `thùng -> kg`: only allowed if package standard exists.

Missing conversion profile must return a domain error, not a generic 500.

## Frequency Expansion

Frequency describes demand cadence. Calculation must support two modes:

### Single Target Calculation

Used for first MVP API response:

- `once`: quantity is the target delivery quantity.
- `daily`: quantity is daily target quantity.
- `weekly`: quantity is weekly target quantity.
- `monthly`: quantity is monthly target quantity.
- `seasonal`: quantity is seasonal target quantity.

### Planning Horizon Calculation

Used when creating production plans from contracts:

- `daily_total = quantity * horizon_days`
- `weekly_total = quantity * ceil(horizon_days / 7)`
- `monthly_total = quantity * ceil(horizon_days / 30)`
- `seasonal_total = quantity for the configured season window`

The response must state whether it is a single target calculation or horizon-expanded calculation.

## Formula Definitions

### Loss Rate

Current implemented formula:

```text
total_loss_percent =
  harvest_loss_percent
  + processing_loss_percent
  + packing_loss_percent
  + non_grade_a_percent
  + reject_percent
```

Validation:

```text
total_loss_percent < 100
```

MVP-0 accepted behavior:

- If loss profile is missing, return `planning_error`.
- If total loss is `>= 100`, return `planning_error`.

Future refinement:

- Losses may need staged compounding instead of a flat sum.
- For MVP, flat sum is accepted if documented in `assumptions`.

### Raw Harvest Quantity

```text
raw_harvest_quantity = delivery_quantity / (1 - total_loss_percent / 100)
```

### Productive Plants Needed

```text
plants_needed = raw_harvest_quantity / avg_yield_per_plant
```

Validation:

- `avg_yield_per_plant > 0`

### Plants To Plant

```text
plants_to_plant = plants_needed / (survival_rate / 100)
```

Validation:

- `survival_rate > 0`
- `survival_rate <= 100`

### Area Required

```text
area_m2 = plants_to_plant / planting_density_per_m2
```

Validation:

- `planting_density_per_m2 > 0`

### Labor Estimate

```text
labor_hours = area_m2 * hours_per_m2
worker_days = labor_hours / 8
```

Validation:

- `hours_per_m2 >= 0`

### Cost Estimate

Current MVP formula:

```text
estimated_cost = area_m2 * cost_per_m2
```

Future additions:

- seed cost snapshot
- fertilizer usage cost
- chemical/biological usage cost
- water cost
- packaging cost, explicitly out of MVP unless enabled later
- labor actual vs planned

### Revenue Estimate

Current MVP formula:

```text
estimated_revenue = delivery_quantity * sale_price_per_unit
```

Contract-aware formula:

```text
estimated_revenue = delivery_quantity * price_snapshot
```

Rule:

- Contract/price snapshot should override crop default sale price when available.

### Margin

```text
margin = estimated_revenue - estimated_cost
margin_percent = margin / estimated_revenue * 100
```

If revenue is 0, `margin_percent` must be 0 and the response must include a warning.

## Rounding Rules

API response should display rounded values, but internal planning snapshots should preserve precision.

Recommended rules:

- `delivery_quantity`: 2 decimals for weight, 0 decimals for count-like units.
- `raw_harvest_quantity`: 2 decimals for weight, 0 decimals for count-like units.
- `plants_needed`: round up to whole plant for execution.
- `plants_to_plant`: round up to whole plant for execution.
- `area_m2`: 2 decimals for estimate, optionally round up allocation request.
- `labor_hours`: 2 decimals.
- `worker_days`: 2 decimals.
- `estimated_cost`: 2 decimals.
- `estimated_revenue`: 2 decimals.
- `margin_percent`: 2 decimals.

The response must include both raw estimate and execution quantity when rounding changes operational meaning.

Example:

```json
{
  "plants_needed_estimate": 123.2,
  "plants_needed_execution": 124
}
```

## Season & Climate Assumptions

Planning must include `assumptions` in the response.

Required assumption fields:

- `formula_version`
- `loss_model`: `flat_sum_v1` for current MVP
- `season`
- `climate_zone`
- `season_factor`
- `climate_factor`
- `unit_conversion_profile_id`
- `price_source`
- `rounding_profile`

MVP default behavior:

- If `season` is missing, use `unspecified` and warning `season_not_specified`.
- If `climate_zone` is missing, use farm climate zone if available.
- If neither request nor farm has climate zone, use `unspecified` and warning `climate_zone_not_specified`.
- If crop variety has `suitable_season` or `suitable_climate_zone`, response should warn when selected context does not match.

Future formula:

```text
effective_yield_per_plant =
  avg_yield_per_plant
  * season_factor
  * climate_factor
```

MVP may keep factors at `1.0` while exposing assumptions and warnings.

## Demand Fulfillment Status

Planning response must not only return numbers. It must also say whether the plan appears feasible.

Required response object:

```json
{
  "fulfillment": {
    "status": "unknown|feasible|warning|shortage",
    "shortages": [],
    "warnings": []
  }
}
```

Shortage types:

- `missing_norm`
- `missing_unit_conversion`
- `insufficient_area`
- `insufficient_labor`
- `delivery_date_risk`
- `season_mismatch`
- `climate_mismatch`
- `low_margin`
- `negative_margin`

MVP-0 can return `unknown` for area/labor if farm capacity data is incomplete, but it must state why.

## Missing Norm Errors

Domain errors must identify the missing field or norm family.

Error shape:

```json
{
  "error": "planning_error",
  "message": "Missing loss profile for crop 'Dưa leo'. Configure harvest, processing, packing, grade, and reject loss percentages.",
  "field": "loss_profile",
  "missing": ["harvest_loss_percent", "processing_loss_percent", "packing_loss_percent"]
}
```

Required missing norm checks:

- loss profile
- harvest model
- labor norm
- unit conversion profile when unit conversion is needed
- price source, warning only unless margin is mandatory
- farm capacity, warning/unknown unless allocation is being created

## Output Contract

Recommended final response shape:

```json
{
  "input": {
    "quantity": 10,
    "unit": "kg",
    "frequency": "daily",
    "crop_id": 1,
    "variety_id": 2,
    "farm_id": 1,
    "target_date": "2026-06-20",
    "customer_channel": "supermarket"
  },
  "output": {
    "delivery_quantity": 10,
    "raw_harvest_quantity": 13.7,
    "plants_needed_estimate": 91.33,
    "plants_needed_execution": 92,
    "plants_to_plant_estimate": 101.48,
    "plants_to_plant_execution": 102,
    "area_m2": 25.5,
    "labor_hours": 8.93,
    "worker_days": 1.12,
    "estimated_cost": 120000,
    "estimated_revenue": 200000,
    "margin": 80000,
    "margin_percent": 40,
    "days_to_first_harvest": 35,
    "estimated_planting_date": "2026-05-16",
    "estimated_first_harvest_date": "2026-06-20"
  },
  "fulfillment": {
    "status": "warning",
    "shortages": [],
    "warnings": ["season_not_specified"]
  },
  "assumptions": {
    "formula_version": "planning_v1_flat_loss",
    "loss_model": "flat_sum_v1",
    "season": "unspecified",
    "climate_zone": "tropical",
    "season_factor": 1,
    "climate_factor": 1,
    "price_source": "crop.sale_price_per_unit",
    "rounding_profile": "mvp_v1"
  },
  "norms_used": {
    "loss_profile_id": 1,
    "harvest_model_id": 1,
    "labor_norm_id": 1
  },
  "safety_warnings": []
}
```

## Production Plan Snapshot Requirements

When storing a production plan, persist enough context so later changes to norms do not rewrite history.

Recommended snapshot fields:

- `formula_version`
- `planning_input_snapshot`
- `planning_output_snapshot`
- `norms_used_snapshot`
- `assumptions_snapshot`
- `price_snapshot`
- `customer_contract_snapshot`
- `fulfillment_snapshot`

If the current schema cannot store these yet, add them before production use or store in a JSON `notes`/snapshot field temporarily with a migration follow-up.

## Business Rules Before Downstream Tasks

Task 5 and later must not assume a production plan is operationally ready unless:

- required norms exist
- missing norm errors are resolved
- unit conversion is known
- rounding execution values are present
- season/climate assumptions are explicit
- fulfillment status is not `shortage`, or shortage has explicit approval
- selected farm is known before allocation

## Test Requirements

### Unit Tests

- calculates raw harvest quantity from delivery quantity and total losses
- rejects total loss >= 100
- rejects missing loss profile
- rejects missing harvest model
- rejects missing labor norm
- rounds plant execution quantities up
- includes formula assumptions
- includes fulfillment status

### Feature/API Tests

- `POST /api/v1/planning/calculate` returns core outputs for complete norms
- missing norms return 422 with `planning_error`
- unsupported unit conversion returns 422 with field-level message
- daily/monthly/seasonal frequency is accepted and returned
- farm climate mismatch returns warning, not 500
- low/negative margin returns warning

### Regression Tests

Every production bug in planning must add a regression test before the fix is accepted.

## Known Gaps From Current Implementation

The current implementation is a good MVP-0 foundation, but these gaps should be closed before opening broad downstream work:

- No explicit `assumptions` object yet.
- No `fulfillment` object yet.
- Season/climate factors are not applied.
- Unit conversion is accepted as string but not strictly canonicalized by conversion profile.
- Plants are rounded to 2 decimals instead of execution-safe whole numbers.
- `estimated_workers` currently means worker-days, not headcount; rename or clarify.
- Stored `production_plans` do not yet snapshot formula/norm assumptions.
- Customer/channel/tolerance/price snapshot are partial and need contract-aware planning.

## Acceptance Gate

Task 4 planning spec is considered complete when:

- This spec exists in `.sisyphus/evidence/task-04-planning-formula-spec.md`.
- Code has tests for happy path and missing norms.
- Next implementation iteration adds/validates assumptions, fulfillment status, rounding profile, and unit conversion behavior.
- Task 5 does not begin broad lifecycle/harvest work until the Task 4 blocker items are green or explicitly accepted by CTO/PM.
