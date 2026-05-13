# Agent W - Realistic Crop Catalog Seed with 3-Farm Regional Data

You are Agent W for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Create a realistic crop catalog seed for the user's Vietnamese vegetable/herb/root/fruit crop list, **plus seed 3 regional farms** (Củ Chi, Lâm Đồng, Măng Đen) with farm-specific agronomic profiles. The seed must include practical agronomic assumptions for growth cycle, harvest model, planting density, water need, labor, loss, growth stages, and **regional suitability mapping** — all as realistically as the current schema allows.

## Context

- Laravel project. Use `rtk` before every command.
- Existing schema supports:
  - `crops`: name, group, sale_unit, production_unit, can_harvest_multiple, has_multiple_cycles, avg_growth_days, harvest_exploitation_days, rest_days, sale_price_per_unit.
  - `crop_varieties`: code, name, description, avg_growth_days, avg_yield_per_plant, planting_density_per_m2, disease_resistance, suitable_season, suitable_climate_zone, care_requirements, notes.
  - `growth_stages`: crop_id, variety_id nullable, name, order, duration_days.
  - `harvest_models`: avg/min/max yield per plant, planting density, survival rate, grade percents, days to first harvest, harvest_duration_days.
  - `irrigation_norms`: water_amount, unit, frequency, timing, notes.
  - `fertilizer_norms`, `labor_norms`, `loss_profiles`, `product_standards`.
- Current `CanonicalSeeder` only seeds Rau muống. Prefer a new dedicated seeder instead of making `CanonicalSeeder` huge.

## Ownership

You own:

- `database/seeders/AgriCropCatalogSeeder.php` (new file)
- `database/seeders/AgriFarmRegionalSeeder.php` (new file — seeds 3 farms)
- minimal `database/seeders/DatabaseSeeder.php` update to call both seeders
- `tests/Feature/AgriCropCatalogSeederTest.php`
- `tests/Feature/AgriFarmRegionalSeederTest.php`
- `.sisyphus/evidence/task-crop-catalog-seed.md`

Do not touch:

- migrations unless blocked by schema
- controllers/routes/services
- React/Expo files
- unrelated tests
- CanonicalSeeder.php (leave it as-is for reference)

## 3 Regional Farms

Seed exactly 3 farms with realistic regional profiles. Use `firstOrCreate` by code for idempotency.

### Farm 1: Củ Chi (FARM-CC)
```
Name: "Nông Trường Củ Chi"
Code: FARM-CC
Climate Zone: tropical
Address: Xã Tân Thông Hội, Huyện Củ Chi, TP.HCM
Region: Đông Nam Bộ (lowland, ~10-25m elevation)
Avg Temp: 25-35°C year-round
Rainy Season: May-November
Status: active
```
**Agronomic profile**: Hot tropical lowland. High evaporation → irrigation norms +20-30% vs highland. Shorter growth cycles for leafy greens (heat accelerates). Suitable for: all tropical leafy greens, herbs, fruiting vegetables, root crops. NOT suitable for temperate crops (bắp sú, súp lơ, cải thảo) unless noted as heat-tolerant variety.

### Farm 2: Lâm Đồng (FARM-LD)
```
Name: "Trang Trại Lâm Đồng"
Code: FARM-LD
Climate Zone: highland
Address: Phường 11, TP. Đà Lạt, Tỉnh Lâm Đồng
Region: Tây Nguyên highland (~1500m elevation)
Avg Temp: 15-25°C year-round
Rainy Season: May-October
Status: active
```
**Agronomic profile**: Vietnam's premier highland vegetable region. Cool temperatures allow temperate crops impossible in lowlands. Longer growth cycles but higher quality/yield. Lower evaporation → irrigation norms baseline. Suitable for: ALL crops including temperate (bắp sú, súp lơ, cải thảo, cà rốt, xà lách các loại, cà chua, bắp cải tím). Best yields for cool-season crops.

### Farm 3: Măng Đen (FARM-MD)
```
Name: "Nông Trại Măng Đen"
Code: FARM-MD
Climate Zone: highland
Address: Thị trấn Măng Đen, Huyện Kon Plông, Tỉnh Kon Tum
Region: Tây Nguyên highland (~1200m elevation)
Avg Temp: 18-26°C year-round
Rainy Season: May-October
Status: active
```
**Agronomic profile**: Emerging highland agricultural area. Slightly warmer than Đà Lạt but still cool. Good for temperate and subtropical crops. Growing organic farming trend. Suitable for: similar to Lâm Đồng but with slightly shorter growth cycles for cool crops. Particularly good for: herbs, leafy greens, fruiting vegetables, root crops.

### Farm-Crop Suitability Mapping

Each crop variety has `suitable_climate_zone`. Use this to indicate which farms a variety thrives in:

