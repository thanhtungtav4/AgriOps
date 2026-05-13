# Agent I MVP-0 Hardening Prompt

You are Agent I MVP-0 Hardening for AgriOps.

Work in `/Users/macbook/Herd/ariops`.

## Mission

Close small MVP-0 hardening gaps that do not require broad feature work: login rate limiting evidence and Task 4 RED trace documentation.

## Sources To Read

- `.sisyphus/evidence/release-risk-log.md`
- `.sisyphus/evidence/task-04-tdd-trace.md`
- `.sisyphus/evidence/task-04-green.log`
- `.sisyphus/evidence/task-04-refactor.log`
- `routes/api.php`
- `tests/Feature/AuthTokenPolicyTest.php`
- `tests/Feature/PlanningApiTest.php`

## Owns

- `tests/Feature/AuthRateLimitTest.php`
- `.sisyphus/evidence/mvp0-hardening.md`
- `.sisyphus/evidence/task-04-tdd-trace.md`

## May Touch With Care

- `routes/api.php` only if needed to add Laravel throttle middleware to login.
- `tests/Feature/AuthTokenPolicyTest.php` only if rate limit expectations need alignment.

## Must Not Touch

- Planting batch code
- Allocation code
- Seeders
- Planning formula code
- Security farm-scope code beyond login route throttle
- `.sisyphus/agent-board.md`
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`

## Requirements

1. Add rate limiting to `/api/v1/auth/login` using existing Laravel middleware conventions.
2. Add focused test proving repeated invalid logins are throttled.
3. Update Task 4 TDD trace to explicitly list RED/GREEN/REFACTOR test names and evidence caveat if RED log is historical/not captured.
4. Write evidence summarizing remaining MVP-0 risks.

## Verification

Run:

```bash
rtk php artisan test tests/Feature/AuthRateLimitTest.php tests/Feature/AuthTokenPolicyTest.php
rtk php artisan test
```

Write evidence with files changed, commands, results, open risks, and next step.

