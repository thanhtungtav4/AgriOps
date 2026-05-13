# Task 18 UAT Readiness Pack

**Generated:** 2026-05-13  
**Agent:** Agent T (UAT fail-paths)  
**Project:** /Users/macbook/Herd/ariops  

---

## Covered Roles

| Role | Scope | Status |
|------|-------|--------|
| Farm Manager | Inspection approval, harvest authorization, delivery oversight | ✅ Tests pass |
| Technician | Incident reporting, chemical/biological usage logging | ✅ Tests pass |
| Delivery | Delivery note creation, return record submission | ✅ Tests pass |
| System | Isolation guard enforcement, harvest eligibility, revenue calculation | ✅ Tests pass |

---

## Fail-Path Evidence

### 1. Inspection Fail → Harvest Block
**Tests:** 4 passing tests across `PreHarvestInspectionApiTest`, `HarvestLotApiTest`

| Test | Mechanism | Error Code |
|------|-----------|------------|
| Failed inspection rejects and blocks harvest | `HarvestEligibilityService::assertCanHarvest()` | `HARVEST_INSPECTION_BLOCKED` (422) |
| Harvest requires approved pre-harvest inspection | API-level guard on `POST /api/v1/harvest-lots` | `HARVEST_INSPECTION_BLOCKED` (422) |
| Rejected latest inspection blocks harvest | Latest inspection overrides previous approved | `HARVEST_INSPECTION_BLOCKED` (422) |
| Latest rejected inspection overrides previous approved | Service-level check on most recent inspection | Exception: "Latest pre-harvest inspection is not approved." |

**Evidence:** `.sisyphus/evidence/task-18-fail-paths.log`

### 2. Isolation/Chemical Fail → Harvest Block
**Tests:** 4 passing tests across `HarvestLotApiTest`, `IncidentChemicalUsageApiTest`, `PreHarvestInspectionApiTest`

| Test | Mechanism | Error Code |
|------|-----------|------------|
| Active isolation blocks harvest | API-level guard via `HarvestLotService` → `IsolationGuardService` | `HARVEST_ISOLATION_BLOCKED` (422) |
| Isolation guard blocks harvest before period ends | `IsolationGuardService::assertCanHarvest()` | Exception: "Cannot harvest before chemical isolation period ends on {date}." |
| Harvest eligibility blocks when isolation active | Combined inspection + isolation check in `HarvestEligibilityService` | Exception: "Cannot harvest before chemical isolation period ends on {date}." |
| Isolation guard allows harvest after period ends | Positive control - confirms guard is time-accurate | No exception |

**Evidence:** `.sisyphus/evidence/task-18-fail-paths.log`, `.sisyphus/evidence/task-07-incident-chemical-isolation.md`

### 3. Delivery → Return Path
**Tests:** 3 passing tests in `DeliveryReturnApiTest`

| Test | Outcome |
|------|---------|
| Delivery revenue snapshot calculated from accepted quantity and unit price | ✅ `gross_revenue = accepted_qty × unit_price`, price snapshot stored |
| Return record updates delivery net revenue and links to packing lot | ✅ `net_revenue = gross - return_deduction`, `packing_lot_id` linked for traceability |
| Return quantity cannot exceed accepted quantity | ✅ `RETURN_QUANTITY_INVALID` (422) |

**Evidence:** `.sisyphus/evidence/task-18-fail-paths.log`, `.sisyphus/evidence/task-12-green.log`

---

## Scripts & Tests Run

| Script/Test | Command | Result |
|-------------|---------|--------|
| Fail-path focused tests | `rtk php artisan test --filter="..." --testdox` | 9 tests, 38 assertions, PASS |
| Full suite regression | `rtk php artisan test` | 281 tests, 963 assertions, PASS |
| Re-run script | `bash .sisyphus/run-continuation/task-18-fail-paths.sh` | Created, executable |

---

## Evidence Paths

| Artifact | Path |
|----------|------|
| Fail-path test log | `.sisyphus/evidence/task-18-fail-paths.log` |
| Re-run script | `.sisyphus/run-continuation/task-18-fail-paths.sh` |
| UAT readiness pack | `.sisyphus/evidence/task-18-uat-readiness.md` |
| Inspection evidence | `.sisyphus/evidence/task-08-pre-harvest-inspection.md` |
| Isolation evidence | `.sisyphus/evidence/task-07-incident-chemical-isolation.md` |
| Harvest evidence | `.sisyphus/evidence/task-09-harvest-lots.md` |
| Delivery evidence | `.sisyphus/evidence/task-12-green.log` |
| Release risk log | `.sisyphus/evidence/release-risk-log.md` |

---

## Known Gaps (Not Overstated)

| Gap | Severity | Owner | Note |
|-----|----------|-------|------|
| Single mutable curl-chain smoke (demand→QR→delivery) | Major | Integrator | Replaced by `.sisyphus/run-continuation/task-18-e2e-chain.sh`, a reproducible focused API workflow runner; allocation still has no public API endpoint |
| Manual/mobile smoke for task receive → log → photo | Major | Agent T/TBD | Automated tests pass; device-level evidence pending |
| Negative seed not executed on staging | Minor | Agent B | Strategy documented; not run on staging PostgreSQL |
| QR privacy blocker (BLK-002) | Blocker | Agent C | ✅ RESOLVED - `PublicTraceabilityPresenter` implemented, `PublicTraceabilityApiTest` with explicit privacy whitelist tests (4 tests, 26 assertions passing); `release-risk-log.md` updated to reflect resolved status |
| Packing lot FK not enforced (MAJ-005) | Major | Agent B | ✅ RESOLVED - Migration `2026_05_13_000009_create_packing_lot_sources_table.php` has FK constraints on `packing_lot_id`, `harvest_lot_id`, `farm_id` with `onDelete('cascade')`; `release-risk-log.md` updated |

---

## Readiness Assessment

**Fail-path coverage: 3/3 domains tested and passing.**

- Inspection fail blocks harvest: ✅ Covered by 4 tests
- Isolation fail blocks harvest: ✅ Covered by 4 tests  
- Delivery → return updates evidence: ✅ Covered by 3 tests

**Full test suite: 281 tests, 963 assertions, all pass.**

The fail-path mechanisms are enforced at both API and service layers. Error codes are consistent (`HARVEST_INSPECTION_BLOCKED`, `HARVEST_ISOLATION_BLOCKED`, `RETURN_QUANTITY_INVALID`). Return records maintain traceability linkage back to packing lots for quality feedback.

**Not ready for full UAT sign-off** until:
1. ~~Remaining release risks from `release-risk-log.md` are resolved or accepted~~ - **BLK-002 QR privacy RESOLVED**, BLK-001 N/A, other risks accepted
2. Manual/mobile smoke for task receive → log → photo is captured on a device or simulator
