# AgriOps Multi-Agent Board

Date: 2026-05-12
Owner: CTO/Integrator
Purpose: Coordinate multiple agents safely while accelerating MVP-0/MVP-1.

## Operating Rule

Multiple agents may run in parallel only when each workstream has clear ownership, evidence path, and file boundaries.

The Integrator is the only role allowed to reconcile cross-cutting decisions, update shared contracts, and declare a task ready for downstream work.

## Roles

### Integrator / CTO

Responsibilities:

- Owns plan coherence and architecture decisions.
- Reviews all agent outputs before merging into the main implementation direction.
- Updates `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`.
- Runs final relevant test suite and evidence checks.
- Resolves conflicts in shared files.

Owns:

- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`
- `.sisyphus/agent-board.md`
- Cross-cutting architecture decisions

Must review:

- `routes/api.php`
- shared models
- migrations touching multiple modules
- API response contracts
- RBAC/policy boundaries

## Active Workstreams

| Agent | Scope | Owns | Must Not Touch | Status | Evidence |
|---|---|---|---|---|---|
| A Planning | Close Task 4 planning gaps | `app/Services/PlanningService.php`, `tests/Feature/PlanningApiTest.php`, planning-specific request/response code | auth, RBAC, unrelated migrations, QR, delivery | Review | `.sisyphus/evidence/task-04-*.log` |
| B DB/QA | Data dictionary, PostgreSQL QC, seed strategy | `.sisyphus/evidence/task-01-db-architecture-qc.log`, data dictionary docs, seed/QC notes | planning service logic, auth policies | Review | `.sisyphus/evidence/data-dictionary-baseline.md` |
| C Security/API | Farm scope, RBAC matrix, API error contract | policy tests, auth/security notes, error schema docs | planning formula internals, DB schema redesign | Review | `.sisyphus/evidence/final-f3c-security-privacy.md` |
| D Business Rules | Soil history, season/climate, multi-harvest, quality taxonomy | business specs and domain rule proposals | production code unless assigned by Integrator | Review | `.sisyphus/evidence/final-f4b-business-architecture.md`, `.sisyphus/evidence/business-rules-v1.md` |
| E QA/Evidence | Traceability matrix and release gates | QA matrix, smoke checklist, evidence index | application code unless explicitly assigned | Review | `.sisyphus/evidence/final-f1b-qa-traceability.md`, `.sisyphus/evidence/evidence-index.md`, `.sisyphus/evidence/release-risk-log.md` |
| C2 Security Scope | Implement farm scope/RBAC/token MVP-0 fixes | selected API controllers, security tests, security evidence | planning formulas, seeders, migrations, Filament | Review | `.sisyphus/evidence/security-scope-implementation.md` |
| B2 Seed/Postgres | Idempotent seed and local PostgreSQL evidence | seeders, seeder tests, seed/PostgreSQL evidence | API controllers, planning formulas, auth policies | Review | `.sisyphus/evidence/seed-postgres-evidence.md` |
| F T5 Batch Foundation | Planting batch/allocation schema foundation | batch migrations/models/tests, task 05 evidence | API controllers, auth/security, seeders, planning formulas | Review | `.sisyphus/evidence/task-05-batch-foundation.md` |
| G T5 Lifecycle API | Planting batch API and lifecycle transitions | `PlantingBatchLifecycleService`, `PlantingBatchController`, lifecycle API tests | allocation service, seeders, auth/token, planning formulas | Review | `.sisyphus/evidence/task-05-lifecycle-api.md` |
| H Allocation Guards | Allocation domain guard service | `PlantingBatchAllocationService`, allocation guard tests | routes/controllers, auth/security, seeders, planning formulas | Review | `.sisyphus/evidence/task-05-allocation-guards.md` |
| I MVP-0 Hardening | Login throttle and Task 4 TDD evidence cleanup | auth rate limit test, TDD trace/evidence | batch/allocation code, seeders, planning formula code | Review | `.sisyphus/evidence/mvp0-hardening.md` |
| J Work Task Schema | Task 6 work task schema/model foundation | `work_tasks` migration, `WorkTask` model, schema tests | routes/controllers, task generation service, auth/security, seeders | Review | `.sisyphus/evidence/task-06-work-task-schema.md` |
| K Task Generation | Generate work tasks from batch/growth stages | `WorkTaskGenerationService`, generation tests | routes/controllers, auth/security, seeders, planning formulas | Review | `.sisyphus/evidence/task-06-task-generation-service.md` |
| L Work Task API | Task 6 work task mobile-facing API | `WorkTaskController`, `WorkTaskStatusService`, work task API tests, minimal routes | farming log schema/model, seeders, Filament, planning formulas | Review | `.sisyphus/evidence/task-06-work-task-api.md` |
| M Farming Log Foundation | Task 6 farming log schema/model foundation | `farming_logs` migration, `FarmingLog` model, foundation tests | routes/controllers, work task API service/controller, seeders, Filament | Review | `.sisyphus/evidence/task-06-farming-log-foundation.md` |
| N Packing Schema | Task 10 processing/packing schema foundation | processing/packing migrations, models, foundation tests | routes/controllers, services, Filament, seeders, public QR | Review | `.sisyphus/evidence/task-10-packing-foundation.md` |
| O Packing API | Task 10 packing lot API and source guards | `PackingLotService`, `PackingLotController`, packing API tests, packing evidence logs | migrations unless compatibility fix, trace graph service, Filament, seeders, public QR | Review | `.sisyphus/evidence/task-10-packing-api.md` |
| P Trace Graph | Task 10 public-safe traceability graph backend | `TraceabilityGraphService`, traceability graph tests | routes/controllers, public QR, packing API service, Filament, seeders | Review | `.sisyphus/evidence/task-10-traceability-graph.md` |
| Q React Dashboard UX | React operations dashboard UX polish | `resources/js/pages/OperationsDashboard.jsx`, dashboard UX evidence | auth/API files, backend routes/controllers, Expo | Review | `.sisyphus/evidence/task-15-react-dashboard-ux.md` |
| R React Auth/API | React operations auth/API resilience | `AuthContext.jsx`, `api.js`, `LoginPage.jsx`, auth/API evidence | dashboard component, backend routes/controllers, Expo | Review | `.sisyphus/evidence/task-15-react-auth-api-resilience.md` |
| S Browser QA | React operations browser/build QA | browser QA evidence/screenshots only | application code | Review | `.sisyphus/evidence/task-15-react-browser-qa.md` |
| T Qwen UX Review | React admin UX friction review | Qwen UX review evidence only | application code | Review | `.sisyphus/evidence/task-15-react-admin-qwen-ux-review.md` |
| U NVIDIA Route Tests | React operations route/build tests | `OperationsWebRoutesTest.php`, route test evidence | React component/auth files, route definitions unless documented | Review | `.sisyphus/evidence/task-15-react-operations-routes-tests.md` |
| V ZEN API Contract | React/Expo API contract review | API contract review evidence only | application code | Review | `.sisyphus/evidence/task-15-react-api-contract-review.md` |

Model assignment:

- Agent A Planning: current session started before explicit model mapping; future runs use `vps-103/claude_sonet_4.5`.
- Agent B DB/QA: `vps-163/claude_sonet_4.5`.
- Agent C Security/API: `vps-180/claude_sonet_4.5`.
- Agent D Business Rules: `vps-103/claude_sonet_4.5`.
- Agent E QA/Evidence: `vps-163/claude_sonet_4.5`.
- Agent C2 Security Scope: `vps-180/claude_sonet_4.5`.
- Agent B2 Seed/Postgres: `vps-163/claude_sonet_4.5`.
- Agent F T5 Batch Foundation: `vps-103/claude_sonet_4.5`.
- Agent G T5 Lifecycle API: `vps-103/claude_sonet_4.5`.
- Agent H Allocation Guards: `vps-163/claude_sonet_4.5`.
- Agent I MVP-0 Hardening: `vps-180/claude_sonet_4.5`.
- Agent J Work Task Schema: `vps-103/claude_sonet_4.5`.
- Agent K Task Generation: `vps-163/claude_sonet_4.5`.
- Agent L Work Task API: `vps-103/claude_sonet_4.5`.
- Agent M Farming Log Foundation: `vps-180/claude_sonet_4.5`.
- Agent N Packing Schema: `vps-103/claude_sonet_4.5`.
- Agent O Packing API: `vps-163/claude_sonet_4.5`.
- Agent P Trace Graph: `vps-180/claude_sonet_4.5`.
- Agent Q React Dashboard UX: `vps-103/claude_sonet_4.5`.
- Agent R React Auth/API: `vps-163/claude_sonet_4.5`.
- Agent S Browser QA: `vps-180/claude_sonet_4.5`.
- Agent T Qwen UX Review: `alibaba/qwen3-coder-plus`.
- Agent U NVIDIA Route Tests: `nvidia/qwen/qwen3-coder-480b-a35b-instruct`.
- Agent V ZEN API Contract: `opencode-go/qwen3.6-plus`.

Runbook:

- `.sisyphus/opencode-runbook.md`
- `.sisyphus/run-opencode-agents.sh`
## Current Priority

Priority 1: Continue MVP-1 release hardening and close remaining accepted evidence gaps.

Current verification:

- Farm scope/security tests pass.
- Seeders are idempotent and local PostgreSQL migrations are applied.
- Task 5 planting batch/allocation foundation, lifecycle API, and allocation guard tests pass.
- Task 6 work task/log/photo APIs pass.
- Task 7 incident/chemical/biological usage and isolation guard tests pass.
- Task 8 pre-harvest inspection and harvest eligibility tests pass.
- Task 9 harvest lots, grade breakdown, and canonical reject taxonomy tests pass.
- Task 10 processing/packing and Task 11 QR privacy tests pass.
- Audit hooks cover lifecycle transitions plus allocation create/remove.
- API error contract has additional crop lookup, planning domain-error, validation, and throttle coverage.
- Mobile logic-layer smoke covers task receive/status/log/photo and offline conflict handling; device readiness evidence documents the remaining emulator/device blocker.
- Full suite passes cleanly: `298 tests, 1062 assertions`.

Next likely work:

- Run device-level mobile smoke for task receive -> log -> photo when a device/emulator is available.
- Capture staging PostgreSQL migration evidence with owner credentials.
- Continue API error-contract cleanup for deferred approval endpoints.

## Workstream Briefs

### Agent A: Planning

Goal:

Close Task 4 gaps without touching unrelated modules.

Expected output:

- Planning response includes `assumptions`.
- Planning response includes `fulfillment`.
- Plant counts include estimate and execution values.
- Missing/unsupported unit conversion returns domain error.
- Tests cover happy path, missing norms, rounding, assumptions, fulfillment.

Preferred files:

- `app/Services/PlanningService.php`
- `app/Http/Controllers/Api/V1/PlanningController.php` only if response validation needs small updates
- `tests/Feature/PlanningApiTest.php`

Shared files needing Integrator review:

- `database/migrations/*production_plans*`
- `routes/api.php`
- model relationships used by other modules

Evidence:

- `.sisyphus/evidence/task-04-green.log`
- `.sisyphus/evidence/task-04-refactor.log`
- `.sisyphus/evidence/task-04-tdd-trace.md`

### Agent B: DB/QA

Goal:

Make database foundation reviewable and PostgreSQL-safe.

Expected output:

- Data dictionary baseline for core tables.
- PostgreSQL constraint/index checklist.
- Canonical seed and negative seed strategy.
- SQL integrity checks for critical pivots and enum/unit fields.

Preferred files:

- `.sisyphus/evidence/task-01-db-architecture-qc.log`
- `.sisyphus/evidence/final-f2b-data-quality.log`
- `.sisyphus/evidence/final-f2c-db-architecture.md`
- seed notes or tests only after Integrator approval

Must not touch:

- planning formulas
- auth policies
- frontend/client code

### Agent C: Security/API

Goal:

Ensure auth, farm scope, API errors, and public/private boundaries are safe before broader API growth.

Expected output:

- RBAC matrix draft.
- Farm-scope access tests/proposals.
- Sanctum token policy notes.
- API error schema proposal.
- Public QR privacy boundary checklist.

Preferred files:

- `.sisyphus/evidence/final-f3c-security-privacy.md`
- `.sisyphus/evidence/api-error-contract-v1.md`
- tests for auth/policy behavior when assigned

Must not touch:

- planning formula math
- DB schema redesign
- QR implementation until Task 10/11 begins

### Agent D: Business Rules

Goal:

Prevent technically correct but operationally wrong implementation.

Expected output:

- Season/climate business rules.
- Soil history/crop rotation rules.
- Multi-harvest lifecycle notes.
- Quality standard and reject reason taxonomy.
- Override governance matrix.

Preferred files:

- `.sisyphus/evidence/final-f4b-business-architecture.md`
- `.sisyphus/evidence/business-rules-v1.md`

Must not touch:

- production code unless assigned by Integrator

### Agent E: QA/Evidence

Goal:

Make release readiness auditable.

Expected output:

- BRD requirement -> task -> test -> evidence matrix.
- MVP-0/MVP-1 smoke checklist.
- Evidence index.
- Known issues/risk log template.

Preferred files:

- `.sisyphus/evidence/final-f1b-qa-traceability.md`
- `.sisyphus/evidence/evidence-index.md`
- `.sisyphus/evidence/release-risk-log.md`

Must not touch:

- application code unless assigned by Integrator

## Shared File Rules

These files are shared and require Integrator ownership or review:

- `routes/api.php`
- `app/Models/User.php`
- shared Eloquent models
- cross-module migrations
- `bootstrap/app.php`
- `.env.example`
- plan files under `.sisyphus/plans/`

## Merge Protocol

Every agent final update must include:

- Summary of completed work.
- Files changed.
- Tests run.
- Evidence paths created/updated.
- Open risks.
- Next recommended step.

Integrator review checklist:

- Does the work match the assigned scope?
- Did the agent touch prohibited files?
- Are tests/evidence present?
- Does the output conflict with business guardrails?
- Are shared contracts updated consistently?
- Is downstream work now unblocked or still gated?

## Conflict Policy

If two agents need the same file:

1. Stop one workstream or narrow its scope.
2. Let the Integrator make the shared edit.
3. Ask the waiting agent to rebase its reasoning on the updated contract.

Do not allow parallel edits to shared migrations, route files, or API contracts.

## Status Legend

- Ready: workstream can start.
- In Progress: assigned and active.
- Blocked: waiting for another task/decision.
- Review: output ready for Integrator review.
- Done: accepted by Integrator with evidence.

## Immediate Next Step

Start with only these three parallel workstreams:

1. Agent A Planning
2. Agent B DB/QA
3. Agent C Security/API

Do not start React/Expo implementation until Task 4-11 API/domain contracts are stable.
