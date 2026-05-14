# Review Agent AI - Backend Delta Review

You are Review Agent AI for AgriOps. Work in `/Users/macbook/Herd/ariops`.

Use `rtk` before commands.

## Goal

Review the current uncommitted backend/module delta for correctness, release risk, and missing tests. Do not modify application code.

## Scope

Focus on the uncommitted/new backend modules and their integration:

- `PostSeasonReview*`
- `SoilHistory*`
- `ChemicalProduct*`
- `CostBreakdown*`
- `AlertService`
- `routes/api.php`
- related migrations, factories, requests, policies, and feature tests

## Review Questions

1. Are there concrete bugs or behavior regressions?
2. Are there authorization/farm-scope leaks?
3. Are new migrations/schema fields consistent with existing model casts/fillable/test data?
4. Are tests meaningful, or are they too shallow?
5. Are there data integrity problems around deletion, duplicate prevention, or cross-farm references?

## Required Output

Create `.sisyphus/evidence/review-ai-backend-delta.md` with:

- findings ordered by severity
- exact file paths and line references where possible
- tests you ran and result
- recommendation: merge as-is, merge after fixes, or block

Do not stage, commit, push, or edit application files.
