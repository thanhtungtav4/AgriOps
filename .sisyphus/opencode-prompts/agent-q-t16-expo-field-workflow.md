You are Agent Q implementing Task 16: Expo App Field Workflow for AgriOps.

Work in /Users/macbook/Herd/ariops. Follow AGENTS instructions: shell commands must be prefixed with `rtk`.

Read before editing:
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md` Task 16.
- `.docs/yeu_cau_crm_nong_nghiep_v1.md` mobile/API/photo upload notes.
- Existing API controllers/models for auth, work tasks, and farming logs.

Scope:
- If no Expo app exists, create a self-contained Expo/React Native app under `mobile/field-app`.
- Do not add Expo dependencies to the Laravel root `package.json`.
- Build the mobile first screen as "Việc hôm nay": login, fetch assigned/today work tasks, accept/start task, submit farming log with note/photo placeholder, show queued/synced/error state.
- Use API v1 contract from Laravel; make base URL configurable via environment constant or config file.
- Add a small offline queue abstraction that persists pending log submissions locally and retries without duplicating entries. If native AsyncStorage is too heavy for tests, provide an adapter interface with an in-memory fallback and keep code phase-safe.
- Keep the UI usable one-handed: primary action near lower half, no marketing/landing page.
- Prefer simple React Native components and clean service modules. Do not over-engineer navigation.

Backend work is allowed only if needed to make the mobile flow testable. Keep controllers thin and covered by tests.

Testing/evidence:
- Add unit tests for the mobile queue/service logic if feasible with the created app tooling.
- Run relevant mobile checks (npm install/build/test or equivalent) from `mobile/field-app`.
- Run Laravel focused tests for work task/farming log APIs if backend touched.
- Run full `php artisan test` before finishing if backend touched, otherwise at least route/API focused tests plus note why.
- Write evidence logs:
  - `.sisyphus/evidence/task-16-expo-sync-happy.log`
  - `.sisyphus/evidence/task-16-expo-retry.log`
  - `.sisyphus/evidence/task-16-mobile-build.log`
  - `.sisyphus/evidence/task-16-refactor.log`

Plan update:
- Mark Task 16 `[x]` in `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md` only if implementation and verification pass.
- Add a concise Implementation status block dated 2026-05-13 with files/evidence.

Do not modify completed task sections except to keep evidence references consistent.
