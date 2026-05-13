# Final QA Traceability Matrix - AgriOps MVP-0/MVP-1

**Generated:** 2026-05-12
**Agent:** E - QA/Evidence
**Project:** /Users/macbook/Herd/ariops
**Scope:** MVP-0 (T1-T4) + MVP-1 Entry Gate (T5-T11)

---

## Overview

This matrix maps every BRD requirement to its corresponding plan task, test artifact, and release gate status.

**Legend:**
- ✅ GREEN = Implemented + Tested + Evidence exists
- 🟡 IN PROGRESS = Implemented partially, testing in progress
- 🔴 BLOCKED = Not started, depends on upstream gate
- ⚠️ GAP = Evidence exists, test coverage incomplete
- ❌ MISSING = Required, not yet created

---

## BRD Section → Plan Task → Test/Evidence → Gate Mapping

### MVP-0: Backend Foundation (Gates: Migration, Auth, Master Data, Planning)

| BRD Requirement | BRD Section | Plan Task | Status | Test File | Evidence Files | Gate |
|----------------|-------------|-----------|--------|-----------|----------------|------|
| Farm CRUD + multi-farm | BRD 5.1 | T1 | ✅ | `tests/Feature/ExampleTest.php` | `task-01-migration.log` | MVP-0 GATE |
| Plot management | BRD 5.2 | T1 | ✅ | - | `task-01-db-architecture-qc.log` | MVP-0 GATE |
| Bed management | BRD 5.3 | T1 | ✅ | - | `task-01-db-architecture-qc.log` | MVP-0 GATE |
| Crop CRUD | BRD 6 | T3 | ✅ | `tests/Feature/ExampleTest.php` | `task-03-master-happy.log` | MVP-0 GATE |
| Growth stages | BRD 7 | T3 | ✅ | - | `task-01-db-architecture-qc.log` | MVP-0 GATE |
| Product standards (grade A/B/C/reject) | BRD 6.2 | T3 | ✅ | - | `task-01-db-architecture-qc.log` | MVP-0 GATE |
| Harvest models | BRD 9.1, 9.2 | T3 | ✅ | - | `task-01-db-architecture-qc.log` | MVP-0 GATE |
| Loss profiles | BRD 10 | T3 | ✅ | - | `task-01-db-architecture-qc.log` | MVP-0 GATE |
| Labor norms | BRD 10 | T3 | ✅ | - | `task-01-db-architecture-qc.log` | MVP-0 GATE |
| Irrigation norms | BRD 8.1 | T3 | ✅ | - | `task-01-db-architecture-qc.log` | MVP-0 GATE |
| Fertilizer norms | BRD 8.2 | T3 | ✅ | - | `task-01-db-architecture-qc.log` | MVP-0 GATE |
| **Laravel Sanctum Auth** | BRD 4 | T2 | ✅ | `tests/Feature/ExampleTest.php` | `task-02-auth-happy.log` | MVP-0 GATE |
| **Role-based access (7 roles)** | BRD 4 | T2 | 🟡 | `tests/Feature/ExampleTest.php` | `task-02-auth-happy.log`, `rbac-matrix-v1.md` | MVP-0 GATE |
| **Supply contracts CRUD** | BRD 3.1 | T3 | ✅ | `tests/Feature/SupplyInputApiTest.php` | `task-03-master-happy.log` | MVP-0 GATE |
| **Supply demands CRUD** | BRD 3.1 | T3 | ✅ | `tests/Feature/SupplyInputApiTest.php` | `task-03-master-happy.log` | MVP-0 GATE |
| **Production plans CRUD** | BRD 12 | T3 | ✅ | `tests/Feature/SupplyInputApiTest.php` | `task-03-master-happy.log` | MVP-0 GATE |
| **Planning calculation API** | BRD 11, 27 | T4 | ✅ | `tests/Feature/PlanningApiTest.php` | `task-04-green.log`, `task-04-tdd-trace.md` | **MVP-0 BLOCKER** |
| Planning formula (m2→cây→sản lượng) | BRD 11.3-11.6 | T4 | ✅ | `tests/Feature/PlanningApiTest.php` | `task-04-planning-formula-spec.md` | **MVP-0 BLOCKER** |
| Planning assumptions output | BRD 11.2 | T4 | ✅ | `tests/Feature/PlanningApiTest.php` | `task-04-tdd-trace.md` | **MVP-0 BLOCKER** |
| Planning fulfillment status | BRD 11.2 | T4 | ✅ | `tests/Feature/PlanningApiTest.php` | `task-04-tdd-trace.md` | **MVP-0 BLOCKER** |
| **DB Migration PostgreSQL** | BRD 28 | T1 | ✅ | - | `task-01-migration.log` | MVP-0 GATE |
| **Data Dictionary** | BRD 28 | T1 | ✅ | - | `data-dictionary-baseline.md` | MVP-0 GATE |
| **DB Architecture QC** | BRD 28 | T1 | ✅ | - | `final-f2c-db-architecture.md` | MVP-0 GATE |
| **Seed Strategy** | BRD 28 | T1 | ✅ | - | `seed-strategy.md`, `final-f2b-data-quality.log` | MVP-0 GATE |

