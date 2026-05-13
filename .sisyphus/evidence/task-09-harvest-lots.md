# Task 9 Evidence: Harvest Lots + Grade Breakdown

## Files Changed

| File | Action |
|------|--------|
| `database/migrations/2026_05_13_000006_create_harvest_lots_table.php` | Created harvest lot schema |
| `app/Models/HarvestLot.php` | Created model and relationships |
| `app/Services/HarvestLotService.php` | Created harvest creation domain service |
| `app/Http/Controllers/Api/V1/HarvestLotController.php` | Created farm-scoped harvest lot API |
| `routes/api.php` | Added harvest lot routes |
| `tests/Feature/HarvestLotApiTest.php` | Created Task 9 coverage |

## Route Contract

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/harvest-lots` | List farm-scoped harvest lots |
| POST | `/api/v1/harvest-lots` | Create harvest lot with grade breakdown |
| GET | `/api/v1/harvest-lots/{id}` | View harvest lot |

## Behavior

- Non-admin users are scoped to their own farm.
- Harvest creation calls `HarvestEligibilityService`, so it requires the latest pre-harvest inspection to be approved and no active chemical isolation window.
- Captures raw quantity and grade A/B/C/reject quantities.
- Rejects grade totals greater than `raw_quantity`.
- Rejects zero/null raw quantity through validation.
- Captures reject reasons as JSON.
- Updates planting batch `status=harvesting`, `actual_harvest_date`, and cumulative `actual_quantity`.

## Tests Run

```bash
rtk php artisan test tests/Feature/HarvestLotApiTest.php
```

Result: `7 tests, 20 assertions`.

```bash
rtk php artisan test
```

Result: `212 tests, 651 assertions`.

## Remaining Risks

- Packing source availability is not implemented yet; Task 10 must consume `harvest_lots.status`.
- Multi-harvest planning reconciliation/reporting is only partially represented by cumulative `actual_quantity`.
- Product standard/reject reason taxonomy is still loose JSON and should be normalized when quality module expands.
