# Task 8 Evidence: Pre-harvest Inspection + Approval Gate

## Files Changed

| File | Action |
|------|--------|
| `database/migrations/2026_05_13_000005_create_pre_harvest_inspections_table.php` | Created inspection schema |
| `app/Models/PreHarvestInspection.php` | Created model and relationships |
| `app/Services/PreHarvestInspectionService.php` | Created inspection approval workflow |
| `app/Services/HarvestEligibilityService.php` | Created harvest eligibility gate |
| `app/Http/Controllers/Api/V1/PreHarvestInspectionController.php` | Created farm-scoped inspection API |
| `routes/api.php` | Added inspection routes |
| `tests/Feature/PreHarvestInspectionApiTest.php` | Created Task 8 coverage |

## Route Contract

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/pre-harvest-inspections` | List farm-scoped inspections |
| POST | `/api/v1/pre-harvest-inspections` | Submit inspection checklist |
| GET | `/api/v1/pre-harvest-inspections/{id}` | View inspection |
| POST | `/api/v1/pre-harvest-inspections/{id}/approve` | Approve submitted inspection |
| POST | `/api/v1/pre-harvest-inspections/{id}/reject` | Reject submitted inspection |

## Behavior

- Non-admin users are scoped to their own farm.
- Inspection checklist is stored as JSON with `key`, `label`, `passed`, and optional `note`.
- If any criterion fails, the inspection is `rejected`.
- If all criteria pass and the actor can approve, the inspection is immediately `approved`.
- If all criteria pass but actor cannot approve, the inspection remains `submitted` until an approver approves it.
- `HarvestEligibilityService::assertCanHarvest()` requires the latest inspection for the batch to be approved.
- `HarvestEligibilityService` also calls `IsolationGuardService`, so harvest eligibility checks inspection and active chemical isolation together.

## Tests Run

```bash
rtk php artisan test tests/Feature/PreHarvestInspectionApiTest.php
```

Result: `7 tests, 21 assertions`.

```bash
rtk php artisan test
```

Result: `205 tests, 631 assertions`.

## Remaining Risks

- Harvest module is not implemented yet, so this gate must be wired into T9 harvest lot creation.
- No dedicated audit event table yet; approval timestamps and approver fields are captured on the inspection record.
- Override harvest eligibility is not implemented; defer until governance is explicit.
