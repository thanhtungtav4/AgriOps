# RBAC Approval Audit Continuation

**Generated:** 2026-05-13
**Agent:** AD / Integrator
**Project:** /Users/macbook/Herd/ariops
**Status:** COMPLETE

## Scope

Audited sensitive approval-style actions after the first AD Opencode run exited without producing a final evidence file.

## Findings

- `PreHarvestInspectionController::approve` and `reject` had farm-scope checks and service-level role checks, but non-approver users received a domain-style `422` instead of a controller RBAC `403`.
- `PlantingBatchController::transition` allowed any same-farm authenticated role to request transition to `approved`.

## Changes

- Added controller-level `canApprove()` checks before pre-harvest approve/reject actions.
- Added controller-level `canApprove()` check before planting batch transition to `approved`.
- Added regression tests proving worker roles cannot approve/reject pre-harvest inspections and cannot transition a planting batch to approved.

## Verification

```bash
rtk php artisan test tests/Feature/PreHarvestInspectionApiTest.php tests/Feature/PlantingBatchApiTest.php tests/Feature/AlertNotificationApiTest.php tests/Feature/DeliveryReturnApiTest.php tests/Feature/CostingPriceMarginApiTest.php
```

```json
{"tool":"phpunit","result":"passed","tests":50,"passed":50,"assertions":165,"duration_ms":981}
```

## Residual Risk

CRIT-001 is reduced for currently implemented approval endpoints. Future approval endpoints must still add explicit `canApprove()` or policy checks at the controller/action boundary.
