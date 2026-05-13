# Task 7 Evidence: Incident + Chemical/Biological Usage + Isolation Guard

## Files Changed

| File | Action |
|------|--------|
| `database/migrations/2026_05_13_000003_create_incidents_table.php` | Created incident trace schema |
| `database/migrations/2026_05_13_000004_create_chemical_usages_table.php` | Created chemical/biological usage schema |
| `app/Models/Incident.php` | Created model and relationships |
| `app/Models/ChemicalUsage.php` | Created model and relationships |
| `app/Http/Controllers/Api/V1/IncidentController.php` | Created farm-scoped incident API |
| `app/Http/Controllers/Api/V1/ChemicalUsageController.php` | Created farm-scoped usage API |
| `app/Services/IsolationGuardService.php` | Created harvest isolation guard service |
| `routes/api.php` | Added incident and chemical usage routes |
| `tests/Feature/IncidentChemicalUsageApiTest.php` | Created Task 7 coverage |

## Route Contract

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/incidents` | List farm-scoped incidents |
| POST | `/api/v1/incidents` | Create pest/disease/weather/soil incident |
| GET | `/api/v1/incidents/{id}` | View incident with usages/context |
| GET | `/api/v1/chemical-usages` | List farm-scoped chemical/biological usages |
| POST | `/api/v1/chemical-usages` | Create usage and isolation snapshot |
| GET | `/api/v1/chemical-usages/{id}` | View usage with incident/context |

## Behavior

- Non-admin users are scoped to their own farm.
- Incident creation can link batch, plot, bed, task, and farming log context.
- Chemical/biological usage can link to an incident or directly to a planting batch.
- Usage inherits batch/plot/bed context from the incident when present.
- Usage records product, active ingredient, dosage, quantity, cost snapshot, applied user, and isolation days.
- `isolation_ends_at` is calculated from `applied_at + isolation_days`.
- `IsolationGuardService::assertCanHarvest()` throws when a harvest date is before an active isolation end date.

## Tests Run

```bash
rtk php artisan test tests/Feature/IncidentChemicalUsageApiTest.php
```

Result: `7 tests, 22 assertions`.

```bash
rtk php artisan test
```

Result: `198 tests, 610 assertions`.

## Remaining Risks

- Harvest module is not implemented yet, so isolation guard is tested at service level and must be wired into T9 harvest creation.
- No approval workflow for chemical usage yet.
- QR public privacy must later whitelist this data and avoid exposing product names, dosage, internal users, and cost.
