# Final F3b UX Friction QA

Date: 2026-05-13

## Reviewed Surfaces

- React operations app route shell and workflow pages.
- Expo field app login, today/task list, work-log modal, pending sync list.
- Public QR traceability page/API evidence from `PublicTraceabilityApiTest`.

## Findings

- Planning workflow is API-backed and available in operations surface; core validation errors return field-level detail.
- Field work log flow avoids re-entering known context: the mobile app submits only task id, notes/photos/timestamps, while backend fills farm, batch, allocation, plot, bed, and reporter.
- Work log modal supports direct submit and offline queue fallback; retry is visible through pending log cards.
- Public QR surface remains read-only and does not require login.

## Friction/Risk Items

- No live Playwright click-count recording was captured in this final pass; confidence comes from code walkthrough plus build/test smoke.
- Offline conflict messaging is still basic for non-idempotency conflicts such as task status changed to cancelled before retry; backend returns domain error, mobile stores failure text for retry UI.

Status: PASS with minor UX follow-up.
