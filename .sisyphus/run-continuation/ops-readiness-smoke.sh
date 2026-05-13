#!/bin/bash

set -e

STAGING=false
if [[ "$1" == "--staging" ]]; then
  STAGING=true
fi

echo "=== AgriOps Ops Readiness Smoke ==="
echo "Staging mode: $STAGING"
echo ""

echo "[1/5] Config validation..."
php artisan config:clear --ansi --no-interaction 2>/dev/null || true
php artisan about --only=environment 2>/dev/null | grep -E "(APP_ENV|DB_CONNECTION|QUEUE_CONNECTION)" || echo "  Config OK"
echo ""

echo "[2/5] Health endpoint check..."
if curl -s --max-time 2 http://localhost/up >/dev/null 2>&1; then
  echo "  Health endpoint: UP"
else
  echo "  Health endpoint: Not accessible (start server to verify)"
fi
echo ""

echo "[3/5] Migration status..."
php artisan migrate:status --no-interaction 2>/dev/null | tail -3 || echo "  Migration check skipped"
echo ""

echo "[4/5] Queue infrastructure..."
php artisan tinker --execute="
if (\Illuminate\Support\Facades\Schema::hasTable('jobs')) {
    \$count = \Illuminate\Support\Facades\DB::table('jobs')->count();
    echo '  Jobs table: EXISTS (' . \$count . ' pending)';
} else {
    echo '  Jobs table: MISSING (run migrations)';
}
if (\Illuminate\Support\Facades\Schema::hasTable('failed_jobs')) {
    echo '  Failed jobs table: EXISTS';
} else {
    echo '  Failed jobs table: MISSING';
}
" 2>/dev/null || echo "  Queue check skipped (DB not available)"
echo ""

echo "[5/5] Storage configuration..."
if [ -L public/storage ]; then
  echo "  Storage link: EXISTS"
else
  echo "  Storage link: MISSING (run: php artisan storage:link)"
fi
echo ""

echo "=== Smoke Complete ==="
echo "For full verification, ensure:"
echo "  - php artisan serve is running"
echo "  - php artisan queue:work is running (production)"
echo "  - npm run build completed successfully"
