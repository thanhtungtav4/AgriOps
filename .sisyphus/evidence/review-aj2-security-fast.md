# Review Agent AJ2 - Fast Security Review

**Date:** 2026-05-14
**Scope:** Uncommitted controllers, routes, and tests

---

## Critical Issues

### 1. SoilHistoryController: No access control on `show`
- **File:** `app/Http/Controllers/Api/V1/SoilHistoryController.php:89-94`
- **Severity:** Critical
- **Issue:** Any authenticated user can read any soil history record by ID, regardless of farm ownership. No farm check is performed.
- **Recommendation:** Add farm ownership check: `if (!$user->isAdmin() && $record->plot->farm_id !== $user->farm_id) return $this->forbiddenError(...);` (or check via `bed` if plot is null)

### 2. SoilHistoryController: No cross-farm validation on `store`
- **File:** `app/Http/Controllers/Api/V1/SoilHistoryController.php:46-87`
- **Severity:** Critical
- **Issue:** Users with valid roles can create soil history records for ANY `plot_id` or `bed_id`, including those belonging to other farms. No ownership validation on the referenced plot/bed.
- **Recommendation:** Validate that `plot_id` (if provided) belongs to a plot in the user's farm: `Plot::where('id', $plotId)->where('farm_id', $user->farm_id)->exists()`

### 3. SoilHistoryController: No cross-farm validation on `update`
- **File:** `app/Http/Controllers/Api/V1/SoilHistoryController.php:96-134`
- **Severity:** Critical
- **Issue:** Users with valid roles can update ANY soil history record by ID, regardless of which farm it belongs to. Only role is checked, not farm ownership.
- **Recommendation:** Add farm check: resolve the record's plot/bed farm_id and compare to `$user->farm_id`

### 4. PostSeasonReviewController: No cross-farm validation on `store`
- **File:** `app/Http/Controllers/Api/V1/PostSeasonReviewController.php:51-79`
- **Severity:** Critical
- **Issue:** Non-admin users with `canManagePostSeasonReview()` can create reviews for ANY `production_plan_id`, even plans belonging to other farms. Only role permission is checked.
- **Recommendation:** Validate that the production plan belongs to the user's farm: `ProductionPlan::where('id', $planId)->where('farm_id', $user->farm_id)->exists()`

### 5. PostSeasonReviewController: No cross-farm validation on `submit`
- **File:** `app/Http/Controllers/Api/V1/PostSeasonReviewController.php:128-148`
- **Severity:** Critical
- **Issue:** Users with `canManagePostSeasonReview()` can submit ANY review by ID for approval, regardless of farm ownership.
- **Recommendation:** Add farm ownership check before allowing submit: `if (!$user->isAdmin() && $review->productionPlan->farm_id !== $user->farm_id)`

### 6. ChemicalProductController: No access control on `show`
- **File:** `app/Http/Controllers/Api/V1/ChemicalProductController.php:97-102`
- **Severity:** Critical
- **Issue:** Any authenticated user can view any chemical product by ID, including products from other farms. The `index` method filters correctly, but `show` has no guard.
- **Recommendation:** Add farm check: `if (!$user->isAdmin() && $product->farm_id && $product->farm_id !== $user->farm_id)`

### 7. ChemicalProductController: No cross-farm validation on `updateStock`
- **File:** `app/Http/Controllers/Api/V1/ChemicalProductController.php:146-172`
- **Severity:** Critical
- **Issue:** Users with valid roles can adjust stock for ANY chemical product by ID, regardless of farm ownership.
- **Recommendation:** Add same farm check as `update`: `if (!$user->isAdmin() && $product->farm_id && $product->farm_id !== $user->farm_id)`

### 8. CostBreakdownController: No cross-farm validation on `calculateForPlan`
- **File:** `app/Http/Controllers/Api/V1/CostBreakdownController.php:78-95`
- **Severity:** Critical
- **Issue:** Users with valid roles can trigger cost calculation for ANY production plan ID, regardless of farm. The service also does not validate farm ownership.
- **Recommendation:** Validate plan ownership before calling service: `ProductionPlan::where('id', $planId)->where('farm_id', $user->farm_id)->firstOrFail()`

