# Agent B2 Seed/PostgreSQL Evidence Prompt

You are Agent B2 DB/QA Implementation for AgriOps.

Work in `/Users/macbook/Herd/ariops`.

## Mission

Close seed reliability and local PostgreSQL evidence gaps without touching API security or planning formulas.

## Sources To Read

- `.sisyphus/evidence/release-risk-log.md`
- `.sisyphus/evidence/seed-strategy.md`
- `.sisyphus/evidence/data-dictionary-baseline.md`
- `database/seeders/*`
- `database/migrations/*`
- existing `tests/Feature/*`

## Owns

- `database/seeders/TestUserSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- New seeders if needed:
  - `database/seeders/CanonicalSeeder.php`
  - `database/seeders/NegativeSeeder.php`
- Seeder tests if useful:
  - `tests/Feature/SeederReliabilityTest.php`
- Evidence:
  - `.sisyphus/evidence/seed-postgres-evidence.md`

## Must Not Touch

- API controllers
- Auth policies/middleware
- Planning service/tests
- Filament resources
- `.sisyphus/agent-board.md`
- `.sisyphus/plans/laravel-filament-react-expo-auth-mvp.md`

## Implementation Requirements

1. `TestUserSeeder` must be idempotent. Running it twice must not fail.
2. Seeded users must use hashed passwords, not raw plaintext assignment.
3. `DatabaseSeeder` should call the canonical local/demo seed path or clearly remain minimal without creating duplicate random users each run.
4. Add a small `CanonicalSeeder` that creates a coherent demo baseline only if the current schema supports it.
5. Add a `NegativeSeeder` only if it can safely document invalid/edge data without breaking normal local workflows; otherwise document why deferred.
6. Capture local PostgreSQL evidence: `migrate:status`, relevant seed commands, and tests.

## Verification

Run:

```bash
rtk php artisan db:seed --class=TestUserSeeder
rtk php artisan db:seed --class=TestUserSeeder
rtk php artisan migrate:status
rtk php artisan test
```

Write evidence with commands, results, files changed, and open risks.

