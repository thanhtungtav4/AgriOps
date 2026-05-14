# Review Agent AK - Docs, Plan, Evidence Sync Review

**Date:** 2026-05-14
**Agent:** AK - Docs/Plan/Evidence Sync Review
**Project:** /Users/macbook/Herd/ariops

---

## 1. Test Count Discrepancies (STALE)

| Document | Claimed Tests | Claimed Assertions | Actual | Status |
|----------|--------------|-------------------|--------|--------|
| `final-summary.md` | 281 | 963 | **349 / 1160** | STALE |
| `final-f5-release-readiness.md` | 281 | 963 | **349 / 1160** | STALE |
| `agent-board.md` (line 118) | 302 | 1071 | **349 / 1160** | STALE |

All three documents report outdated test counts. The actual suite has grown by +68 tests and +197 assertions since the last documentation update.

**Command run:** `rtk php artisan test --no-coverage` → 349 tests, 1160 assertions, 0 failures, ~4.9s.

---

## 2. Migration Count Discrepancy (STALE)

| Document | Claimed | Actual | Status |
|----------|---------|--------|--------|
| `final-summary.md` (line 52) | 40 migrations | **44 migrations** | STALE |

Four additional migrations have been added since the summary was written:
- `2026_05_13_171530_create_post_season_reviews_table` (batch 2)
- `2026_05_13_173902_create_soil_histories_table` (batch 3)
- `2026_05_13_232523_create_chemical_products_table` (batch 4)
- `2026_05_13_233349_create_cost_breakdowns_table` (batch 5)

---

## 3. TODO_BRAND.md - Stale Items (IMPLEMENTED BUT STILL LISTED AS MISSING)

### HIGH PRIORITY items that are now implemented:

| TODO Item | TODO Status | Actual State | Evidence |
|-----------|------------|--------------|----------|
| §2 Post-season Review (BRD 26.6) | "chưa có" table | Migration `create_post_season_reviews_table` **RAN** (batch 2) | Migration exists and applied |
| §3 Soil History (BRD 5.4) | "chưa có" table | Migration `create_soil_histories_table` **RAN** (batch 3) | Migration exists and applied |
| §4 Chemical Product Catalog (BRD 8.3) | "chưa có" table | Migration `create_chemical_products_table` **RAN** (batch 4) | Migration exists and applied |
| §7 Cost Records (BRD 25) | Partially listed | Migration `create_cost_breakdowns_table` **RAN** (batch 5) | New table beyond existing `cost_records` |

### MEDIUM PRIORITY items that may be partially implemented:

| TODO Item | TODO Status | Notes |
|-----------|------------|-------|
| §1 Processing (BRD 17) | "flow chưa hoàn chỉnh" | `processing_records` migration exists (batch 1). API completeness needs verification. |
| §5 Alerts tự động (BRD 24) | Needs AlertService | `alerts` table exists (batch 1). Basic model present per TODO_BRAND "ĐÃ CÓ" section. |

**Recommendation:** TODO_BRAND.md §2, §3, §4 should be moved from HIGH PRIORITY to "ĐÃ CÓ" or updated to reflect that tables exist but API/flow may need completion.

---

## 4. Evidence Index - Missing File Entries

The `evidence-index.md` does not list the following files that exist in `.sisyphus/evidence/`:

### T15 (React Web) evidence files not indexed:
- `task-15-build-evidence.log`
- `task-15-build.log`
- `task-15-green.log`
- `task-15-lint.log`
- `task-15-refactor.log`
- `task-15-routes.log`
- `task-15-react-admin-qwen-ux-review.md`
- `task-15-react-api-contract-review.md`
- `task-15-react-auth-api-resilience.md`
- `task-15-react-browser-qa.md`
- `task-15-react-dashboard-ux.md`
- `task-15-react-operations-routes-tests.md`

### T16 (Expo App) evidence files not indexed:
- `task-16-expo-retry.log`
- `task-16-expo-sync-happy.log`
- `task-16-green.log`
- `task-16-mobile-build.log`
- `task-16-refactor.log`

### T17 (TDD Quality Net) evidence files not indexed:
- `task-17-green.log`
- `task-17-known-rule.log`
- `task-17-red.log`
- `task-17-refactor.log`

### T18 (UAT Readiness) evidence files not indexed:
- `task-18-e2e-chain.log`
- `task-18-e2e-chain.md`
- `task-18-fail-paths.log`
- `task-18-refactor.log`
- `task-18-uat-readiness.md`

### Continuation/other files not indexed:
- `api-error-contract-continuation.md`
- `api-validation-contract-continuation.md`
- `evidence-index-continuation.md`
- `task-04-red-cleanup-continuation.md`
- `packing-invalid-source-continuation.md`
- `postgres-evidence-continuation.md`
- `rbac-approval-audit-continuation.md`
- `task-12-delivery-return-continuation.md`
- `task-13-cost-margin-continuation.md`
- `task-14-alert-continuation.md`
- `hardening-a-audit-trail.md`
- `hardening-b-offline-conflict.md`
- `hardening-c-uat-risk-cleanup.md`
- `hardening-d-ops-runbook.md`
- `all-green-2026-05-13.log`
- `final-f6-ops-performance.md`
- `login-page-desktop.png`
- `login-page-mobile.png`
- `task-10-packing-api-blocked.md`
- `task-10-traceability-graph-blocked.md`

