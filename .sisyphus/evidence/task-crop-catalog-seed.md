# Task Crop Catalog Seed Evidence

## Scope

- Added `AgriCropCatalogSeeder` for the 81 crop names listed in `.sisyphus/opencode-prompts/agent-w-crop-catalog-seed.md`.
- Updated `AgriFarmRegionalSeeder` with 3 regional farms: Củ Chi, Lâm Đồng, Măng Đen.
- Updated `DatabaseSeeder` to call farm and crop catalog seeders while preserving existing seeders.
- Added feature tests for crop catalog completeness, idempotency, key agronomic plausibility, and farm regional metadata.

## Data Method

- Crop names are copied exactly from the Agent W prompt.
- Agronomy values are practical estimates for demo/planning use in Vietnam farm contexts, not source-cited field trial data.
- The seeder uses helper classification for crop group, climate zone, multi-harvest behavior, density, yield, water, labor, loss, and product standards.
- Explicit overrides cover key crops requested in the prompt: Rau muống, Xà lách romain, Khoai tây vàng, Măng tây, Dưa leo baby, Bắp sú tim, Cải thảo, Khổ qua.

## Verification

- `rtk php artisan test tests/Feature/AgriCropCatalogSeederTest.php tests/Feature/AgriFarmRegionalSeederTest.php`
  - Passed: 5 tests, 762 assertions.
- `rtk php artisan test`
  - Passed: 369 tests, 1943 assertions.
