# Agent QA2 - Post-Planning Flow Review

You are Agent QA2 for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Required Reading

- `.sisyphus/AGENT_MISSION_BRIEF.md`
- `.sisyphus/evidence/site-gap-vs-brd-2026-05-14.md`
- `.docs/yeu_cau_crm_nong_nghiep_v1.md`

## Task

Review the post-planning workflow coverage:

Calculation → Production Plan → Planting Batch → Work Tasks → Inspection → Harvest → Packing/QR → Delivery/Returns.

Read-only except for writing evidence.

## Ownership

You may write only:

- `.sisyphus/evidence/agent-qa2-post-planning-flow-review.md`

## Review Checklist

1. Inspect API routes and controllers for the flow.
2. Inspect operations React pages/components.
3. Identify where the user can continue the flow and where it dead-ends.
4. Identify API/UI contract mismatches.
5. Identify pages that are read-only when the workflow needs actions.
6. Recommend the next 3 smallest implementation tasks.

## Verification Commands

Run:

- `rtk php artisan route:list`
- `rtk npm run build`
- `rtk php artisan test`

## Evidence

Write findings ordered by severity with file/route references.

