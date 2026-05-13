You are Agent T implementing the Task 18 UAT readiness and fail-path evidence.

Work in /Users/macbook/Herd/ariops. Follow AGENTS instructions: every shell command must be prefixed with `rtk`.

Read before editing:
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md` Task 18.
- Existing API tests/controllers for pre-harvest inspection, incident/chemical isolation, delivery, returns, alerts, traceability.
- `.sisyphus/evidence/release-risk-log.md` if useful.

Ownership:
- You may create or edit only:
  - `.sisyphus/evidence/task-18-uat-readiness.md`
  - `.sisyphus/run-continuation/task-18-fail-paths.sh`
  - `.sisyphus/evidence/task-18-fail-paths.log`
- Do not edit application code unless absolutely required and explicitly justified in your final summary.
- Do not update the plan file; Codex integrator will update it after review.

Goal:
- Produce a concise role-based UAT readiness pack for full v1.
- Build or run fail-path evidence for:
  - inspection fail blocks harvest,
  - isolation/chemical fail blocks harvest where currently implemented,
  - delivery → return path updates return evidence where currently implemented.
- If a fail path is already covered by existing tests, run those focused tests and cite exact test names in the evidence.

Verification:
- Capture command outputs to `.sisyphus/evidence/task-18-fail-paths.log`.
- In `.sisyphus/evidence/task-18-uat-readiness.md`, include covered roles, scripts/tests run, evidence paths, and known gaps without overstating readiness.
