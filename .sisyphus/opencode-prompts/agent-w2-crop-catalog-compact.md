# Agent W2 - Compact Crop Catalog Seed

You are Agent W2 for AgriOps. Work in `/Users/macbook/Herd/ariops`.

Use `rtk` before commands.

## Goal

Complete the unfinished crop catalog seed task. The previous Agent W prompt is too large; implement a compact, maintainable version.

## Own Files Only

- `database/seeders/AgriCropCatalogSeeder.php`
- `database/seeders/AgriFarmRegionalSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `tests/Feature/AgriCropCatalogSeederTest.php`
- `tests/Feature/AgriFarmRegionalSeederTest.php`
- `.sisyphus/evidence/task-crop-catalog-seed.md`

Do not touch controllers/services/routes/security files.

## Existing State

- `database/seeders/AgriFarmRegionalSeeder.php` already exists; review and improve only if needed.
- `AgriCropCatalogSeeder.php` does not exist yet.
- The canonical crop list is in `.sisyphus/opencode-prompts/agent-w-crop-catalog-seed.md`.

## Implementation Guidance

Do not write a giant hand-authored agronomy table for every field. Keep it data-driven:

1. Store the crop names in one array, exactly from the Agent W prompt.
2. Use helper methods to classify each crop:
   - group: leafy/fruit/root/fruit_tree
   - sale_unit and production_unit
   - suitable_climate_zone
   - growth days / density / yield / water / labor / loss defaults by crop group and climate
3. Add targeted overrides for important/key crops:
   - Rau muống: tropical, multi-harvest true
   - Xà lách romain: highland, 30-60 day growth
   - Khoai tây vàng: root, 80-130 day growth
   - Măng tây: multi-harvest/perennial-like, long harvest duration
   - Dưa leo baby: water >= 4 L/m2/day
   - Bắp sú tim and Cải thảo: temperate/highland
   - Khổ qua: tropical
4. For each crop, create/update:
   - `crops`
   - one active `crop_varieties` with deterministic code `CAT001`, `CAT002`, ...
   - 3-5 `growth_stages`
   - one `harvest_models`
   - one `irrigation_norms`
   - one `labor_norms`
   - one `loss_profiles`
   - one grade A `product_standards`
5. Use `updateOrCreate` / `firstOrCreate`; seeder must be idempotent.
6. Update `DatabaseSeeder.php` to call `AgriFarmRegionalSeeder::class` and `AgriCropCatalogSeeder::class` without removing existing calls.

## Tests

Create tests:

### `AgriCropCatalogSeederTest`
- run seeder and assert every requested crop exists
- assert seeded crop count equals the list count used by the seeder
- assert every seeded crop has active variety, growth stage, harvest model, irrigation norm, labor norm, loss profile, product standard
- assert idempotency by running twice
- assert key crops:
  - Xà lách romain growth 30-60 days
  - Khoai tây vàng growth 80-130 days
  - Măng tây multi-harvest/perennial-like with long harvest duration
  - Dưa leo baby water >= 4
  - Rau muống multi-harvest true and tropical
  - Khổ qua tropical

### `AgriFarmRegionalSeederTest`
- run seeder and assert FARM-CC/FARM-LD/FARM-MD exist
- assert climate zones: FARM-CC tropical, FARM-LD highland, FARM-MD highland
- assert active status and idempotency

## Verification

Run:

```bash
rtk php artisan test tests/Feature/AgriCropCatalogSeederTest.php tests/Feature/AgriFarmRegionalSeederTest.php
rtk php artisan test
```

## Evidence

Write `.sisyphus/evidence/task-crop-catalog-seed.md` with:

- exact crop count seeded
- farms seeded
- files changed
- realism approach and caveats
- tests run and results
- residual agronomist review notes
