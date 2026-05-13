You are Final Agent E running on VPS for the Final Verification Wave.

Work in /Users/macbook/Herd/ariops. Follow AGENTS instructions: every shell command must be prefixed with `rtk`.

Mode: review/evidence only. Do not edit application code. Do not update the plan.

Ownership:
- `.sisyphus/evidence/final-f4b-business-architecture.md`
- `.sisyphus/evidence/final-f5-release-readiness.md`
- `.sisyphus/evidence/final-f6-ops-performance.md`

Scope:
- F4b Business Architecture Review:
  - Verify planning assumptions, shortage warnings, unit conversion/rounding.
  - Verify allocation guardrails, multi-harvest lot support, grade/reject taxonomy, override governance, material usage snapshot, customer/contract baseline, return feedback loop.
- F5 Release Readiness Gate:
  - Summarize blocker/critical/major open items from all evidence available so far.
  - Confirm QR privacy and required invariants have evidence.
  - Record release decision: ready, conditionally ready, or not ready.
- F6 Operations & Performance Gate:
  - Review `.env.example`, queue/scheduler/storage config, health checks, backup/restore notes.
  - Run lightweight performance smoke for planning calculate, work task list, and public QR/traceability where practical.
  - Check trace/request-id logging expectations.

Verification:
- Evidence should be concise, severity-ranked, and actionable.