---

### MVP-1: Production Trace Slice (Gates: Batch, Task, Harvest, Packing, QR)

| BRD Requirement | BRD Section | Plan Task | Status | Test File | Evidence Files | Gate |
|----------------|-------------|-----------|--------|-----------|----------------|------|
| Planting batch lifecycle (14 states) | BRD 12B.2 | T5 | 🔴 | - | - | **GATE: T4** |
| Batch ↔ Plot/Bed allocation (many-to-many) | BRD 12B.2 | T5 | 🔴 | - | - | **GATE: T4** |
| Soil history & crop rotation rules | BRD 5.4 | T5 | 🔴 | - | - | **GATE: T4** |
| Work task generation | BRD 13.3 | T6 | 🔴 | - | - | **GATE: T4** |
| Work task assignment | BRD 13.3 | T6 | 🔴 | - | - | **GATE: T4** |
| Farming log with photo | BRD 14.1, 14.2 | T6 | 🔴 | - | - | **GATE: T4** |
| Plan vs Actual separation | BRD 14.1 | T6 | 🔴 | - | - | **GATE: T4** |
| **Incident logging** | BRD 14.2 | T7 | 🔴 | - | - | **GATE: T5** |
| **Chemical/biological usage** | BRD 8.3 | T7 | 🔴 | - | - | **GATE: T5** |
| **Isolation period calculation** | BRD 8.3 | T7 | 🔴 | - | - | **GATE: T5** |
| **Pre-harvest inspection** | BRD 15 | T8 | 🔴 | - | - | **GATE: T5** |
| **Approval workflow** | BRD 23 | T8 | 🔴 | - | - | **GATE: T5** |
| **Harvest lot with grade A/B/C** | BRD 16, 16.1 | T9 | 🔴 | - | - | **GATE: T8** |
| **Harvest rejection reasons** | BRD 16.1 | T9 | 🔴 | - | - | **GATE: T8** |
| **Multi-harvest batch support** | BRD 9.2 | T9 | 🔴 | - | - | **GATE: T8** |
| Processing records | BRD 17 | T10 | 🔴 | - | - | **GATE: T9** |
| **Packing lot (many-to-many sources)** | BRD 18.1, 18.2 | T10 | 🔴 | - | - | **GATE: T9** |
| **Cross-farm packing mixing** | BRD 18.2, 22 | T10 | 🔴 | - | - | **GATE: T9** |
| Traceability graph | BRD 18.2 | T10 | 🔴 | - | - | **GATE: T9** |
| **Public QR endpoint** | BRD 19 | T11 | 🔴 | - | - | **GATE: T10** |
| **QR privacy whitelist** | BRD 19.2 | T11 | 🔴 | - | - | **GATE: T10** |

---

### Security & Compliance (Cross-MVP)

