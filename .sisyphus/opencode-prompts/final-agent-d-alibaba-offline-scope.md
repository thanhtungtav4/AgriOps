You are Final Agent D running on Alibaba/Qwen for the Final Verification Wave.

Work in /Users/macbook/Herd/ariops. Follow AGENTS instructions: every shell command must be prefixed with `rtk`.

Mode: review/evidence only. Do not edit application code. Do not update the plan.

Ownership:
- `.sisyphus/evidence/final-f3d-offline-sync.md`
- `.sisyphus/evidence/final-f4-scope-fidelity.md`

Scope:
- F3d Offline Sync Contract Gate:
  - Review Expo offline queue/client_uuid behavior and Laravel WorkTaskLog API.
  - Check whether duplicate work log/photo upload with same idempotency key is prevented server-side.
  - Check conflict behavior when task status changes before retry.
  - Run or cite tests where available; if missing, mark explicit blocker/major gap with implementation path.
- F4 Scope Fidelity Check:
  - Compare deliverables for tasks 1-18 against plan scope.
  - Identify missing committed items and out-of-scope creep.
  - Do not overstate readiness; separate pass, warning, and fail items.

Verification:
- Evidence must be concrete and file-backed.
