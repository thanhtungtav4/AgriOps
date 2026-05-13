# Continuation Agent Y - Mobile Smoke Evidence

You are not alone in this repository. Other agents may be working in parallel. Do not revert or overwrite unrelated changes. Do not commit or push.

Work in `/Users/macbook/Herd/ariops`. Prefix shell commands with `rtk`.

## Goal

Improve evidence for `MAJ-006`: task receive -> log submit -> photo/offline handling for the field/mobile workflow.

## Scope You Own

- Mobile test/evidence files under `mobile/field-app/` only if needed
- `.sisyphus/evidence/mobile-smoke-continuation.md`
- Optional repeatable smoke/check script under `.sisyphus/run-continuation/` if useful

## Must Not Touch

- Laravel API controllers/services/models
- React web UI files
- Release risk log or agent board

## Required Workflow

1. Read the existing mobile field app tests and farming log service code.
2. Run the available mobile quality checks, at minimum:
   - `rtk npm run lint` from `mobile/field-app` if configured
   - `rtk npm test -- --runInBand` from `mobile/field-app` if configured
   - `rtk npm run typecheck` or equivalent if configured
3. If tests are missing for task receive/log/photo/offline conflict behavior, add a focused test in the existing mobile test style.
4. Write `.sisyphus/evidence/mobile-smoke-continuation.md` with:
   - checks run and exact results
   - what this proves
   - what still requires a real device/emulator
5. Do not claim device-level smoke is complete unless you actually ran a device/emulator.

## Output

Final response must list changed files, commands run, and remaining device-level gap if any.
