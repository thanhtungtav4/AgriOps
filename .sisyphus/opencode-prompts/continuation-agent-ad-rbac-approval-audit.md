# Continuation Agent AD - RBAC Approval Audit

You are not alone in this repository. Other agents may be working in parallel. Do not revert or overwrite unrelated changes. Do not commit or push.

Work in `/Users/macbook/Herd/ariops`. Prefix shell commands with `rtk`.

## Goal

Reduce CRIT-001/G4 ambiguity by auditing current approval-sensitive API endpoints and adding focused tests only if a real authorization gap exists.

## Scope You Own

- RBAC/security tests under `tests/Feature/`
- Evidence file `.sisyphus/evidence/rbac-approval-audit-continuation.md`
- API controllers only if a test proves a real approval/RBAC gap

## Must Not Touch

- Mobile app
- React UI
- Database migrations
- Release risk log or agent board

## Required Workflow

1. Read `User` roles/capabilities, `rbac-matrix-v1.md`, `routes/api.php`, and controllers with approval-sensitive actions:
   - `PreHarvestInspectionController`
   - `PlantingBatchController`
   - `AlertController`
   - any `approve`, `reject`, `transition`, or `trigger` actions
2. Run existing relevant tests.
3. Identify if farm manager/technician/worker can perform an action that should require manager/admin/approval permission.
4. If a real gap exists, add the smallest test and fix.
5. If no real gap or endpoint is intentionally deferred, write evidence explaining why CRIT-001 remains partial.
6. Run focused tests and write `.sisyphus/evidence/rbac-approval-audit-continuation.md`.

## Output

Final response must list changed files, tests run, and whether CRIT-001 should remain partial.
