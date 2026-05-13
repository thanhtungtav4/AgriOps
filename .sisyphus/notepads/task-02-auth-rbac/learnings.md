# Task T2 Auth + RBAC Learnings

## Patterns
- Laravel 11 uses bootstrap/app.php for routing config, not RouteServiceProvider
- Need apiPrefix: 'api' in withRouting() to mount routes under /api
- Migration ordering matters for foreign keys (farms before users)
- AuthServiceProvider registered via bootstrap/providers.php

## Issues Encountered
- farms migration was created with timestamp suffix, causing it to run AFTER users migration
- Solution: renamed to 0000_ prefix to ensure correct ordering

## Decisions
- Placed ApprovalPolicy in app/Policies/ folder for future expansion
- Kept policy registration empty for now (no PlantingBatch model exists yet)

