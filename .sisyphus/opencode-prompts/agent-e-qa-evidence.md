# Agent E QA/Evidence Prompt

You are Agent E QA/Evidence for AgriOps.

Work in `/Users/macbook/Herd/ariops`.

## Mission

Make release readiness auditable by building a traceability and evidence framework for MVP-0 and MVP-1.

## Sources To Read

- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`
- `.docs/AgriOps — Hệ thống quản lý sản xuất, cung ứng và truy xuất nông nghiệp.md`
- `.docs/yeu_cau_crm_nong_nghiep_v1.md` when the plan references expanded BRD sections
- Existing `.sisyphus/evidence/*`
- `tests/Feature/*`

## Owns

- `.sisyphus/evidence/final-f1b-qa-traceability.md`
- `.sisyphus/evidence/evidence-index.md`
- `.sisyphus/evidence/release-risk-log.md`

## Must Not Touch

- Production application code
- Migrations
- Existing tests unless explicitly needed for read-only analysis
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`
- `.sisyphus/agent-board.md`

## Required Output

Create or update the owned evidence files with:

1. BRD requirement -> plan task -> test/evidence -> release gate matrix.
2. MVP-0 smoke checklist.
3. MVP-1 demand-to-QR smoke checklist.
4. Evidence index covering all current `.sisyphus/evidence/*` artifacts.
5. Known issue and release risk log with severity, owner, mitigation, and gate.
6. Missing test/evidence gaps that must be closed before downstream development.
7. Suggested QA commands for local/Herd/PostgreSQL verification.

## Evidence Standard

End your final message with:

- Summary
- Files changed
- Tests run, if any
- Evidence paths
- Open risks
- Next recommended step

