# MVP-0 Hardening Evidence

**Date:** 2026-05-13
**Agent:** I - MVP-0 Hardening
**Project:** /Users/macbook/Herd/ariops
**Status:** COMPLETE

---

## Mission Scope

Close small MVP-0 hardening gaps:
1. Login rate limiting evidence
2. Task 4 RED trace documentation

---

## Files Changed

### 1. routes/api.php
**Change:** Added `throttle:5,1` middleware to `/api/v1/auth/login`

```php
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');
```

**Rationale:** Standard Laravel middleware - 5 attempts per 1 minute window. Configured on the login route only (not logout or other auth endpoints).

### 2. tests/Feature/AuthRateLimitTest.php (NEW FILE)
**Tests added:**
- `test_repeated_invalid_logins_are_throttled` - Verifies 429 after 5 failed attempts
- `test_valid_login_succeeds_before_rate_limit` - Ensures valid login not blocked
- `test_rate_limit_response_format` - Verifies 429 response format

### 3. .sisyphus/evidence/task-04-tdd-trace.md
**Change:** Updated RED phase documentation to:
- Explicitly list all RED test names
- Add evidence caveat noting RED log was historical (not separately captured)
- Added explicit test name table by TDD phase

---

## Commands Run

```bash
rtk php artisan test tests/Feature/AuthRateLimitTest.php tests/Feature/AuthTokenPolicyTest.php
# Result: passed, 11 tests, 59 assertions

rtk php artisan test
# Result: passed, 48 tests, 199 assertions
```

---

## Test Results

| File | Tests | Assertions | Status |
|------|-------|------------|--------|
| AuthRateLimitTest.php | 3 | 12 | ✅ PASS |
| AuthTokenPolicyTest.php | 8 | 47 | ✅ PASS |
| Full Suite | 48 | 199 | ✅ PASS |

---

## Risk Updates

### CRIT-002 - Planning TDD RED log incomplete
**Status:** ✅ RESOLVED
- Task 4 TDD trace now explicitly lists RED/GREEN/REFACTOR test names
- Evidence caveat added for historical RED log capture gap

### CRIT-004 - Token policy partially complete
**Status:** ✅ RESOLVED
- Login rate limiting implemented via Laravel `throttle:5,1` middleware
- AuthRateLimitTest.php proves throttling works correctly

---

## Remaining MVP-0 Risks

| ID | Risk | Severity | Status |
|----|------|----------|--------|
| CRIT-003 | PostgreSQL staging not verified | Critical | 🟡 PARTIAL (requires Owner action) |

**Note:** CRIT-003 requires staging PostgreSQL verification which Agent I cannot perform (requires access to staging environment).

---

## Open Risks Summary

| Gate | Criteria | Status |
|------|----------|--------|
| MVP-0 G4 | RBAC policies enforced | 🟡 Farm-scoped API enforced; approval policies deferred |

---

## Next Steps

1. **Owner:** Run `php artisan migrate:fresh --seed` on staging PostgreSQL and verify
2. **Integrator:** Review Agent I hardening outputs and merge to main branch
3. **MVP-0 Release:** Ready for PROCEED status pending staging verification

---

**Evidence Generated:** 2026-05-13
**Agent I Hardening:** COMPLETE