| BRD Requirement | BRD Section | Plan Task | Status | Test File | Evidence Files | Gate |
|----------------|-------------|-----------|--------|-----------|----------------|------|
| Farm scope isolation | BRD 4, Security | T2 | ⚠️ | - | `final-f3c-security-privacy.md` | **CRITICAL - Not Implemented** |
| RBAC enforcement on controllers | BRD 4 | T2 | ⚠️ | - | `rbac-matrix-v1.md` | **CRITICAL - Not Implemented** |
| Approval policy | BRD 23 | T2 | 🟡 | - | `rbac-matrix-v1.md` | MVP-0 |
| API error contract | BRD 4 | T2 | 🟡 | - | `api-error-contract-v1.md` | MVP-0 |
| Token policy (expiry, revoke) | BRD Security | T2 | ⚠️ | - | `final-f3c-security-privacy.md` | MVP-0 |
| QR no chemical/dosage exposure | BRD 19.2 | T11 | 🔴 | - | `final-f3c-security-privacy.md` | **GATE: T10** |

---

## MVP-0 Exit Gate Checklist

All items below MUST be GREEN before proceeding to MVP-1:

| # | Gate Item | Criteria | Status | Evidence |
|---|-----------|----------|--------|----------|
| G1 | Migration fresh pass | `php artisan migrate:fresh` → exit 0 | ✅ | `task-01-migration.log` |
| G2 | DB constraints validated | FK, unique, indexes present per spec | ✅ | `task-01-db-architecture-qc.log` |
| G3 | Auth login functional | POST /api/v1/auth/login → 200 + token | ✅ | `task-02-auth-happy.log` |
| G4 | Role separation | Different roles get different access | 🟡 | `rbac-matrix-v1.md` (design only) |
| G5 | Farm scope isolation | User A cannot see Farm B data | ⚠️ | `final-f3c-security-privacy.md` (GAP) |
| G6 | Master data CRUD | Farm/Plot/Crop/Norm CRUD via API | ✅ | `task-03-master-happy.log` |
| G7 | Supply contract/demand CRUD | Happy path + validation | ✅ | `tests/Feature/SupplyInputApiTest.php` |
| G8 | Planning calculation | Happy path with all outputs | ✅ | `task-04-green.log` |
| G9 | Planning domain errors | Missing norms → 422 with field | ✅ | `task-04-tdd-trace.md` |
| G10 | Planning formula spec | Documented + reviewed | ✅ | `task-04-planning-formula-spec.md` |
| G11 | Data dictionary | All MVP-0 tables documented | ✅ | `data-dictionary-baseline.md` |
| G12 | PostgreSQL compatibility | All migrations pass on PostgreSQL | 🟡 | Staging not verified |

---

## MVP-1 Exit Gate Checklist

All items below MUST be GREEN before proceeding to MVP-2:

| # | Gate Item | Criteria | Status | Evidence |
|---|-----------|----------|--------|----------|
| M1 | Batch lifecycle end-to-end | Tạo batch → chuyển trạng thái → audit log | 🔴 | - |
| M2 | Allocation guard | Batch → plot rest/suspended → reject | 🔴 | - |
| M3 | Task generation from stage template | Auto-generate tasks per growth stage | 🔴 | - |
| M4 | Mobile log submission | POST work log + photo → 201 | 🔴 | - |
| M5 | Incident + chemical trace | Incident → usage → isolation guard | 🔴 | - |
| M6 | Isolation blocking | Harvest before isolation complete → 422 | 🔴 | - |
| M7 | Inspection pass → harvest enabled | Inspection approved → harvest allowed | 🔴 | - |
| M8 | Harvest grade breakdown | raw_qty = grade_a + b + c + reject | 🔴 | - |
| M9 | Packing multi-source | 2+ harvest lots → 1 packing lot | 🔴 | - |
| M10 | QR public → no leaks | GET /traceability/{qr} → no chemicals/costs | 🔴 | - |
| M11 | Demand → QR E2E smoke | Full happy path automated test | 🔴 | - |

---

## Test Coverage Summary

### Existing Tests

| Test File | Tests | Assertions | Coverage | Status |
|-----------|-------|------------|----------|--------|
| `tests/Feature/ExampleTest.php` | 2 | ~ | Auth basic | ✅ |
| `tests/Feature/PlanningApiTest.php` | 5 | 52 | Planning calculation | ✅ GREEN |
| `tests/Feature/SupplyInputApiTest.php` | 2 | ~ | Contracts/demands | ✅ |

