# OpenCode Multi-Agent Runbook

Date: 2026-05-12
Owner: CTO/Integrator

## Available VPS Models

Verified with:

```bash
rtk opencode models
rtk opencode providers list
```

Models:

- `vps-103/claude_sonet_4.5`
- `vps-163/claude_sonet_4.5`
- `vps-180/claude_sonet_4.5`
- `alibaba/qwen3-coder-plus`
- `nvidia/qwen/qwen3-coder-480b-a35b-instruct`
- `opencode-go/qwen3.6-plus`

## Prompt Files

- Agent A Planning: `.sisyphus/opencode-prompts/agent-a-planning.md`
- Agent B DB/QA: `.sisyphus/opencode-prompts/agent-b-db-qa.md`
- Agent C Security/API: `.sisyphus/opencode-prompts/agent-c-security-api.md`
- Agent D Business Rules: `.sisyphus/opencode-prompts/agent-d-business-rules.md`
- Agent E QA/Evidence: `.sisyphus/opencode-prompts/agent-e-qa-evidence.md`
- Agent C2 Security Scope Implementation: `.sisyphus/opencode-prompts/agent-c2-security-scope-implementation.md`
- Agent B2 Seed/PostgreSQL Evidence: `.sisyphus/opencode-prompts/agent-b2-seed-postgres-evidence.md`
- Agent F T5 Batch Foundation: `.sisyphus/opencode-prompts/agent-f-t5-batch-foundation.md`
- Agent G T5 Lifecycle API: `.sisyphus/opencode-prompts/agent-g-t5-lifecycle-api.md`
- Agent H Allocation Guards: `.sisyphus/opencode-prompts/agent-h-allocation-guards.md`
- Agent I MVP-0 Hardening: `.sisyphus/opencode-prompts/agent-i-mvp0-hardening.md`
- Agent J Work Task Schema: `.sisyphus/opencode-prompts/agent-j-work-task-schema.md`
- Agent K Task Generation Service: `.sisyphus/opencode-prompts/agent-k-task-generation-service.md`
- Agent L Work Task API: `.sisyphus/opencode-prompts/agent-l-work-task-api.md`
- Agent M Farming Log Foundation: `.sisyphus/opencode-prompts/agent-m-farming-log-foundation.md`
- Agent N Packing Schema Foundation: `.sisyphus/opencode-prompts/agent-n-packing-schema.md`
- Agent O Packing API and Source Guards: `.sisyphus/opencode-prompts/agent-o-packing-api-service.md`
- Agent P Traceability Graph Backend: `.sisyphus/opencode-prompts/agent-p-traceability-graph.md`
- Agent Q React Dashboard UX: `.sisyphus/opencode-prompts/agent-q-react-dashboard-ux.md`
- Agent R React Auth/API Resilience: `.sisyphus/opencode-prompts/agent-r-react-auth-api-resilience.md`
- Agent S React Browser QA: `.sisyphus/opencode-prompts/agent-s-react-browser-qa.md`
- Agent T Qwen React UX Review: `.sisyphus/opencode-prompts/agent-t-qwen-react-ux-review.md`
- Agent U NVIDIA Operations Route Tests: `.sisyphus/opencode-prompts/agent-u-nvidia-operations-routes-tests.md`
- Agent V OpenCode Go/ZEN API Contract Review: `.sisyphus/opencode-prompts/agent-v-zend-api-contract-review.md`
- Agent W Crop Catalog Seed: `.sisyphus/opencode-prompts/agent-w-crop-catalog-seed.md`

## Recommended Assignment

