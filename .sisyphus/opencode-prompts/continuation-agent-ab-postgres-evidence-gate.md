# Continuation Agent AB - PostgreSQL Evidence Gate

You are not alone in this repository. Other agents may be working in parallel. Do not revert or overwrite unrelated changes. Do not commit or push.

Work in `/Users/macbook/Herd/ariops`. Prefix shell commands with `rtk`.

## Goal

Reduce CRIT-003 confusion by separating local PostgreSQL evidence from staging PostgreSQL owner action.

## Scope You Own

- `.sisyphus/evidence/postgres-evidence-continuation.md`
- Optional `.sisyphus/run-continuation/postgres-evidence-check.sh`
- Existing PostgreSQL evidence docs only if correcting stale factual claims

## Must Not Touch

- Application code
- Database migrations
- Tests, unless only running them
- Release risk log or agent board

## Required Workflow

1. Read `.env`, `.env.testing`, `phpunit.xml`, database config, and existing PostgreSQL evidence docs.
2. Run safe local PostgreSQL checks if configured:
   - `rtk php artisan migrate:status`
   - `rtk php artisan schema:assert-no-pending-migrations` if command exists
   - focused seed/migration check if safe and non-destructive
3. Do not run destructive migrate:fresh unless in a clearly disposable testing DB and documented.
4. Create an evidence file that states exactly what is verified locally and what still requires staging credentials.
5. Create a reusable script only if it is safe by default and refuses destructive staging operations.

## Output

Final response must list commands run, local result, and why CRIT-003 should or should not remain partial.
