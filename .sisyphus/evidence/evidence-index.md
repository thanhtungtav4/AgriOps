# Evidence Index - AgriOps MVP-0/MVP-1

**Generated:** 2026-05-12
**Agent:** E - QA/Evidence
**Project:** /Users/macbook/Herd/ariops
**Last Updated:** 2026-05-13 (Continuation Agents AD-AH - Evidence/RBAC Cleanup)

---

## Purpose

This index catalogs all evidence artifacts in `.sisyphus/evidence/` for:
- Auditability: Find who created what and when
- Traceability: Map artifacts to BRD requirements and plan tasks
- Coverage: Identify missing evidence gaps
- Release readiness: Verify gate criteria are met

---

## Artifact Directory

**Location:** `/Users/macbook/Herd/ariops/.sisyphus/evidence/`

---

## MVP-0 Evidence Artifacts (T1-T4)

### Database Architecture (Task 1)

| File | Type | Created | Purpose | Owner | Status |
|------|------|---------|---------|-------|--------|
| `task-01-migration.log` | Log | 2026-05-12 | Migration fresh run evidence | Agent B | ✅ |
| `task-01-db-architecture-qc.log` | Log | 2026-05-12 | DB architecture analysis (FK, index, enum) | Agent B | ✅ |
| `data-dictionary-baseline.md` | Spec | 2026-05-12 | Field-by-field dictionary for MVP-0 tables | Agent B | ✅ |
| `final-f2c-db-architecture.md` | Checklist | 2026-05-12 | PostgreSQL constraint/index recommendation | Agent B | ✅ |
| `seed-strategy.md` | Spec | 2026-05-12 | Canonical + negative seed data specification | Agent B | ✅ |
| `final-f2b-data-quality.log` | Spec | 2026-05-12 | SQL integrity check proposal | Agent B | ✅ |

### Auth + RBAC (Task 2)

| File | Type | Created | Purpose | Owner | Status |
|------|------|---------|---------|-------|--------|
| `task-02-auth-happy.log` | Log | 2026-05-12 | Auth smoke test evidence | Agent C | ✅ |
| `rbac-matrix-v1.md` | Spec | 2026-05-12 | RBAC permission matrix (resource × action × role) | Agent C | 🟡 |
| `api-error-contract-v1.md` | Spec | 2026-05-12 | API error schema standard v1 | Agent C | 🟡 |
| `final-f3c-security-privacy.md` | Audit | 2026-05-12 | Security gap analysis | Agent C | ⚠️ |

### Master Data (Task 3)

| File | Type | Created | Purpose | Owner | Status |
|------|------|---------|---------|-------|--------|
| `task-03-master-happy.log` | Log | 2026-05-12 | Master data CRUD smoke evidence | Agent A | ✅ |

### Planning (Task 4)

| File | Type | Created | Purpose | Owner | Status |
|------|------|---------|---------|-------|--------|
| `task-04-green.log` | Log | 2026-05-12 | Planning tests passing (5 tests, 52 assertions) | Agent A | ✅ |
| `task-04-red.log` | Log | 2026-05-13 | TDD RED phase evidence (reconstructed) | Agent A / AH | 🟡 |
| `task-04-refactor.log` | Log | 2026-05-12 | Refactor phase evidence | Agent A | ✅ |
| `task-04-migration.log` | Log | 2026-05-12 | T4 migration evidence | Agent A | ✅ |
| `task-04-routes.log` | Log | 2026-05-12 | T4 routes registration evidence | Agent A | ✅ |
| `task-04-tdd-trace.md` | Trace | 2026-05-12 | TDD cycle documentation | Agent A | ✅ |
| `task-04-planning-formula-spec.md` | Spec | 2026-05-12 | Planning formula contract | Agent A | ✅ |

### QA Evidence Files (Agent E)

