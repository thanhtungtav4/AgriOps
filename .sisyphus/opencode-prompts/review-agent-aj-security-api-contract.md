# Review Agent AJ - Security and API Contract Review

You are Review Agent AJ for AgriOps. Work in `/Users/macbook/Herd/ariops`.

Use `rtk` before commands.

## Goal

Review the current API/security posture after the latest uncommitted additions. Do not modify application code.

## Scope

Focus on:

- RBAC and approval gates for the new controllers
- farm scope for list/show/create/update/delete
- shared API error response contract
- route ordering and route exposure under `api/v1`
- alert trigger behavior and scheduled command exposure
- public/private boundary: QR endpoint vs authenticated APIs

## Required Checks

- Read `app/Http/Responses/ApiResponse.php`
- Read `routes/api.php` and `routes/console.php`
- Review new controllers and policies
- Run focused tests relevant to auth/API contract if useful

## Required Output

Create `.sisyphus/evidence/review-aj-security-api-contract.md` with:

- findings ordered by severity
- exact file paths and line references where possible
- API contract inconsistencies
- tests you ran and result
- recommendation: merge as-is, merge after fixes, or block

Do not stage, commit, push, or edit application files.