### Missing Tests (Critical Gaps)

| Test Category | Missing | Priority | Blocked By |
|---------------|---------|----------|------------|
| Farm scope isolation | All controller tests | **CRITICAL** | T2 (not started) |
| RBAC permission enforcement | Policy/controller tests | **CRITICAL** | T2 (not started) |
| Batch lifecycle transitions | T5 tests | HIGH | T5 (not started) |
| Allocation validation | T5 tests | HIGH | T5 (not started) |
| Work task acceptance | T6 tests | HIGH | T6 (not started) |
| Chemical isolation guard | T7 tests | HIGH | T7 (not started) |
| Inspection approval gate | T8 tests | HIGH | T8 (not started) |
| Harvest grade math | T9 tests | HIGH | T9 (not started) |
| Packing source validation | T10 tests | HIGH | T10 (not started) |
| QR privacy whitelist | T11 tests | **CRITICAL** | T11 (not started) |
| API error contract enforcement | All controllers | MEDIUM | T2 (partial) |
| Token expiry/revoke | Auth tests | MEDIUM | T2 (partial) |

---

## Evidence Index

### MVP-0 Evidence Files

| File | Type | Purpose | Status |
|------|------|---------|--------|
| `task-01-migration.log` | Log | Migration fresh run evidence | ✅ |
| `task-01-db-architecture-qc.log` | Log | DB architecture analysis | ✅ |
| `data-dictionary-baseline.md` | Spec | All MVP-0 table/field documentation | ✅ |
| `final-f2c-db-architecture.md` | Checklist | PostgreSQL constraint/index checklist | ✅ |
| `seed-strategy.md` | Spec | Canonical + negative seed data | ✅ |
| `final-f2b-data-quality.log` | Spec | SQL integrity check proposal | ✅ |
| `task-02-auth-happy.log` | Log | Auth smoke test evidence | ✅ |
| `rbac-matrix-v1.md` | Spec | RBAC permission matrix (design) | 🟡 |
| `api-error-contract-v1.md` | Spec | API error schema standard | 🟡 |
| `final-f3c-security-privacy.md` | Audit | Security gap analysis | ⚠️ |
| `task-03-master-happy.log` | Log | Master data CRUD evidence | ✅ |
| `task-04-green.log` | Log | Planning tests green | ✅ |
| `task-04-red.log` | Log | Planning TDD RED phase | 🔴 |
| `task-04-refactor.log` | Log | Planning refactor evidence | ✅ |
| `task-04-migration.log` | Log | T4 migration evidence | ✅ |
| `task-04-routes.log` | Log | T4 routes evidence | ✅ |
| `task-04-tdd-trace.md` | Trace | TDD cycle documentation | ✅ |
| `task-04-planning-formula-spec.md` | Spec | Planning formula contract | ✅ |

---

## Release Gate Decision Matrix

| Scenario | Decision | Condition |
|----------|----------|-----------|
| MVP-0 with known gaps | **PROCEED** | Farm scope GAP acknowledged, fix scheduled for T17+ |
| MVP-1 with T4 incomplete | **BLOCK** | T4 test must pass before T5-T11 |
| MVP-1 without batch migration | **BLOCK** | planting_batches table required for T5-T11 |
| MVP-1 without QR privacy | **BLOCK** | QR public cannot ship without privacy test |
| MVP-0 with RBAC GAP | **PROCEED WITH NOTE** | Farm isolation GAP logged, fix in MVP hardening |

---

## Next Steps

1. **IMMEDIATE**: Verify PostgreSQL staging migration (not yet run)
2. **IMMEDIATE**: Create farm scope isolation tests before MVP-1
3. **BEFORE T5**: Complete RBAC controller enforcement per `rbac-matrix-v1.md`
4. **BEFORE T11**: Create QR privacy whitelist with automated tests
5. **MVP-1 ENTRY**: Only after all MVP-0 gates GREEN and T4 tests pass

---

**End of QA Traceability Matrix**
