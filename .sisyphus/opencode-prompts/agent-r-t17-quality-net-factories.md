You are Agent R implementing Task 17: TDD Quality Net + Test Data Factory.

Work in /Users/macbook/Herd/ariops. Follow AGENTS instructions: every shell command must be prefixed with `rtk`.

Read before editing:
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md` Task 17 and Verification Strategy.
- Existing models under `app/Models`.
- Existing feature tests under `tests/Feature`.
- Existing seeders under `database/seeders`.

Scope:
- Add focused Laravel model factories for the core AgriOps domain so tests can stop hand-building large object graphs.
- Prefer factories for stable primitives and relationships already present in tests: Farm, Crop, CropVariety, Plot, Bed, ProductionPlan, PlantingBatch, PlantingBatchAllocation, GrowthStage, WorkTask, FarmingLog, HarvestLot, ProcessingBatch, PackingLot, QRCode, Delivery, ReturnRecord, Alert, CostRecord, PriceTable.
- Keep factories deterministic enough for tests: sensible defaults, relationship factories, and named states for important statuses/roles.
- Add one small fixture/helper class only if it meaningfully reduces repeated setup in tests.
- Add a regression test that proves the factories can build a complete farm workflow fixture used by API smoke tests.
- Add a known-rule regression test that runs twice cleanly, ideally around a high-value rule already implemented: cancelled work task cannot accept logs, requires_photo task requires photo, or farm-scope isolation.
- Keep production code changes minimal. Do not refactor unrelated controllers/services.

TDD/evidence:
- First add a failing test and capture RED to `.sisyphus/evidence/task-17-red.log`.
- Then implement factories/helpers and capture GREEN to `.sisyphus/evidence/task-17-green.log`.
- Then run a broader regression/full suite and capture `.sisyphus/evidence/task-17-refactor.log`.
- Capture known-rule repeat evidence to `.sisyphus/evidence/task-17-known-rule.log`.

Plan update:
- Mark Task 17 `[x]` only after tests pass.
- Add Implementation status dated 2026-05-13 with concise files/evidence list.

Do not modify completed task sections except evidence references if needed.
