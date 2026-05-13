# AgriOps Hardening Agent C - UAT Evidence and Risk Log Cleanup

You are working in `/Users/macbook/Herd/ariops`.

This is primarily evidence/review with small script/doc updates only. Do not edit application code unless you find a direct, tested bug and note it clearly.

## Goal

Make the UAT readiness/risk documents reflect current truth after the final gate.

Known stale areas:

- `release-risk-log.md` still marks QR privacy blocker and packing FK risk open despite later evidence/tests.
- `task-18-uat-readiness.md` still says not ready for full UAT sign-off in places.
- Some old evidence says pending migration or old test counts.

## Ownership

You may edit:

- `.sisyphus/evidence/release-risk-log.md`
- `.sisyphus/evidence/task-18-uat-readiness.md`
- `.sisyphus/evidence/evidence-index.md`
- create `.sisyphus/evidence/hardening-c-uat-risk-cleanup.md`
- optionally add/update a smoke script under `.sisyphus/run-continuation/`

Do not edit app code.

## Required Work

1. Re-check current evidence and tests around:
   - QR public privacy whitelist
   - packing lot FK/source constraints
   - mobile/API work log/photo smoke evidence
   - migration status and current test count
2. Update stale risk statuses with precise evidence links.
3. Keep unresolved items honest; do not mark device-level manual smoke done unless you actually create a reproducible substitute or evidence note.
4. Run `rtk php artisan test` if feasible and capture current count.

## Evidence

Write `.sisyphus/evidence/hardening-c-uat-risk-cleanup.md` with:

- documents updated
- risks resolved vs accepted vs still open
- commands run and results

