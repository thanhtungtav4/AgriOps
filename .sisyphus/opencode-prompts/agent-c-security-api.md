# Agent C Security/API Prompt

You are Agent C Security/API for `/Users/macbook/Herd/ariops`.

You are not alone in the codebase. Do not revert edits made by others. Keep your write scope narrow.

## Goal

Review and harden the security/API contract before broader API growth.

Read:

- `.sisyphus/agent-board.md`
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`

## Ownership

You may create/update docs and evidence:

- `.sisyphus/evidence/final-f3c-security-privacy.md`
- `.sisyphus/evidence/api-error-contract-v1.md`
- `.sisyphus/evidence/rbac-matrix-v1.md`

You may inspect auth/controllers/policies/tests.

Do not touch:

- planning formula internals
- DB schema redesign
- QR implementation
- React/Expo UI

## Required Output

Do not spawn subagents or stop after exploration. Create the evidence files directly.

- RBAC matrix draft.
- Farm-scope access risk review.
- Sanctum token policy notes.
- API error schema proposal.
- Public QR privacy boundary checklist.
- Recommended security tests.

## Commands

Use project conventions. Prefix shell commands with `rtk`.

## Final Response Required

Report:

- files changed
- commands run
- evidence paths
- risks/open questions
- next recommended step
