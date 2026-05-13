# Agent P - Traceability Graph Backend

You are Agent P for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Implement Task 10 backend traceability graph service that reads actual harvest, processing, and packing data. This prepares Task 11 public QR without exposing sensitive internal data.

## Dependency Guard

This task depends on Agent N's packing schema/model foundation.

Before editing, verify these files exist:

- `app/Models/PackingLot.php`
- `app/Models/PackingLotSource.php`
- `app/Models/ProcessingRecord.php`

If they do not exist, do not create fallback schema. Instead write `.sisyphus/evidence/task-10-traceability-graph-blocked.md` explaining the blocker and stop.

## Ownership

You own:

- `app/Services/TraceabilityGraphService.php`
- `tests/Feature/TraceabilityGraphServiceTest.php`
- `.sisyphus/evidence/task-10-traceability-graph.md`

Do not touch:

- routes/api.php
- API controllers
- public QR routes/controllers
- migrations except if Agent N already created them and a tiny compatibility fix is absolutely required
- packing API/service owned by Agent O
- Filament resources
- seeders

## Required Behavior

Create a service that can build a public-safe graph from a packing lot:

```php
$graph = app(TraceabilityGraphService::class)->forPackingLot($packingLot);
```

The graph must be based on actual records:

- packing lot
- packing lot sources
- harvest lots
- processing records linked to harvest lots if any
- planting batch/crop/farm summary where relationships exist

Do not build from production plan assumptions.

## Privacy Boundary

The graph must not expose sensitive internal fields intended to stay out of public QR:

- chemical product names
- dosage
- concentration
- cost
- applied_by_user_id
- internal user emails
- private metadata blobs

It may expose safe summaries:

- farm name/code
- crop name
- batch id/code
- harvest lot code/date/quantity/unit/status
- processing input/output/loss rate/unit/status
- packing code/date/quantity/unit/status/source count
- source farm name/code
- isolation/inspection safety status as boolean or short status, without chemical details

## Tests

Create feature tests covering:

- graph includes a packing lot with two source harvest lots from two farms
- graph includes processing records attached to source harvest lots
- graph does not expose production plan-only data as the source of traceability
- graph does not expose sensitive chemical usage fields when the batch has a chemical usage record
- missing optional relationships do not break graph generation

Run:

```bash
rtk php artisan test tests/Feature/TraceabilityGraphServiceTest.php
rtk php artisan test
```

## Evidence

Write `.sisyphus/evidence/task-10-traceability-graph.md` with:

- files changed
- graph shape summary
- privacy fields intentionally excluded
- tests run and counts
- remaining risks