| Climate Zone | Suitable Farms | Typical Crops |
|---|---|---|
| `tropical` | Củ Chi | Rau muống, rau dền, mồng tơi, húng, lá thuốc, khổ qua, dưa leo, bí, bầu, mướp, gừng, sả |
| `subtropical` | Củ Chi (cool months), Măng Đen | Hành lá, cần tây, đậu ve, cà tím, ớt, chanh |
| `temperate` | Lâm Đồng, Măng Đen | Bắp sú, súp lơ, cải thảo, cà rốt, su hào, củ cải trắng, bắp cải tím |
| `highland` | Lâm Đồng, Măng Đen | Xà lách các loại, cà chua beef/mini, khoai tây, măng tây, dâu, cải kale, bó xôi |

When seeding varieties, set `suitable_climate_zone` to the PRIMARY zone the variety thrives in. A crop can have multiple varieties suited for different zones (future expansion), but for this task seed ONE variety per crop with the most common zone.

## Crop List

Seed all of these names exactly as display names, preserving Vietnamese accents where provided, but normalize obvious casing only if needed:

- Lá thuốc dòi tím
- Lá sương sâm
- Lá chùm ngây
- Lá húng chanh
- Lá đinh lăng
- Xà lách romain
- Xà lách lo lo xanh
- Khoai tây vàng
- Ngải cứu
- Cải kale
- Cải bó xôi
- Cải bẹ xanh
- Cải ngọt
- Rau lang
- Cải thìa
- Mồng tơi
- Cải cúc
- Rau muống
- Rau dền
- Cải ngồng
- Đậu ve
- Cà tím nhật
- Măng tây
- Hành lá
- Cải muối dưa
- Lá vối
- Lá giang
- Lá chanh
- Bắp nếp
- Khổ qua
- Cà chua beef
- Bắp ngọt nhật
- Ớt chỉ địa
- Hành paro
- Bẹ bạc hà
- Lá dứa
- Xà lách mỹ
- Rau ngót
- Xà lách mỡ
- Dưa leo
- Chanh
- Bắp sú tim
- Cải thảo
- Củ dền
- Súp lơ xanh
- Ớt chuông đỏ
- Lá dổi xá xị
- Su su
- Bí đỏ
- Đu đủ
- Khoai lang
- Cà rốt
- Su hào
- Củ cải trắng
- Cà chua mini đỏ
- Bắp sú
- Bầu
- Lá chè xanh
- Bí đao
- Mướp hương
- Gừng
- Sả
- Đậu Hà Lan
- Cần tây
- Cà chua mini vàng
- Cà chua mini socola
- Lá hẹ
- Đọt bầu
- Rau càng cua
- Lá ổi
- Rau diếp cá
- Đọt khổ qua
- Súp lơ trắng
- Dưa leo baby
- Ớt chuông vàng
- Cải thìa tím
- Ớt chuông xanh
- Súp lơ baby
- Bắp cải tím
- Đậu cove nhật
- Khoai lang Úc

## Agronomy Requirements

For every crop:

- Create one active crop and one default active variety.
- Assign a realistic `group` using only allowed enum values:
  - `leafy` for leafy herbs/leaf vegetables/stems.
  - `fruit` for fruiting vegetables, corn, citrus, papaya.
  - `root` for tubers/roots/rhizomes.
  - `fruit_tree` only when the perennial tree behavior matters, for Chanh/Đu đủ if you judge appropriate.
- Set `sale_unit` realistically from allowed enum: `kg`, `trái`, `bó`, `thùng`.
- Set `production_unit` realistically from allowed enum: `cây`, `m2`, `luống`.
- Set growth days, first harvest, harvest duration, rest days, multi-harvest flags.
- **Set `suitable_climate_zone` on each variety** based on the crop's primary growing region (see Farm-Crop Suitability Mapping above). This determines which farm the variety is best suited for.
- Add planting density per m2 and yield per plant. If the item is leaf-harvested by area/bunch, use a practical per-plant equivalent and explain in notes.
- Add irrigation norm in `liters_per_m2_per_day` with frequency and timing. **Adjust for regional climate**: Củ Chi (tropical) needs +20-30% more water than Lâm Đồng/Măng Đen (highland) due to higher evaporation.
- Add labor norm and loss profile.
- Add 3-5 growth stages whose durations sum approximately to the growth cycle.
- Add product standard grade A baseline.
- Add care requirement notes with concise source/assumption notes. If exact local data is uncertain, use a range midpoint and state it.

## Realism Guidance

Use practical tropical/subtropical/highland Vietnam farm assumptions, not random values. **Account for regional climate differences across the 3 farms.**

### Regional Growth Adjustments

| Crop Type | Củ Chi (tropical) | Lâm Đồng (highland) | Măng Đen (highland) |
|---|---|---|---|
| Leafy greens | 20-40 days, higher density | 30-55 days, premium quality | 25-45 days, good quality |
| Fruiting veg | 40-80 days, heat stress risk | 50-100+ days, optimal | 45-90 days, near-optimal |
| Root/tuber | 55-100 days, smaller size | 70-130 days, larger size | 65-120 days, good size |
| Herbs/perennial | Fast growth, frequent harvest | Slower, more aromatic | Moderate, aromatic |