| File | Type | Created | Purpose | Owner | Status |
|------|------|---------|---------|-------|--------|
| `final-f1b-qa-traceability.md` | Matrix | 2026-05-12 | BRD→Task→Test→Gate matrix | Agent E | ✅ |
| `smoke-mvp0.md` | Checklist | 2026-05-12 | MVP-0 smoke test checklist | Agent E | ✅ |
| `smoke-mvp1.md` | Checklist | 2026-05-12 | MVP-1 demand-to-QR smoke checklist | Agent E | ✅ |
| `mvp0-smoke-run.md` | Evidence | 2026-05-12 | Actual MVP-0 smoke test run evidence | Agent E | ✅ |
| `final-f4b-business-architecture.md` | Spec | 2026-05-12 | Business architecture guardrails | Agent D | ✅ |
| `business-rules-v1.md` | Spec | 2026-05-12 | Detailed business rules and override governance | Agent D | ✅ |
| `security-scope-implementation.md` | Evidence | 2026-05-12 | Farm scope, token policy, and API security implementation | Agent C2 | ✅ |
| `rbac-approval-audit-continuation.md` | Evidence | 2026-05-13 | Approval endpoint RBAC audit and worker-denial regressions | Agent AD / Integrator | ✅ |
| `seed-postgres-evidence.md` | Evidence | 2026-05-12 | Idempotent seeders and local PostgreSQL evidence | Agent B2 | ✅ |
| `postgres-evidence-continuation.md` | Evidence | 2026-05-13 | Local PostgreSQL migration status and staging-owner gate | Agent AB / Integrator | ✅ |
| `task-05-batch-foundation.md` | Evidence | 2026-05-12 | Planting batch/allocation schema, models, and tests | Agent F | ✅ |
| `task-05-lifecycle-api.md` | Evidence | 2026-05-13 | Planting batch API and lifecycle transition service | Agent G | ✅ |
| `task-05-allocation-guards.md` | Evidence | 2026-05-13 | Allocation guard service and tests | Agent H | ✅ |
| `mvp0-hardening.md` | Evidence | 2026-05-13 | Login throttling and Task 4 TDD trace hardening | Agent I | ✅ |
| `task-06-work-task-schema.md` | Evidence | 2026-05-13 | Work task schema, model relationships, and schema tests | Agent J | ✅ |
| `task-06-task-generation-service.md` | Evidence | 2026-05-13 | Work task generation from growth stages with allocation guards | Agent K / Integrator | ✅ |
| `task-06-work-task-api.md` | Evidence | 2026-05-13 | Work task list/show/generate/status API and farm-scope tests | Agent L / Integrator | ✅ |
| `task-06-farming-log-foundation.md` | Evidence | 2026-05-13 | Farming log schema/model foundation and relationship tests | Agent M / Integrator | ✅ |
| `task-06-work-task-log-api.md` | Evidence | 2026-05-13 | Work task farming log submission API and validation tests | Integrator | ✅ |
| `task-07-incident-chemical-isolation.md` | Evidence | 2026-05-13 | Incident API, chemical/biological usage API, and isolation guard tests | Integrator | ✅ |
| `task-08-pre-harvest-inspection.md` | Evidence | 2026-05-13 | Pre-harvest inspection API, approval gate, and harvest eligibility tests | Integrator | ✅ |
| `task-09-harvest-lots.md` | Evidence | 2026-05-13 | Harvest lot API, grade breakdown, and eligibility guard tests | Integrator | ✅ |

---

## MVP-1 Evidence Artifacts (T5-T11)

