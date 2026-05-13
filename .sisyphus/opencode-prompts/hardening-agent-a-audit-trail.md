# AgriOps Hardening Agent A - Lifecycle/Allocation Audit Trail

You are working in `/Users/macbook/Herd/ariops`.

Follow local repo conventions and do not revert changes by other agents. This is a coding task, not just review.

## Goal

Close the remaining T5 audit/event gap:

- Batch lifecycle transitions should leave a durable audit/domain-event record.
- Planting allocation create/update decisions should leave a durable audit/domain-event record.

## Ownership

You may edit:

- `database/migrations/*audit*`, `database/migrations/*domain_event*`
- `app/Models/*Audit*`, `app/Models/*DomainEvent*`
- `app/Services/PlantingBatchLifecycleService.php`
- `app/Services/PlantingBatchAllocationService.php`
- related tests under `tests/Feature/*Lifecycle*`, `tests/Feature/*Allocation*`, or new focused tests
- evidence file `.sisyphus/evidence/hardening-a-audit-trail.md`

Do not edit React/Expo files. Do not edit unrelated docs except your evidence file.

## Required Behavior

1. Add a small durable audit/domain-event table if none exists.
2. Record lifecycle transition events with: event type, actor/user if available, farm id, batch id, from state, to state, reason/metadata, timestamp.
3. Record allocation events with: event type, farm id, batch id, plot/bed ids, allocated area/plants, metadata, timestamp.
4. Add tests proving events are written on successful lifecycle transition and allocation creation.
5. Run focused tests, then full Laravel test suite if feasible.

## Evidence

Write `.sisyphus/evidence/hardening-a-audit-trail.md` with:

- files changed
- behavior implemented
- tests run and results
- any limitations

