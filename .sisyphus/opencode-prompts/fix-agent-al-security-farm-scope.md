# Fix Agent AL - Farm Scope Security Fixes

You are Fix Agent AL for AgriOps. Work in `/Users/macbook/Herd/ariops`.

Use `rtk` before commands.

## Goal

Fix the critical farm-scope/ownership gaps found by review agents in the current uncommitted modules. Edit code and tests directly.

## Scope

You own these files only:

- `app/Http/Controllers/Api/V1/SoilHistoryController.php`
- `app/Http/Controllers/Api/V1/ChemicalProductController.php`
- `app/Http/Controllers/Api/V1/PostSeasonReviewController.php`
- `app/Http/Controllers/Api/V1/CostBreakdownController.php`
- `tests/Feature/SoilHistoryTest.php`
- `tests/Feature/ChemicalProductTest.php`
- `tests/Feature/PostSeasonReviewTest.php`
- `tests/Feature/CostBreakdownTest.php`

Do not touch docs, evidence index, routes, migrations, models, or unrelated tests unless absolutely required.

## Required Fixes

1. Soil history:
   - non-admin users must only list soil histories for their farm
   - non-admin users must not show/update records from another farm
   - non-admin users must not create records for another farm's `plot_id` or `bed_id`
   - handle plot-only, bed-only, and both-null records safely

2. Chemical products:
   - non-admin users must not show products from another farm
   - non-admin users must not update stock for products from another farm
   - global products (`farm_id = null`) remain readable to all authenticated users
   - only admins may mutate global products unless existing business rules clearly say otherwise

3. Post-season reviews:
   - non-admin users must not create a review for another farm's production plan
   - non-admin users must not submit another farm's review
   - non-admin users must not approve/reject another farm's review unless they are admin
   - keep existing role checks

4. Cost breakdown:
   - non-admin users must not calculate breakdowns for another farm's production plan
   - non-admin users must not calculate breakdowns for another farm's planting batch
   - admin behavior remains global

5. Tests:
   - add focused cross-farm denial regression tests for each fixed module
   - expected response should be 403 where an authenticated user is forbidden
   - keep existing tests green

## Verification

Run at least:

```bash
rtk php artisan test tests/Feature/SoilHistoryTest.php tests/Feature/ChemicalProductTest.php tests/Feature/PostSeasonReviewTest.php tests/Feature/CostBreakdownTest.php
```

If that passes, run:

```bash
rtk php artisan test
```

## Required Output

Create `.sisyphus/evidence/fix-al-security-farm-scope.md` with:

- files changed
- summary of fixed endpoints
- tests run and results
- any residual risks

Do not stage, commit, or push.