### Base Assumptions

- Leafy greens often 25-55 days, higher density, water roughly 3-6 L/m2/day (Củ Chi: 4-7, highlands: 3-5).
- Fruiting vegetables often 45-100+ days to first harvest, lower density, water roughly 4-8 L/m2/day (Củ Chi: 5-9, highlands: 4-7).
- Root/tuber crops often 60-130 days, moderate density, water roughly 3-6 L/m2/day.
- Perennials/herb leaves can have first establishment harvest and repeated harvest windows.
- Măng tây and Chanh are perennial-style crops; represent as best possible within current schema and document assumptions.

### Crop-to-Farm Suitability Examples

- **Củ Chi excels at**: Rau muống, rau dền, mồng tơi, húng chanh, lá thuốc, khổ qua, dưa leo, bí đỏ, bầu, mướp hương, gừng, sả, lá giang, lá chanh, rau lang, cải ngọt, đậu ve, ớt, chanh, đu đủ, bắp nếp, bắp ngọt nhật, su su
- **Lâm Đồng excels at**: Xà lách (all types), cải thảo, bắp sú, bắp sú tim, bắp cải tím, súp lơ xanh/trắng, cà rốt, su hào, củ cải trắng, cà chua (all), khoai tây, cải kale, cải bó xôi, cải cúc, cải thìa, măng tây, đậu Hà Lan, cần tây, hành paro, hành lá
- **Măng Đen excels at**: Similar to Lâm Đồng but also good for herbs (ngải cứu, lá chè xanh, lá vối, lá dổi), mixed leafy greens, and fruiting vegetables with slightly shorter cycles

Prefer reputable agronomy references if web access is available to you. Do not invent false source citations. Evidence must state whether data is source-backed or agronomic-estimate.

## Implementation Shape

- **Farm seeder** (`AgriFarmRegionalSeeder.php`): Seeds 3 farms (FARM-CC, FARM-LD, FARM-MD) with full profiles using `firstOrCreate` by code. Idempotent.
- **Crop catalog seeder** (`AgriCropCatalogSeeder.php`): Seeds all 121 crops with varieties, growth stages, harvest models, irrigation norms, labor norms, loss profiles, product standards. Keep data arrays readable and maintainable.
- Keep both seeders idempotent with `firstOrCreate` / `updateOrCreate`.
- Use deterministic crop codes for variety codes, e.g. `CAT001`, `CAT002`.
- If a crop already exists, update missing catalog fields without duplicating.
- Avoid adding more than one variety per crop in this task.
- Update `DatabaseSeeder.php` to call both new seeders.

## Tests

### AgriCropCatalogSeederTest (`tests/Feature/AgriCropCatalogSeederTest.php`)

Create tests that:

- Run the seeder and assert every requested crop exists (121 crops).
- Assert each seeded crop has:
  - at least one active variety
  - at least one growth stage
  - harvest model
  - irrigation norm
  - labor norm
  - loss profile
  - product standard
- Assert the seeder is idempotent by running it twice and confirming crop count does not double.
- Assert key crops have plausible data:
  - Xà lách romain growth 30-60 days
  - Khoai tây vàng growth 80-130 days
  - Măng tây marked multi-harvest/perennial-like with long harvest duration
  - Dưa leo baby water >= 4 L/m2/day
  - Rau muống multi-harvest true
- **Regional suitability assertions**:
  - Rau muống variety suitable_climate_zone = 'tropical'
  - Xà lách romain variety suitable_climate_zone = 'highland'
  - Bắp sú tim variety suitable_climate_zone = 'temperate' or 'highland'
  - Cải thảo variety suitable_climate_zone = 'temperate' or 'highland'
  - Khổ qua variety suitable_climate_zone = 'tropical'

### AgriFarmRegionalSeederTest (`tests/Feature/AgriFarmRegionalSeederTest.php`)

Create tests that:

- Run the farm seeder and assert all 3 farms exist by code (FARM-CC, FARM-LD, FARM-MD).
- Assert each farm has correct climate_zone:
  - FARM-CC → tropical
  - FARM-LD → highland
  - FARM-MD → highland
- Assert each farm has name, address, status = active.
- Assert the seeder is idempotent by running it twice and confirming farm count does not increase.

Run:

```bash
rtk php artisan test tests/Feature/AgriCropCatalogSeederTest.php
rtk php artisan test tests/Feature/AgriFarmRegionalSeederTest.php
rtk php artisan test
```

## Evidence

Write `.sisyphus/evidence/task-crop-catalog-seed.md` with:

- number of farms seeded (3) with codes and climate zones
- number of crops seeded (121)
- files changed
- data realism approach (regional climate adjustments, crop-to-farm suitability)
- source/assumption caveats
- tests run and results
- any crops that need later agronomist review
- summary of regional suitability mapping (which crops assigned to which climate zones)
