# AgriOps Hardening Agent D - Ops Runbook and Production Readiness Notes

You are working in `/Users/macbook/Herd/ariops`.

This is documentation plus lightweight config/test review. Do not edit app code unless required for a failing health check.

## Goal

Close the final F6 follow-ups as much as possible without deployment credentials:

- backup/restore runbook
- deployment env checklist
- request/logging observability note
- staging verification checklist

## Ownership

You may edit:

- `README.md`
- `.env.example` if adding non-secret documented env names is needed
- create/update `.sisyphus/evidence/hardening-d-ops-runbook.md`
- create `.sisyphus/run-continuation/ops-readiness-smoke.sh` if useful

Do not edit app services/controllers/models unless you find a small, tested issue.

## Required Work

1. Review current Laravel config and README.
2. Add concise ops section/runbook covering:
   - install/build/test commands
   - migration/seed path
   - storage link/uploads
   - queue/scheduler notes
   - PostgreSQL backup and restore commands/placeholders
   - health checks and rollback notes
3. Add or document a repeatable ops smoke command if useful.
4. Run lightweight verification commands if feasible.

## Evidence

Write `.sisyphus/evidence/hardening-d-ops-runbook.md` with:

- files changed
- checks run and results
- remaining production-only items

