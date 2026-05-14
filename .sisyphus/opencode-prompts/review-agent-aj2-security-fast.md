# Review Agent AJ2 - Fast Security Review

Work in `/Users/macbook/Herd/ariops`. Use `rtk` before commands.

Do not edit application code. Create only `.sisyphus/evidence/review-aj2-security-fast.md`.

Focus only on security/API contract risks in current uncommitted files:

- `app/Http/Controllers/Api/V1/PostSeasonReviewController.php`
- `app/Http/Controllers/Api/V1/SoilHistoryController.php`
- `app/Http/Controllers/Api/V1/ChemicalProductController.php`
- `app/Http/Controllers/Api/V1/CostBreakdownController.php`
- `routes/api.php`
- `routes/console.php`
- relevant tests

Look specifically for:

1. non-admin users reading/updating another farm's resource by ID
2. create/calculate actions accepting another farm's IDs
3. API response contract inconsistencies
4. missing regression tests for cross-farm denial

Output concise findings with severity, file:line, and recommendation.