### 9. CostBreakdownController: No cross-farm validation on `calculateForBatch`
- **File:** `app/Http/Controllers/Api/V1/CostBreakdownController.php:100-117`
- **Severity:** Critical
- **Issue:** Same as above — any planting batch ID can be used regardless of farm ownership.
- **Recommendation:** Validate batch ownership: `PlantingBatch::where('id', $batchId)->where('farm_id', $user->farm_id)->firstOrFail()`

---

## Important Issues

### 10. SoilHistoryController: `index` lacks farm scoping for non-admin
- **File:** `app/Http/Controllers/Api/V1/SoilHistoryController.php:15-44`
- **Severity:** Important
- **Issue:** Non-admin users can list ALL soil history records across all farms. Unlike `PostSeasonReviewController` and `ChemicalProductController`, there is no `whereHas` or `where` clause to filter by farm.
- **Recommendation:** Add farm filter: `if (!$user->isAdmin()) { $query->whereHas('plot', fn($q) => $q->where('farm_id', $user->farm_id)); }` (handle bed-only records separately)

### 11. PostSeasonReviewController: `approve`/`reject` lack farm scoping
- **File:** `app/Http/Controllers/Api/V1/PostSeasonReviewController.php:153-202`
- **Severity:** Important
- **Issue:** Approvers can approve/reject reviews from any farm. This may be intentional (global approvers), but if approvers should only handle their own farm, this needs a check.
- **Recommendation:** Clarify business intent. If approvers are farm-scoped, add: `if (!$user->isAdmin() && $review->productionPlan->farm_id !== $user->farm_id)`

### 12. API response contract inconsistency: `store` methods
- **Files:** Multiple controllers
- **Severity:** Important
- **Issue:** `PostSeasonReviewController::store` returns a custom error format for duplicates (line 62-69) that does not use the standard `ApiResponse` helpers. Other controllers use `$this->success()` / `$this->forbiddenError()` consistently.
- **Recommendation:** Use `$this->error()` or a dedicated conflict response helper for consistency

---

## Missing Regression Tests for Cross-Farm Denial

### PostSeasonReviewTest.php
- **Missing:** Test that farm_manager cannot create review for another farm's production plan
- **Missing:** Test that farm_manager cannot view/update/submit another farm's review by ID
- **Missing:** Test that non-admin user cannot approve/reject another farm's review

### SoilHistoryTest.php
- **Missing:** Test that farm_manager cannot view another farm's soil history record by ID
- **Missing:** Test that farm_manager cannot create soil history for another farm's plot/bed
- **Missing:** Test that farm_manager cannot update another farm's soil history record
- **Missing:** Test that non-admin user cannot list all soil histories across farms

### ChemicalProductTest.php
- **Missing:** Test that farm_manager cannot view another farm's chemical product by ID
- **Missing:** Test that farm_manager cannot update stock for another farm's product
- **Missing:** Test that non-admin user cannot list products from other farms (should be covered by index filtering, but no test exists)

### CostBreakdownTest.php
- **Missing:** Test that farm_manager cannot calculate breakdown for another farm's production plan
- **Missing:** Test that farm_manager cannot calculate breakdown for another farm's planting batch
- **Missing:** Test that farm_manager cannot calculate seasonal breakdown for another farm

---

## Summary

| Severity | Count |
|----------|-------|
| Critical | 9     |
| Important| 3     |

**Primary risk:** All four controllers have at least one endpoint where a non-admin user can read, create, update, or act on resources belonging to another farm by supplying a foreign ID. The `farm.scope` middleware only sets the farm context on the request — it does not automatically filter or validate resource ownership per endpoint.

**Recommended fix pattern:** For each endpoint that accepts a resource ID (or a foreign key like `production_plan_id`, `plot_id`, `farm_id`), add an ownership check:
```php
if (!$user->isAdmin() && $resource->farm_id !== $user->farm_id) {
    return $this->forbiddenError('...');
}
```
