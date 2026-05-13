# Task 18 E2E Chain Evidence

Date: 2026-05-13

This dry-run uses focused Laravel API tests as the reproducible chain artifact. A single curl chain is not yet stable because planting batch allocation has no public API endpoint; allocation is currently exercised through service-backed feature tests.

Covered workflow slices:

- Demand/planning to production plan: `PlanningApiTest`
- Planting batch lifecycle and allocation guards: `PlantingBatchApiTest`, `PlantingBatchAllocationGuardTest`
- Work task and farming log flow: `WorkTaskApiTest`, `WorkTaskLogApiTest`
- Inspection and harvest eligibility: `PreHarvestInspectionApiTest`, `HarvestLotApiTest`
- Packing, QR, traceability: `PackingLotApiTest`, `PublicTraceabilityApiTest`, `TraceabilityGraphServiceTest`
- Delivery, return, revenue: `DeliveryReturnApiTest`

Evidence:

- Script: `.sisyphus/run-continuation/task-18-e2e-chain.sh`
- Log: `.sisyphus/evidence/task-18-e2e-chain.log`
