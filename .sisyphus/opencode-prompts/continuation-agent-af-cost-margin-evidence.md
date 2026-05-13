# Continuation Agent AF - T13 Cost/Margin Evidence

You are not alone in this repository. Other agents may be working in parallel. Do not revert or overwrite unrelated changes. Do not commit or push.

Work in `/Users/macbook/Herd/ariops`. Prefix shell commands with `rtk`.

## Goal

Close MVP-2 evidence-index gaps for:

- `.sisyphus/evidence/task-13-margin-happy.log`
- `.sisyphus/evidence/task-13-cost-validation.log`

## Scope You Own

- `tests/Feature/CostingPriceMarginApiTest.php` only if evidence reveals missing test coverage
- `.sisyphus/evidence/task-13-margin-happy.log`
- `.sisyphus/evidence/task-13-cost-validation.log`
- `.sisyphus/evidence/task-13-cost-margin-continuation.md`

## Must Not Touch

- Application code unless a focused test proves a real bug
- Mobile/React files
- Release risk log or agent board

## Required Workflow

1. Read `CostingPriceMarginApiTest`, `CostRecordController`, `MarginDashboardController`, `PriceTableController`, and current evidence.
2. Identify whether margin happy path and cost validation are already tested.
3. Add focused tests only if necessary.
4. Run `rtk php artisan test tests/Feature/CostingPriceMarginApiTest.php`.
5. Write the two exact `.log` evidence files and a short continuation summary.

## Output

Final response must list changed files and exact test result.
