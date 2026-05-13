# Continuation Agent X - Evidence Index Cleanup

You are not alone in this repository. Other agents may be working in parallel. Do not revert or overwrite unrelated changes. Do not commit or push.

Work in `/Users/macbook/Herd/ariops`. Prefix shell commands with `rtk`.

## Goal

Clean stale Sisyphus evidence references after the latest hardening commits.

## Scope You Own

- `.sisyphus/evidence/evidence-index.md`
- New evidence file `.sisyphus/evidence/evidence-index-continuation.md`

## Must Not Touch

- Application code
- Tests
- Mobile or React files
- `.sisyphus/evidence/release-risk-log.md`
- `.sisyphus/agent-board.md`

## Required Workflow

1. Read `.sisyphus/evidence/evidence-index.md`, `.sisyphus/evidence/task-05-audit-hooks.md`, `.sisyphus/evidence/task-06-smoke-evidence.md`, `.sisyphus/evidence/task-07-smoke-evidence.md`, `.sisyphus/evidence/task-08-smoke-evidence.md`, `.sisyphus/evidence/task-09-taxonomy-smoke.md`, and `.sisyphus/evidence/release-risk-log.md`.
2. Update only stale entries in `evidence-index.md` that now have evidence:
   - T5 audit hooks
   - T6 manual/API smoke evidence
   - T7 manual/API smoke evidence
   - T8 manual/API smoke evidence
   - T9 reject reason taxonomy
3. Preserve genuine open risks such as staging PostgreSQL owner action and device-level mobile smoke if still open.
4. Write `.sisyphus/evidence/evidence-index-continuation.md` summarizing what changed and what remains open.
5. Run a lightweight validation command such as `rtk rg` for stale phrases you changed.

## Output

Final response must list changed files and any still-stale references found.