| File | Type | Purpose | Owner | Status |
|------|------|---------|-------|--------|
| `task-05-batch-foundation.md` | Evidence | Batch/allocation schema and model foundation | Agent F | ✅ |
| `task-05-lifecycle-api.md` | Evidence | Batch lifecycle API/service tests | Agent G | ✅ |
| `task-05-allocation-guards.md` | Evidence | Allocation guard service tests | Agent H | ✅ |
| `task-05-audit-hooks.md` | Evidence | Lifecycle and allocation audit event hooks | Agent G / Integrator | ✅ |
| `task-06-work-task-schema.md` | Evidence | Work task schema/model tests | Agent J | ✅ |
| `task-06-task-generation-service.md` | Evidence | Work task generation service tests | Agent K / Integrator | ✅ |
| `task-06-work-task-api.md` | Evidence | Work task list/show/generate/status API tests | Agent L / Integrator | ✅ |
| `task-06-farming-log-foundation.md` | Evidence | Farming log schema/model foundation tests | Agent M / Integrator | ✅ |
| `task-06-work-task-log-api.md` | Evidence | Work task log API, binary photo upload, and validation tests | Integrator | ✅ |
| `task-06-smoke-evidence.md` | Evidence | API smoke for work task receive, status transition, log submit, and photo upload | Agent G / Integrator | ✅ |
| `mobile-smoke-continuation.md` | Evidence | Mobile logic-layer smoke for task receive/status/log/photo/offline handling | Agent Y / Integrator | ✅ |
| `mobile-device-smoke-readiness.md` | Evidence | Device/emulator smoke readiness and blocker checklist | Agent AA | ✅ |
| `task-06-task-log-happy.log` | Log | Manual/mobile photo upload happy path | TBD | 🟡 |
| `task-06-task-log-validation.log` | Log | Manual/mobile photo upload validation errors | TBD | 🟡 |
| `task-07-incident-chemical-isolation.md` | Evidence | Incident + chemical usage trace and isolation guard tests | Integrator | ✅ |
| `task-07-smoke-evidence.md` | Evidence | API smoke for incident, chemical usage, and isolation guard | Agent G / Integrator | ✅ |
| `task-07-chemical-trace.log` | Log | Manual/API incident + chemical usage trace smoke | TBD | 🟡 |
| `task-07-isolation-block.log` | Log | Manual/API isolation guard smoke | TBD | 🟡 |
| `task-08-pre-harvest-inspection.md` | Evidence | Inspection pass/fail and harvest eligibility gate tests | Integrator | ✅ |
| `task-08-smoke-evidence.md` | Evidence | API smoke for pre-harvest inspection and harvest unlock gate | Agent G / Integrator | ✅ |
| `task-08-inspection-pass.log` | Log | Manual/API inspection pass smoke | TBD | 🟡 |
| `task-08-inspection-fail.log` | Log | Manual/API inspection fail smoke | TBD | 🟡 |
| `task-09-harvest-lots.md` | Evidence | Harvest with grade breakdown and validation tests | Integrator | ✅ |
| `task-09-taxonomy-smoke.md` | Evidence | Canonical reject reason taxonomy TDD and smoke evidence | Agent G / Integrator | ✅ |
| `task-09-harvest-happy.log` | Log | Manual/API harvest happy path smoke | TBD | 🟡 |
| `task-09-harvest-validation.log` | Log | Manual/API harvest validation smoke | TBD | 🟡 |
| `task-10-packing-mix.log` | Log | Packing lot with multiple sources | ✅ |
| `task-10-packing-source-invalid.log` | Log | Invalid packing source rejection | Integrator | ✅ |
| `task-11-qr-public.log` | Log | QR public endpoint evidence | ✅ |
| `task-11-qr-privacy.log` | Log | QR privacy whitelist test | ✅ |

---

## MVP-2 Evidence Artifacts (T12-T14)

| File | Type | Purpose | Owner | Status |
|------|------|---------|-------|--------|
| `task-12-delivery-revenue.log` | Log | Delivery → revenue calculation | Agent AE / Integrator | ✅ |
| `task-12-return-flow.log` | Log | Return flow evidence | Agent AE / Integrator | ✅ |
| `task-12-delivery-return-continuation.md` | Evidence | Delivery/return evidence summary | Agent AE / Integrator | ✅ |
| `task-13-margin-happy.log` | Log | Cost/margin calculation | Agent AF / Integrator | ✅ |
| `task-13-cost-validation.log` | Log | Cost record validation | Agent AF / Integrator | ✅ |
| `task-13-cost-margin-continuation.md` | Evidence | Cost/margin evidence summary | Agent AF / Integrator | ✅ |
| `task-14-alert-happy.log` | Log | Alert generation | Agent AG / Integrator | ✅ |
| `task-14-alert-continuation.md` | Evidence | Alert evidence summary | Agent AG / Integrator | ✅ |

---

## Evidence Artifact Types

| Type | Extension | Description |
|------|-----------|-------------|
| **Log** | `.log` | Test run output, command output, execution evidence |
| **Spec** | `.md` | Design specification, contract, standard |
| **Matrix** | `.md` | Cross-cutting traceability mapping |
| **Trace** | `.md` | TDD cycle documentation |
| **Checklist** | `.md` | Verification checklist |
| **Audit** | `.md` | Security/compliance audit findings |

---

## Evidence by BRD Section

| BRD Section | Artifact(s) | Status |
|------------|-------------|--------|
| BRD 4 (Roles) | `rbac-matrix-v1.md`, `task-02-auth-happy.log` | 🟡 |
| BRD 5 (Farm/Plot/Bed) | `task-01-*.log`, `data-dictionary-baseline.md` | ✅ |
| BRD 6-7 (Crop/Variety) | `task-03-master-happy.log`, `data-dictionary-baseline.md` | ✅ |
| BRD 8 (Norms) | `data-dictionary-baseline.md`, `final-f2c-db-architecture.md` | ✅ |
| BRD 11-12 (Planning) | `task-04-*.log`, `task-04-planning-formula-spec.md` | ✅ |
| BRD 19 (QR) | `final-f3c-security-privacy.md` (design) | ⚠️ |
| BRD 28 (DB) | `final-f2c-db-architecture.md`, `seed-strategy.md` | ✅ |