**Total evidence files on disk:** 131
**Files indexed in evidence-index.md:** ~60 (estimated from tables)
**Gap:** ~71 files not referenced in the index

---

## 5. Release Risk Log - Stale Status

| Risk ID | Documented Status | Assessment |
|---------|------------------|------------|
| MAJ-002 "API error contract inconsistent" | PARTIAL | Additional coverage via `api-error-contract-continuation.md` and `api-validation-contract-continuation.md`; should be re-evaluated |
| MIN-001 "CHECK constraints not added" | KNOWN | Still valid - no new CHECK constraint migrations observed |
| MIN-002 "No partial unique indexes" | KNOWN | Still valid |
| MIN-003 "Decimal precision review" | KNOWN | Still valid |
| MIN-004 "Composite indexes missing" | KNOWN | Still valid |
| MIN-005 "Negative seed not executed" | KNOWN | Still valid |

**Risk trend line** (line 154-158): States "16 tracked risks, 30 resolved" as of 2026-05-13. This should be updated to reflect current state with 44 migrations, 349 tests, and new tables added.

---

## 6. Agent Board - Stale Verification Numbers

`agent-board.md` line 118 states: "Full suite passes cleanly: 302 tests, 1071 assertions."

**Actual:** 349 tests, 1160 assertions.

The "Next likely work" section (lines 122-124) lists:
- Device-level mobile smoke - still pending (valid)
- Staging PostgreSQL migration evidence - still pending (valid)
- API error-contract cleanup - partially addressed (continuation files exist)

These items remain accurate as outstanding work.

---

## 7. Plan File (laravel-filament-react-expo-auth-mvp.md)

The plan file is comprehensive and structurally sound. Key observations:

- All T1-T18 tasks are marked `[x]` (complete) in the TODOs section.
- Implementation status notes for T5-T11 are present and reasonably current.
- The plan does not reference the newer migrations (post_season_reviews, soil_histories, chemical_products, cost_breakdowns).
- The plan's "Definition of Done" section (lines 89-93) is satisfied: migrations pass, tests pass, MVP vertical slice has evidence.

---

## 8. Contradictory Findings

| Finding | Document A | Document B | Reality |
|---------|-----------|-----------|---------|
| Test counts | final-summary: 281 | agent-board: 302 | **349** |
| Post-season review table | TODO_BRAND: "chưa có" | Migration: exists | **Table exists** |
| Soil history table | TODO_BRAND: "chưa có" | Migration: exists | **Table exists** |
| Chemical products table | TODO_BRAND: "chưa có" | Migration: exists | **Table exists** |
| Evidence file count | evidence-index: ~60 indexed | Disk: 131 files | **131 files on disk** |

---

## 9. Tests/Checks Ran

| Check | Command | Result |
|-------|---------|--------|
| Laravel test suite | `rtk php artisan test --no-coverage` | **349 passed, 1160 assertions, 0 failures** (~4.9s) |
| Migration status | `rtk php artisan migrate:status` | **44 migrations, all Ran** (batches 1-5) |
| API route count | `rtk php artisan route:list --path=api/v1` | **94 lines of output** (60+ endpoints confirmed) |
| Evidence file count | `ls .sisyphus/evidence/ \| wc -l` | **131 files** |

---

## 10. Recommended Doc/Evidence Updates

### HIGH Priority (factual inaccuracies):
1. **final-summary.md**: Update test count 281→349, assertions 963→1160, migrations 40→44
2. **final-f5-release-readiness.md**: Update test count 281→349, assertions 963→1160
3. **agent-board.md**: Update test count 302→349, assertions 1071→1160
4. **TODO_BRAND.md**: Move §2 (Post-season Review), §3 (Soil History), §4 (Chemical Product Catalog) from HIGH PRIORITY to "ĐÃ CÓ" section, or update to note that tables exist but API/flow needs completion
5. **evidence-index.md**: Add entries for T15-T18 evidence files (19+ files missing from index), plus continuation files and hardening docs

### MEDIUM Priority (stale but not incorrect):
6. **release-risk-log.md**: Re-evaluate MAJ-002 status given additional error contract coverage files
7. **release-risk-log.md**: Update risk trend summary with current migration/test counts
8. **evidence-index.md**: Last updated field says "2026-05-13" - update to current date

### LOW Priority (housekeeping):
9. **agent-board.md**: Consider adding Agent entries for post-T18 work (new migrations for post_season_reviews, soil_histories, chemical_products, cost_breakdowns)
10. **evidence-index.md**: Consider adding a "T15-T18 Evidence Artifacts" section for React/Expo/Hardening/UAT files

---

## 11. Recommendation

**Doc-only cleanup needed.** No application code changes required.

The codebase is in good shape (349 tests all passing, 44 migrations all applied, no failures). The documentation artifacts have simply not been updated to reflect the growth in tests, migrations, and evidence files since the last update on 2026-05-13.

The most impactful single update would be to fix the test/migration counts across `final-summary.md`, `final-f5-release-readiness.md`, and `agent-board.md`, followed by updating `TODO_BRAND.md` to reflect that post_season_reviews, soil_histories, and chemical_products tables now exist.

---

**End of Review Agent AK Report**
