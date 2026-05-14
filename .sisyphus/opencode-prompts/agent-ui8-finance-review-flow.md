# Agent UI8 - Finance and Post-season Review Flow

Read first:

- `.sisyphus/AGENT_MISSION_BRIEF.md`
- `.sisyphus/opencode-prompts/STRICT_AGENT_TEMPLATE.md`
- `.sisyphus/evidence/site-flow-gap-review-round2-2026-05-14.md`
- `app/Http/Controllers/Api/V1/CostBreakdownController.php`
- `app/Http/Controllers/Api/V1/CostRecordController.php`
- `app/Http/Controllers/Api/V1/PriceTableController.php`
- `app/Http/Controllers/Api/V1/MarginDashboardController.php`
- `app/Http/Controllers/Api/V1/PostSeasonReviewController.php`

Task:

Expose finance and MVP improvement loop.

Scope:

- Add `resources/js/pages/operations/finance/FinancePage.jsx`.
- Add `resources/js/pages/operations/reviews/PostSeasonReviewsPage.jsx`.
- Wire routes:
  - `/operations/finance`
  - `/operations/reviews`
- Finance page should show price tables, cost dashboard, and margin dashboard using available endpoints.
- Reviews page should list/create post-season reviews and support submit/approve/reject actions.

Constraints:

- Do not invent report APIs.
- Use existing post-season review request contract; inspect request classes before implementing forms.
- Keep charts simple; tables/cards are enough for this pass.

Verification:

- Run `rtk npm run build`.
- Run focused tests: `rtk php artisan test tests/Feature/CostBreakdownTest.php tests/Feature/CostingPriceMarginApiTest.php tests/Feature/PostSeasonReviewTest.php`.
- Write evidence to `.sisyphus/evidence/agent-ui8-finance-review-flow.md`.

