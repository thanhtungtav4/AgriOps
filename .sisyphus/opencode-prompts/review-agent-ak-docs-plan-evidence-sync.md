# Review Agent AK - Docs, Plan, Evidence Sync Review

You are Review Agent AK for AgriOps. Work in `/Users/macbook/Herd/ariops`.

Use `rtk` before commands.

## Goal

Review whether `.docs`, `.sisyphus` plan/evidence/risk files, and `TODO_BRAND.md` match the actual current code and tests. Do not modify application code.

## Scope

Focus on:

- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`
- `.sisyphus/evidence/evidence-index.md`
- `.sisyphus/evidence/release-risk-log.md`
- `.sisyphus/evidence/final-summary.md`
- `.sisyphus/evidence/final-f5-release-readiness.md`
- `.sisyphus/agent-board.md`
- `TODO_BRAND.md`
- current test counts from backend, React, and Expo

## Required Checks

- Compare claimed test counts with actual test runs if feasible.
- Identify stale TODOs caused by newly implemented modules.
- Identify contradictory risk/evidence status.
- Identify missing evidence files for new modules.

## Required Output

Create `.sisyphus/evidence/review-ak-docs-plan-evidence-sync.md` with:

- stale or contradictory documentation findings
- recommended doc/evidence updates
- tests/checks you ran and result
- recommendation: doc-only cleanup needed, or release docs acceptable

Do not stage, commit, push, or edit application files.