---

## Evidence by Plan Task

| Task | Artifacts | Status |
|------|-----------|--------|
| T1 (Data Foundation) | 6 files | ✅ |
| T2 (Auth + RBAC) | 4 files | 🟡 |
| T3 (Master Data) | 1 file | ✅ |
| T4 (Planning) | 7 files | ✅ |
| T5 (Planting Batch Lifecycle) | 4 files | ✅ |
| T6 (Work Task & Farming Log) | 7 files | 🟡 |
| T7 (Incident + Chemical Isolation) | 2 files | ✅ |
| T8 (Pre-harvest Inspection) | 2 files | ✅ |
| T9 (Harvest Lots) | 2 files | ✅ |
| T10-T14 (MVP-1/2 downstream) | 12 files | ✅ |

---

## Coverage Assessment

### HIGH Coverage (Evidence Complete)
- Database architecture and migrations (T1)
- Auth basic functionality (T2)
- Master data CRUD (T3)
- Planning calculation and formula (T4)

### MEDIUM Coverage (Evidence Partial)
- RBAC permission matrix (T2) - Current approval endpoints verified; future approval endpoints still need explicit checks
- API error contract (T2) - Spec complete, enforcement not tested

### LOW Coverage (Evidence Gaps)
- T6 device-level mobile smoke execution - readiness checklist exists; actual device/emulator evidence remains pending
- Staging PostgreSQL execution - local PostgreSQL evidence exists; owner credentialed staging run remains pending
- Manual device-level evidence for mobile/photo flow remains pending

### NO Coverage
- Original failing console output for reconstructed RED logs where work was completed before evidence capture

---

## Missing Evidence Gaps

### CRITICAL (Block Release)

1. **Staging PostgreSQL evidence** - Local PostgreSQL evidence exists; staging credentialed run remains owner action
2. **Future RBAC controller enforcement** - Implemented approval endpoints now enforce controller RBAC; future approval actions must keep this pattern
3. **QR privacy whitelist test** - Required before QR public ships

### HIGH (Should Close Before MVP-1)

4. **Device-level mobile smoke** - API/service tests and readiness checklist exist; Expo device/emulator flow still pending
5. **PostgreSQL staging migration** - Local PostgreSQL evidence exists; staging credentialed run still requires owner action
6. **Planning TDD RED log** - `task-04-red.log` reconstructed from test names and TDD trace (2026-05-13). Original PHPUnit failure output not recoverable without code revert.

### MEDIUM (MVP Hardening)

8. **Token expiry/revoke tests** - Not tested
9. **API error contract enforcement** - Inconsistent across controllers
10. **Negative seed execution** - Strategy documented, not run
11. **Canonical seed execution** - Strategy documented, partially run

---

## Evidence Storage Conventions

### File Naming
```
{task}-{phase}-{description}.{ext}

Examples:
- task-04-green.log        (TDD GREEN phase)
- task-04-planning-formula-spec.md  (T4 spec)
- final-f1b-qa-traceability.md      (QA matrix)
```

### Required Front Matter
```markdown
**Generated:** YYYY-MM-DD
**Agent:** [Agent Letter] - [Role]
**Project:** /path/to/project
**Status:** DRAFT|IN PROGRESS|COMPLETE|BLOCKED
```

### Evidence Path Convention
All evidence must be stored in `.sisyphus/evidence/` with relative paths from project root.

---

## Verification Commands

### List All Evidence
```bash
ls -la /Users/macbook/Herd/ariops/.sisyphus/evidence/
```

### Count Evidence Files by Type
```bash
ls .sisyphus/evidence/*.log | wc -l  # Logs
ls .sisyphus/evidence/*.md | wc -l     # Docs
```

### Check Evidence Completeness
```bash
# Check for missing TDD traces
ls task-*-red.log | wc -l
ls task-*-green.log | wc -l

# Check for MVP-1 evidence
ls task-0[5-9]-*.log 2>/dev/null | wc -l
ls task-1[0-4]-*.log 2>/dev/null | wc -l
```

---

## Next Actions

1. **Create missing MVP-1 evidence files** as each task is implemented
2. **Verify PostgreSQL staging migration** before MVP-1 entry
3. **Add farm scope isolation tests** before MVP-1 entry
4. **Update this index** when new evidence is created

---

**End of Evidence Index**
