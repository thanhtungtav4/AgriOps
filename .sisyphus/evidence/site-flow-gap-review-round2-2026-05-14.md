# Site Flow Gap Review Round 2 - 2026-05-14

## Context

After adding the first post-planning UI flow, the visible `/operations` app is better but still does not cover the full BRD v1 operating flow. Backend APIs, Filament resources, and tests cover many modules; the main remaining issue is end-to-end user flow exposure in React operations screens.

## Current Visible Operations Flow

Implemented / wired:

- `/operations` dashboard: alerts, batches, tasks summary only.
- `/operations/planning`: demand quantity calculator.
- `/operations/plans`: production plan list.
- `/operations/batches/create?plan_id=...`: create planting batch from plan.
- `/operations/batches`: planting batch list.
- `/operations/batches/:id`: batch detail, lifecycle transition, generate tasks, related task list.
- `/operations/fulfillment`: tabbed basic create/list for pre-harvest inspections, harvest lots, packing lots, deliveries, returns.

Still weak:

- Dashboard still has its own nested header/logout inside the new layout and duplicates navigation patterns.
- `/operations/tasks` still points to `OperationsDashboard`, not a real task execution page.
- Fulfillment screen is a raw tabbed CRUD surface; it is not yet a guided flow from eligible batch to inspection to harvest to packing to QR to delivery.
- Supply contracts/demands are not surfaced in React, so planning starts from manual entry only.
- Safety, finance, reports, post-season improvement, traceability public page entry, and role/user management are not surfaced in the React operations app.

## BRD Flow Coverage

| Flow | Backend/API | Filament/Admin | React Operations | Status |
|---|---:|---:|---:|---|
| Farm / plot / bed master data | Yes | Yes | No | Admin only, no ops context |
| Crop / variety / standards / stages | Yes | Yes | Partial via selects | No catalog ops screen |
| Supply contracts | Yes | Yes | No | Missing P0/P1 flow |
| Supply demands | Yes | Yes | No | Missing demand-to-plan flow |
| Planning calculator | Yes | Yes | Yes | Works, now can save plan |
| Production plans | Yes index/store | Yes | Partial list only | No detail/approve/status actions |
| Planting batch creation | Yes | Yes | Partial | From plan works; manual/allocation missing |
| Land allocation | Yes service/tests | Filament likely partial | No | Missing in batch detail |
| Batch lifecycle | Yes | Yes | Partial | Transitions available; timeline/history missing |
| Work task generation | Yes | Yes | Partial | Generate button exists |
| Work task execution | Yes | Yes | No | Missing task detail, assign, status, log, photo |
| Farming logs | Yes via task logs | No/limited | No | Missing web review and evidence flow |
| Incidents | Yes | Yes | No | Missing safety surface |
| Chemical usage | Yes | Yes | No | Missing safety/PHI flow |
| Chemical product stock | Yes | Yes | No | Missing inventory/safety surface |
| Pre-harvest inspection | Yes | Yes | Basic in fulfillment | Needs batch-driven approve/reject flow |
| Harvest lots | Yes | Yes | Basic in fulfillment | Needs eligible batch source picker |
| Processing/sơ chế | Model/service exists, route unclear | No/limited | No | Missing UI and possibly API exposure |
| Packing lots / QR | Yes | Yes | Basic in fulfillment | Needs QR publish/print/open trace action |
| Public QR traceability | Yes API | N/A | No public web page verified in React | Missing user-facing public page or link |
| Delivery notes | Yes | Yes | Basic in fulfillment | Needs delivery acceptance/proof flow |
| Returns | Yes | Yes | Basic in fulfillment | Needs link from delivery detail |
| Cost records/breakdowns | Yes | Yes | No | Missing finance screens |
| Price tables / margin dashboard | Yes | likely partial | No | Missing finance screens |
| Alerts | Yes | No/limited | Partial list only | Missing mark-read/filter/source drilldown |
| Post-season reviews | Yes | Yes | No | Missing improvement loop UI |
| Suggested norm updates approval | Partial via post-season review | No visible flow | No | Critical MVP gap |
| Reports | Partial services/APIs | No unified report UI | No | Missing production/yield/loss/cost/quality |
| User/role/farm assignment | Partial auth/model | Filament default unclear | No | Missing explicit admin UX |

## Highest Priority Missing Flows

### P0. Work Task Execution Flow

Why: BRD requires workers/managers to execute and record actual work, including notes, time, photo evidence, and status updates. Current `/operations/tasks` is only a dashboard table.

Needed screens:

