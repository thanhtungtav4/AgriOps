# Agent UI6 - Safety Flow

Read first:

- `.sisyphus/AGENT_MISSION_BRIEF.md`
- `.sisyphus/opencode-prompts/STRICT_AGENT_TEMPLATE.md`
- `.sisyphus/evidence/site-flow-gap-review-round2-2026-05-14.md`
- `app/Http/Controllers/Api/V1/IncidentController.php`
- `app/Http/Controllers/Api/V1/ChemicalUsageController.php`
- `app/Http/Controllers/Api/V1/ChemicalProductController.php`
- `app/Services/IsolationGuardService.php`

Task:

Expose safety workflow for incident reporting, chemical/biological usage, product stock, and isolation visibility.

Scope:

- Add `resources/js/pages/operations/safety/SafetyPage.jsx`.
- Add tabs or subcomponents:
  - incidents
  - chemical usages
  - chemical products / low stock
- Wire route `/operations/safety`.
- Create basic list/create forms using real API contracts.
- Show isolation end date and related batch/plot/bed when data exists.

Constraints:

- Do not invent endpoints.
- Use existing API options from model/controller constants where visible.
- Keep forms pragmatic; avoid large design rewrites.

Verification:

- Run `rtk npm run build`.
- Run focused tests: `rtk php artisan test tests/Feature/IncidentChemicalUsageApiTest.php tests/Feature/ChemicalProductTest.php`.
- Write evidence to `.sisyphus/evidence/agent-ui6-safety-flow.md`.

