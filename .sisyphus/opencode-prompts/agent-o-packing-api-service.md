# Agent O - Packing API and Source Guards

You are Agent O for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Implement Task 10 authenticated packing lot API and domain service for selecting multiple harvest sources with availability guards.

## Dependency Guard

This task depends on Agent N's schema/model foundation.

Before editing, verify these files exist:

- `app/Models/PackingLot.php`
- `app/Models/PackingLotSource.php`
- `database/migrations/*create_packing_lots_table.php`
- `database/migrations/*create_packing_lot_sources_table.php`

If they do not exist, do not create fallback schema. Instead write `.sisyphus/evidence/task-10-packing-api-blocked.md` explaining the blocker and stop.

## Ownership

You own:

- `app/Services/PackingLotService.php`
- `app/Http/Controllers/Api/V1/PackingLotController.php`
- minimal `routes/api.php` additions for packing lot routes
- `tests/Feature/PackingLotApiTest.php`
- `.sisyphus/evidence/task-10-packing-api.md`
- `.sisyphus/evidence/task-10-packing-mix.log`
- `.sisyphus/evidence/task-10-packing-source-invalid.log`

Do not touch:

- migrations except if Agent N already created them and a tiny compatibility fix is absolutely required
- Filament resources
- seeders
- public QR routes/controllers
- traceability graph service owned by Agent P

## Required API

Authenticated and farm-scoped under `/api/v1`:

- `GET /packing-lots`
- `POST /packing-lots`
- `GET /packing-lots/{id}`

Suggested POST payload:

```json
{
  "farm_id": 1,
  "packed_at": "2026-05-15 10:00:00",
  "total_output_quantity": 95,
  "unit": "kg",
  "notes": "Mixed lot",
  "sources": [
    {"harvest_lot_id": 1, "quantity": 60},
    {"harvest_lot_id": 2, "quantity": 40}
  ]
}
```

## Business Rules

- Must reject empty sources.
- Must reject a source harvest lot unless `status=available`.
- Must reject duplicate harvest sources in one request.
- Must reject source quantity <= 0.
- Must reject source quantity greater than the harvest lot raw quantity for MVP.
- Non-admin users can only use harvest lots from their own farm.
- Admin users may mix sources across farms.
- Response must include `sources` with source farm and harvest lot details so UI can display bulk selection result.
- After successful packing, mark source harvest lots as `packed`.
- Generate a deterministic unique `code` if not supplied. Use existing project code style where possible.
- Generate or persist a `qr_code` placeholder based on actual packing lot data, not plan data. This can be an internal token for Task 11 to expose publicly later.

## Tests

Create feature tests covering:

- admin can create packing lot mixed from two farms and GET response includes both source farms
- farm manager cannot pack another farm's harvest lot
- packed/unavailable harvest lot is rejected with HTTP 422 and error code/message
- duplicate source rows are rejected
- successful packing marks harvest lots as `packed`
- list/show responses include source availability fields useful for UI

Run:

```bash
rtk php artisan test tests/Feature/PackingLotApiTest.php
rtk php artisan test
```

## Evidence

Write:

- `.sisyphus/evidence/task-10-packing-api.md` with files changed, rules implemented, tests run/counts, risks
- `.sisyphus/evidence/task-10-packing-mix.log` with the relevant passing test or curl-style evidence summary
- `.sisyphus/evidence/task-10-packing-source-invalid.log` with the invalid source rejection evidence