- `/operations/tasks`: actionable list with filters by status, assignee, due date, batch.
- `/operations/tasks/:id`: task detail with batch context.
- Actions:
  - assign worker (`PATCH /work-tasks/{id}/status` with `assigned_user_id`)
  - start / complete / cancel
  - submit farming log (`POST /work-tasks/{id}/logs`)
  - support `photo_paths` and/or upload when feasible.

### P0. Supply Demand to Plan Flow

Why: BRD starts planning from contracts/demands, not only manual calculator input.

Needed screens:

- `/operations/supply/contracts`
- `/operations/supply/demands`
- create demand from contract or one-off supermarket need.
- action: "Tính kế hoạch" opens planning prefilled by demand/contract.
- action: save production plan linked to `supply_contract_id` / `supply_demand_id` if backend supports fields.

### P0. Batch Detail Completion

Why: Batch is the production backbone. Current detail lacks land allocation and connected subflows.

Needed:

- Allocation panel: plot/bed/area/plants.
- Linked tabs: tasks, logs, incidents, chemical usages, inspections, harvest lots, packing lots.
- Clear next-step CTA by status:
  - planned -> approve
  - approved -> allocate/generate tasks
  - harvesting -> create inspection/harvest
  - completed -> create post-season review.

### P1. Safety Flow

Why: Chemical/biological usage and isolation guard are critical for harvest safety.

Needed screens:

- `/operations/safety/incidents`
- `/operations/safety/chemical-usages`
- `/operations/safety/products`
- show isolation end dates and block/flag harvest risk.
- connect incident -> chemical usage -> pre-harvest eligibility.

### P1. Fulfillment Guided Flow

Why: Existing `/operations/fulfillment` is CRUD-like and ID-entry heavy.

Needed:

- Replace manual ID entry with selects/searches from eligible batches/lots.
- Split or tab by real process:
  - inspections
  - harvest
  - packing / QR
  - delivery
  - returns
- Packing detail must expose QR code, public traceability URL, print/copy action.

### P1. Finance / Margin Flow

Why: BRD requires cost, price, revenue, margin and after-season efficiency.

Needed screens:

- `/operations/finance/prices`
- `/operations/finance/costs`
- `/operations/finance/margins`
- use:
  - `/price-tables`
  - `/cost-records`
  - `/cost-breakdowns/dashboard`
  - `/margin-dashboard`

### P1. Post-season Improvement Flow

Why: MVP goal says actual data after each season must suggest improvements and require manager approval before updating norms.

Needed screens:

- `/operations/reviews`
- create review from completed production plan/batch.
- submit/approve/reject.
- show suggested changes to yield/loss/labor/water/cost norms.

### P2. Reports

Needed:

- Production report.
- Yield report.
- Loss report.
- Cost report.
- Quality / returns report.
- Post-season improvement report.

## Route Issues to Fix

- `/operations/tasks` currently renders `OperationsDashboard`; it must become a task list page.
- Dashboard component should be simplified to fit inside `OperationsLayout`, not render its own full page shell/header/logout.
- Navigation should become grouped:
  - Overview
  - Supply
  - Planning
  - Production
  - Tasks
  - Safety
  - Fulfillment
  - Finance
  - Reviews
  - Reports

## Suggested Agent Split

1. Agent UI4 - Task Execution
   - Own `resources/js/pages/operations/tasks/*`
   - Add routes for task list/detail/log.
   - Verify with build.

2. Agent UI5 - Supply Demand to Plan
   - Own `resources/js/pages/operations/supply/*`
   - Add contracts/demands list/create and planning prefill.
   - Avoid backend schema changes unless contract fields are missing.

3. Agent UI6 - Safety Workflow
   - Own `resources/js/pages/operations/safety/*`
   - Add incident, chemical usage, chemical product low-stock views.

4. Agent UI7 - Fulfillment Hardening
   - Own `resources/js/pages/operations/fulfillment/*`
   - Convert raw ID forms into guided selectors and QR actions.

5. Agent UI8 - Finance and Review Loop
   - Own `resources/js/pages/operations/finance/*` and `resources/js/pages/operations/reviews/*`
   - Add margin/cost dashboards and post-season review actions.

6. Agent QA3 - BRD Flow Smoke
   - Own evidence only.
   - Confirm routes build, API contracts match, and summarize remaining gaps.

## Verification Baseline

Before this review:

- `rtk npm run build` passed.
- Focused post-planning API tests passed: 102 tests, 374 assertions.
- Full backend suite passed: 369 tests, 1943 assertions.

