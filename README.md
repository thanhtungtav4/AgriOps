# AgriOps

AgriOps is a Laravel, Filament, React, and Expo farm operations platform for planning, field execution, harvest, packing, delivery, and public QR traceability.

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan test
```

For the Expo field app:

```bash
cd mobile/field-app
npm install
npm run lint
npm test -- --runInBand
```

## Development

```bash
composer dev
```

The Laravel API and Filament admin share the main app. The React operations surface is served through Laravel routes under `/operations`. Public QR traceability is available at `/traceability/{qrCode}` and `/api/v1/traceability/{qrCode}`.

## Verification

```bash
php artisan test
bash .sisyphus/run-continuation/task-18-e2e-chain.sh
bash .sisyphus/run-continuation/task-18-fail-paths.sh
npm run build
cd mobile/field-app && npm run lint && npm test -- --runInBand
```

Current hardening baseline after the latest wave:

- Laravel full suite: 281 tests / 963 assertions.
- Expo field app focused tests: offline queue conflict handling covered.
- MVP/UAT evidence lives under `.sisyphus/evidence/`.

## Operations

Use PostgreSQL for staging/production:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ariops
DB_USERNAME=postgres
DB_PASSWORD=<secret>
QUEUE_CONNECTION=database
```

Deployment checklist:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Queue and scheduler:

```bash
php artisan queue:work database --tries=3 --timeout=60
php artisan schedule:run
```

Health and readiness:

```bash
curl -fsS http://localhost/up
bash .sisyphus/run-continuation/ops-readiness-smoke.sh
```

Backup and restore placeholders:

```bash
PGPASSWORD=<password> pg_dump -h <host> -U <user> -d ariops > ariops_$(date +%Y%m%d).sql
PGPASSWORD=<password> psql -h <host> -U <user> -d ariops < ariops_YYYYMMDD.sql
```

Rollback outline:

```bash
git checkout <last-good-ref>
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Notes

Do not commit `.env`, `vendor`, `node_modules`, runtime logs, generated build output, or local SQLite databases. Public QR responses are intentionally whitelisted and must not expose operational secrets such as chemical dosages, internal costs, user emails, or farm-private notes.
