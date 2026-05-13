# Continuation Agent Z - API Validation Contract Narrow Pass

You are not alone in this repository. Other agents may be working in parallel. Do not revert or overwrite unrelated changes. Do not commit or push.

Work in `/Users/macbook/Herd/ariops`. Prefix shell commands with `rtk`.

## Goal

Reduce MAJ-002 by auditing Laravel validation error shapes and adding a low-risk normalization path if appropriate.

## Scope You Own

- `app/Http/Responses/ApiResponse.php`
- API validation-related tests under `tests/Feature/`
- Evidence file `.sisyphus/evidence/api-validation-contract-continuation.md`

## Must Not Touch

- Mobile app files under `mobile/`
- React UI files under `resources/js/`
- Database migrations
- WorkTask generate success response shape
- Release risk log or agent board

## Required Workflow

1. Read `ApiResponse.php`, `bootstrap/app.php`, exception handling config, and existing feature tests that assert validation errors.
2. Determine whether Laravel validation failures can be safely normalized to the shared `error.code/message/details/trace_id` format without breaking too many established tests.
3. If safe, implement the smallest global or targeted handler and update/add tests.
4. If not safe, do not force it. Instead add a focused evidence file explaining why this remains partial.
5. Run focused tests and `rtk php artisan test` if feasible.
6. Write `.sisyphus/evidence/api-validation-contract-continuation.md` with audited files, decision, tests, and remaining risks.

## Output

Final response must list changed files, tests run, and whether MAJ-002 should remain partial.