| Agent | Model | Reason |
|---|---|---|
| A Planning | `vps-103/claude_sonet_4.5` | Code implementation and tests |
| B DB/QA | `vps-163/claude_sonet_4.5` | Schema/documentation/evidence review |
| C Security/API | `vps-180/claude_sonet_4.5` | Security/API contract review |
| D Business Rules | `vps-103/claude_sonet_4.5` | Business architecture review after Planning implementation |
| E QA/Evidence | `vps-163/claude_sonet_4.5` | Traceability and release gate evidence |
| C2 Security Scope | `vps-180/claude_sonet_4.5` | Farm scope/RBAC/token implementation |
| B2 Seed/Postgres | `vps-163/claude_sonet_4.5` | Seeder reliability and PostgreSQL evidence |
| F T5 Batch Foundation | `vps-103/claude_sonet_4.5` | Planting batch/allocation schema foundation |
| G T5 Lifecycle API | `vps-103/claude_sonet_4.5` | Planting batch API and lifecycle transitions |
| H Allocation Guards | `vps-163/claude_sonet_4.5` | Allocation domain guard service |
| I MVP-0 Hardening | `vps-180/claude_sonet_4.5` | Login throttle and TDD evidence cleanup |
| J Work Task Schema | `vps-103/claude_sonet_4.5` | Work task schema/model foundation |
| K Task Generation | `vps-163/claude_sonet_4.5` | Generate tasks from batch/growth stages |
| L Work Task API | `vps-103/claude_sonet_4.5` | Work task API and status transitions |
| M Farming Log Foundation | `vps-180/claude_sonet_4.5` | Farming log schema/model foundation |
| N Packing Schema | `vps-103/claude_sonet_4.5` | Processing/packing schema foundation |
| O Packing API | `vps-163/claude_sonet_4.5` | Packing API and source availability guard |
| P Trace Graph | `vps-180/claude_sonet_4.5` | Public-safe traceability graph backend |
| Q React Dashboard UX | `vps-103/claude_sonet_4.5` | Operations dashboard implementation |
| R React Auth/API | `vps-163/claude_sonet_4.5` | Auth and API client implementation |
| S Browser QA | `vps-180/claude_sonet_4.5` | Browser/build QA evidence |
| T Qwen UX Review | `alibaba/qwen3-coder-plus` | UI/UX friction review |
| U NVIDIA Route Tests | `nvidia/qwen/qwen3-coder-480b-a35b-instruct` | Route/build tests |
| V ZEN API Contract | `opencode-go/qwen3.6-plus` | API contract review for React/Expo reuse |
| W Crop Catalog Seed | `alibaba/qwen3-coder-480b-a35b-instruct` | Realistic Vietnamese crop/norm seed implementation |

## Run Commands

Run from `/Users/macbook/Herd/ariops`.

```bash
rtk opencode run "Execute the attached Agent A Planning prompt. Work in /Users/macbook/Herd/ariops and follow the scope exactly." \
  -f .sisyphus/opencode-prompts/agent-a-planning.md \
  --title "AgriOps Agent A Planning" \
  -m vps-103/claude_sonet_4.5
```

```bash
rtk opencode run "Execute the attached Agent B DB/QA prompt. Work in /Users/macbook/Herd/ariops and follow the scope exactly." \
  -f .sisyphus/opencode-prompts/agent-b-db-qa.md \
  --title "AgriOps Agent B DB-QA" \
  -m vps-163/claude_sonet_4.5
```

```bash
rtk opencode run "Execute the attached Agent C Security/API prompt. Work in /Users/macbook/Herd/ariops and follow the scope exactly." \
  -f .sisyphus/opencode-prompts/agent-c-security-api.md \
  --title "AgriOps Agent C Security-API" \
  -m vps-180/claude_sonet_4.5
```

```bash
rtk opencode run "Execute the attached Agent D Business Rules prompt. Work in /Users/macbook/Herd/ariops and follow the scope exactly." \
  -f .sisyphus/opencode-prompts/agent-d-business-rules.md \
  --title "AgriOps Agent D Business-Rules" \
  -m vps-103/claude_sonet_4.5
```

```bash
rtk opencode run "Execute the attached Agent E QA/Evidence prompt. Work in /Users/macbook/Herd/ariops and follow the scope exactly." \
  -f .sisyphus/opencode-prompts/agent-e-qa-evidence.md \
  --title "AgriOps Agent E QA-Evidence" \
  -m vps-163/claude_sonet_4.5
```

## Important CLI Note

For `opencode run`, put the message before `-f`.

This works:

```bash
rtk opencode run "Execute attached prompt" -f .sisyphus/opencode-prompts/agent-a-planning.md
```

This fails because opencode may parse the message as another file:

```bash
rtk opencode run -f .sisyphus/opencode-prompts/agent-a-planning.md "Execute attached prompt"
```

## Integrator Checklist

After each agent finishes:

1. Read final summary.
2. Check changed files match ownership.
3. Run relevant tests.
4. Inspect evidence paths.
5. Update `.sisyphus/agent-board.md` status.
6. Resolve shared file conflicts manually.
7. Decide whether downstream task is unblocked.

## Current Session Notes

- Agent A Planning was started first before explicit model assignment was documented.
- Agent B and Agent C can run in parallel because they are doc/evidence scoped and must not touch planning formula internals.
