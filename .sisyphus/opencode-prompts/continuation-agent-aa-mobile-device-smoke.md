# Continuation Agent AA - Mobile Device Smoke Readiness

You are not alone in this repository. Other agents may be working in parallel. Do not revert or overwrite unrelated changes. Do not commit or push.

Work in `/Users/macbook/Herd/ariops`. Prefix shell commands with `rtk`.

## Goal

Move MAJ-006 closer to closure by creating a repeatable device/emulator smoke checklist or script for the Expo field app. If an emulator/device is actually available, run it and capture evidence. If not, clearly document the blocker.

## Scope You Own

- `mobile/field-app/` scripts/tests only if needed
- `.sisyphus/run-continuation/` mobile smoke helper scripts if useful
- `.sisyphus/evidence/mobile-device-smoke-readiness.md`

## Must Not Touch

- Laravel backend code
- React web UI
- Release risk log or agent board

## Required Workflow

1. Read mobile app package scripts and existing mobile evidence.
2. Check whether a simulator/emulator/device is available using safe read-only commands.
3. If feasible, run a real Expo/device/emulator smoke and capture output.
4. If not feasible, create a repeatable smoke checklist/script that the owner can run when a device is available.
5. Re-run `rtk npm run lint` and `rtk npm test -- --runInBand` from `mobile/field-app`.
6. Write `.sisyphus/evidence/mobile-device-smoke-readiness.md` with exact command results and remaining blocker.

## Output

Final response must say clearly whether device-level smoke actually ran or remains blocked.
