# Hardening A - Audit Trail Evidence

Date: 2026-05-13

## Files Changed

- `database/migrations/2026_05_13_000018_create_audit_events_table.php`
- `app/Models/AuditEvent.php`
- `app/Services/PlantingBatchLifecycleService.php`
- `app/Services/PlantingBatchAllocationService.php`
- `tests/Feature/AuditTrailTest.php`

## Behavior Implemented

- Added durable `audit_events` table.
- Lifecycle transitions now create `lifecycle_transition` audit events.
- Allocation creation now creates `allocation_created` audit events.
- Audit events capture farm, actor/user, entity, from/to status, plot/bed, allocated area, reason, and metadata.

## Verification

- `rtk php artisan migrate --no-interaction`: PASS, audit migration ran.
- `rtk php artisan test`: PASS, 281 tests / 963 assertions.

## Notes

- Audit hooks are service-level. Future controller-level callers can pass user id where available.

