You are Agent S implementing the Task 18 dry-run end-to-end chain evidence.

Work in /Users/macbook/Herd/ariops. Follow AGENTS instructions: every shell command must be prefixed with `rtk`.

Read before editing:
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md` Task 18.
- Existing API tests/controllers for planning, planting batches, allocations, work tasks, farming logs, inspections, harvest, packing, traceability, delivery, returns.
- Existing seeders/factories from Task 17.

Ownership:
- You may create or edit only:
  - `.sisyphus/run-continuation/task-18-e2e-chain.sh`
  - `.sisyphus/evidence/task-18-e2e-chain.log`
  - Optional supporting notes under `.sisyphus/evidence/task-18-e2e-chain.md`
- Do not edit application code unless absolutely required and explicitly justified in your final summary.
- Do not update the plan file; Codex integrator will update it after review.

Goal:
- Build a reproducible dry-run artifact for demand → delivery.
- Prefer a Laravel/PHP or bash script that uses the existing app/test DB safely. If API auth/bootstrap makes curl too costly, use `php artisan test` or a focused smoke script with factories and service/API calls, but keep the evidence tied to the Task 18 scenario.
- The chain should cover as much as currently implemented without inventing endpoints:
  demand/planning → production plan → planting batch → allocation/work task/log → inspection → harvest → packing/QR/traceability → delivery.

Verification:
- Run the script/test and capture output to `.sisyphus/evidence/task-18-e2e-chain.log`.
- Include a short summary of covered endpoints/models and any intentional gaps in the evidence.
