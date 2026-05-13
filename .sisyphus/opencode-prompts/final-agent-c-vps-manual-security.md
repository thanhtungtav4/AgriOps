You are Final Agent C running on VPS for the Final Verification Wave.

Work in /Users/macbook/Herd/ariops. Follow AGENTS instructions: every shell command must be prefixed with `rtk`.

Mode: review/evidence only. Do not edit application code. Do not update the plan.

Ownership:
- `.sisyphus/evidence/final-f3-qa/`
- `.sisyphus/evidence/final-f3b-ux-friction.md`
- `.sisyphus/evidence/final-f3c-security-privacy.md`

Scope:
- F3 Real Manual QA + API evidence:
  - Reuse Task 18 scripts where appropriate.
  - Capture happy-path and negative-path evidence logs under `.sisyphus/evidence/final-f3-qa/`.
- F3b UX Friction QA:
  - Review React operations app and Expo field app implementation.
  - Estimate click/tap budgets for planning, work log, harvest, packing, public QR.
  - Record friction and remediation without changing code.
- F3c Security & Privacy Gate:
  - Run existing farm scope, auth policy, rate limit, public QR privacy, and upload validation tests.
  - Verify public traceability/QR code avoids sensitive fields.
  - Report any cross-farm leak, role bypass, or public forbidden field.

Verification:
- Evidence should include commands run, test names, results, and remaining risk.
