# Task 10 - Packing API and Source Guards

## Files Changed

### Created
- `app/Services/PackingLotService.php` - Domain service for packing lot creation with source validation
- `app/Http/Controllers/Api/V1/PackingLotController.php` - REST controller with CRUD endpoints
- `tests/Feature/PackingLotApiTest.php` - 10 feature tests covering all business rules

### Modified
- `routes/api.php` - Added packing-lots routes under farm.scope middleware

## Business Rules Implemented

1. **Empty sources rejected** - HTTP 422 if no sources array or empty array
2. **Unavailable harvest lots rejected** - HTTP 422 with `PACKING_SOURCE_UNAVAILABLE` if status != 'available'
3. **Duplicate sources rejected** - HTTP 422 with `PACKING_DUPLICATE_SOURCES`
4. **Zero/negative quantity rejected** - HTTP 422 with `PACKING_INVALID_QUANTITY`
5. **Exceeds raw quantity rejected** - HTTP 422 with `PACKING_QUANTITY_EXCEEDS`
6. **Farm scope enforced** - Non-admin users can only pack harvest lots from their own farm (HTTP 403)
7. **Cross-farm mixing for admins** - Admin users can mix sources from different farms
8. **Response includes source details** - `sources` array includes `harvest_lot`, `farm`, `planting_batch` details
9. **Source harvest lots marked as packed** - Status updated after successful packing
10. **Code generation** - Unique code format: `PL-YYYYMMDD-XXXX`
11. **Atomic writes** - Source validation completes before insert and packing writes are wrapped in a transaction

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | /api/v1/packing-lots | List all packing lots (farm-scoped) |
| POST | /api/v1/packing-lots | Create packing lot with sources |
| GET | /api/v1/packing-lots/{id} | Show packing lot details |

## Test Coverage

- `rtk php artisan test tests/Feature/PackingLotApiTest.php`: passed, 10 tests, 62 assertions
- `rtk php artisan test`: passed, 242 tests, 778 assertions
- Covers: mixed farm creation, farm boundary enforcement, unavailable source rejection, duplicate sources, harvest lot status update, empty sources, zero quantity, quantity exceedance, list/show responses

## Risks

1. **QR code placeholder** - Uses generated random token; Task 11 will expose publicly
2. **total_input_quantity auto-calculation** - Sums source quantities; may need adjustment when partial allocation accounting is introduced
