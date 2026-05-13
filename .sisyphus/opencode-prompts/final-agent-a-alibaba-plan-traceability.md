You are Final Agent A running on Alibaba/Qwen for the Final Verification Wave.

Work in /Users/macbook/Herd/ariops. Follow AGENTS instructions: every shell command must be prefixed with `rtk`.

Mode: review/evidence only. Do not edit application code. Do not update the plan.

Ownership:
- `.sisyphus/evidence/final-f1-plan-compliance.md`
- `.sisyphus/evidence/final-f1b-qa-traceability.md`

Scope:
- F1 Plan Compliance Audit:
  - Compare tasks 1-18 in `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md` against code paths and evidence artifacts.
  - Verify evidence paths exist for task 1-18 where expected.
  - Produce pass/fail table with concrete file references and blockers.
- F1b QA Traceability Matrix:
  - Map major BRD/MVP requirements to task number, tests, and evidence.
  - Mark missing test/evidence as blocker only when it belongs to MVP-0/MVP-1.
  - Identify orphan evidence or orphan critical requirements if any.

Verification:
- Run read-only checks with `rtk rg`, `rtk test -e` via shell conditionals, and targeted `rtk php artisan test --filter=...` only if needed.
- Keep outputs concise but evidence-grounded.
