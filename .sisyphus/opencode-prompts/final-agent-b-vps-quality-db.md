You are Final Agent B running on VPS for the Final Verification Wave.

Work in /Users/macbook/Herd/ariops. Follow AGENTS instructions: every shell command must be prefixed with `rtk`.

Mode: review/evidence only. Do not edit application code. Do not update the plan.

Ownership:
- `.sisyphus/evidence/final-f2-quality.log`
- `.sisyphus/evidence/final-f2b-data-quality.log`
- `.sisyphus/evidence/final-f2c-db-architecture.md`

Scope:
- F2 Code Quality Review:
  - Run full test suite.
  - Run PHP syntax checks on changed/new PHP files if static analyzer is unavailable.
  - Record blocker/major/minor findings.
- F2b Data Quality + Migration QC:
  - Run `php artisan migrate:fresh --force`.
  - Run canonical seed if available.
  - Run integrity checks for orphan core records, enum/status anomalies, and quantity/unit nulls using SQLite-compatible SQL or tinker.
  - Run negative seed only if it is safe and already supported; otherwise state why skipped.
- F2c Database Architecture Review:
  - Review schema, critical FK/index/check/unique constraints, and nullable columns against MVP needs.
  - Run lightweight query plan or schema introspection where practical.
  - Identify missing critical FK/index/check constraints as findings with severity.

Verification:
- Evidence must include command outputs and a clear PASS/WARN/FAIL summary.
