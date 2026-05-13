# Continuation Agent W - API Error Contract Cleanup

You are not alone in this repository. Other agents may be working in parallel. Do not revert or overwrite unrelated changes. Do not commit or push.

Work in `/Users/macbook/Herd/ariops`. Prefix shell commands with `rtk`.

## Goal

Close or materially reduce `MAJ-002` from `.sisyphus/evidence/release-risk-log.md`: API error contract inconsistent.

## Scope You Own

- API controllers under `app/Http/Controllers/Api/V1/`
- API response/error tests under `tests/Feature/`
- Evidence file `.sisyphus/evidence/api-error-contract-continuation.md`

## Must Not Touch

- Mobile app files under `mobile/`
- React UI files under `resources/js/`
- Database migrations unless a test proves a schema issue
- Release risk log or agent board; the integrator will update those

## Required Workflow

1. Read `app/Http/Responses/ApiResponse.php`, `routes/api.php`, and existing controller tests.
2. Find endpoints that still return non-standard JSON errors for common auth/forbidden/not found/domain validation cases.
3. Implement small, conservative fixes using the existing `ApiResponse` trait pattern.
4. Add focused regression tests.
5. Run the relevant tests, then `rtk php artisan test` if feasible.
6. Write concise evidence to `.sisyphus/evidence/api-error-contract-continuation.md` with:
   - endpoints audited
   - files changed
   - tests run and exact results
   - remaining risks

## Output

Final response must list changed files and test results. Do not claim the risk is fully resolved unless all API V1 controllers have been audited or tested.
