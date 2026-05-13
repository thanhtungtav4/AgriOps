# Agent V - OpenCode Go/ZEN API Contract Review

You are Agent V for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Review whether the React operations/admin surface uses API v1 consistently and is ready for Expo Task 16 to reuse the same contracts.

## Ownership

You own only:

- `.sisyphus/evidence/task-15-react-api-contract-review.md`

Do not edit application code.

## Review Focus

- API paths used by React operations.
- Auth token persistence and Bearer header behavior.
- 401/error behavior.
- Response shape assumptions (`data.data`, arrays, nested relationship names).
- Any mismatch that Expo Task 16 should not copy.

## Output

Write a concise report with:

- contract assumptions found
- risks for Expo reuse
- recommended API client conventions
- whether backend changes are needed before Task 16

