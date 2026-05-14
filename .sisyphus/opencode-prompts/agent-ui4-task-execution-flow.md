# Agent UI4 - Task Execution Flow

Read first:

- `.sisyphus/AGENT_MISSION_BRIEF.md`
- `.sisyphus/opencode-prompts/STRICT_AGENT_TEMPLATE.md`
- `.sisyphus/evidence/site-flow-gap-review-round2-2026-05-14.md`
- `app/Http/Controllers/Api/V1/WorkTaskController.php`
- `app/Http/Controllers/Api/V1/WorkTaskLogController.php`
- `resources/js/services/api.js`
- `resources/js/OperationsApp.jsx`

Task:

Implement the missing `/operations/tasks` execution flow.

Scope:

- Add `resources/js/pages/operations/tasks/TasksPage.jsx`.
- Add `resources/js/pages/operations/tasks/TaskDetailPage.jsx`.
- Wire routes:
  - `/operations/tasks`
  - `/operations/tasks/:id`
- Update operations navigation if needed.
- Task list must support filters/search for status, due date, assignee/batch when data is present.
- Task detail must support:
  - status transition via `PATCH /work-tasks/{id}/status`
  - assign worker when moving to `assigned`
  - complete with `completion_note`
  - submit log via `POST /work-tasks/{id}/logs` with `notes`, `actual_start_at`, `actual_end_at`, `photo_paths` text entries at minimum.

Constraints:

- Do not invent endpoints.
- Use default `api` import from `resources/js/services/api.js`; do not prefix `/api/v1`.
- Keep UI consistent with existing operations pages.
- Do not change backend unless an API contract bug is proven.

Verification:

- Run `rtk npm run build`.
- Run focused tests: `rtk php artisan test tests/Feature/WorkTaskApiTest.php tests/Feature/WorkTaskLogApiTest.php`.
- Write evidence to `.sisyphus/evidence/agent-ui4-task-execution-flow.md`.

