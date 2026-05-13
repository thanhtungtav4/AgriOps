# Seed Strategy - AgriOps MVP-0/MVP-1

**Generated:** 2026-05-12
**Agent:** B - DB/QA
**Project:** /Users/macbook/Herd/ariops

---

## Part 1: Canonical Seed Strategy

### Overview

Canonical seed creates a minimal but complete working dataset that demonstrates:
- Login → Dashboard → Demand → Plan → Batch → Log → Harvest → Packing → QR

### Seed Data Specification

#### Users (6 roles per BRD requirement)

| Role | Name | Email | Farm Access | Purpose |
|------|------|-------|-------------|---------|
| admin | Quản trị viên | admin@agriops.test | System | Full access |
| farm_owner | Ông Nguyễn Văn A | owner@agriops.test | Farm 1 | Farm owner |
| farm_manager | Bà Trần Thị B | manager@agriops.test | Farm 1 | Farm manager |
| technician | Anh Lê Văn C | tech@agriops.test | Farm 1 | Technician |
| worker | Chị Phạm Thị D | worker@agriops.test | Farm 1 | Field worker |
| delivery | Anh Hoàng Văn E | delivery@agriops.test | Farm 1 | Delivery |

**Password for all users:** `password` (hashed with bcrypt)

#### Farms (1 farm)

| Field | Value |
|-------|-------|
| name | Trang trại Rau An Toàn |
| code | FARM001 |
| address | 123 Đường Nguyễn Trãi, Quận 1, TP.HCM |
| climate_zone | tropical |
| total_area_m2 | 5000.00 |
| status | active |

#### Plots (2 plots)

| Code | Name | Area m² | Status | Purpose |
|------|------|---------|--------|---------|
| PLOT-A | Lô A - Nhà kính | 1500.00 | planting | Main production |
| PLOT-B | Lô B - Ngoài trời | 1000.00 | available | Secondary production |

#### Beds (6 beds, 3 per plot)

| Plot | Code | Length | Width | Area m² | Expected Plants |
|------|------|--------|-------|---------|-----------------|
| PLOT-A | A-01 | 50.00 | 3.00 | 150.00 | 150 |
| PLOT-A | A-02 | 50.00 | 3.00 | 150.00 | 150 |
| PLOT-A | A-03 | 50.00 | 3.00 | 150.00 | 150 |
| PLOT-B | B-01 | 40.00 | 2.50 | 100.00 | 100 |
| PLOT-B | B-02 | 40.00 | 2.50 | 100.00 | 100 |
| PLOT-B | B-03 | 40.00 | 2.50 | 100.00 | 100 |

#### Crops (1 crop)

| Field | Value |
|-------|-------|
| name | Rau muống (Water Spinach) |
| group | leafy |
| sale_unit | kg |
| production_unit | m2 |
| can_harvest_multiple | true |
| has_multiple_cycles | true |
| avg_growth_days | 30 |
| harvest_exploitation_days | 15 |
| rest_days | 7 |
| sale_price_per_unit | 15000.00 |

#### Crop Varieties (1 variety)

| Field | Value |
|-------|-------|
| name | Muống Nhật (Japanese Water Spinach) |
| code | VM001 |
| avg_growth_days | 25 |
| avg_yield_per_plant | 0.15 |
| planting_density_per_m2 | 20 |
| disease_resistance | Medium |
| suitable_season | All seasons |
| status | active |

#### Growth Stages (4 stages)

| Name | Order | Duration Days |
|------|-------|---------------|
| Gieo giống (Seeding) | 1 | 5 |
| Cây con (Seedling) | 2 | 10 |
| Sinh trưởng (Growth) | 3 | 10 |
| Thu hoạch (Harvest) | 4 | 15 |

#### Product Standards (1 standard)

| Field | Value |
|-------|-------|
| name | Rau muống loại 1 |
| code | PS001 |
| allowed_defect_percent | 5.00 |
| grade | A |

#### Harvest Models (1 model)

| Field | Value |
|-------|-------|
| harvest_type | multiple |
| avg_yield_per_plant | 0.15 |
| min_yield_per_plant | 0.10 |
| max_yield_per_plant | 0.20 |
| planting_density_per_m2 | 20 |
| survival_rate | 95.00 |
| grade_a_percent | 85.00 |
| grade_b_percent | 10.00 |
| grade_c_percent | 3.00 |
| reject_percent | 2.00 |
| days_to_first_harvest | 25 |
| harvest_duration_days | 15 |

#### Loss Profiles (1 profile)

| Field | Value |
|-------|-------|
| harvest_loss_percent | 5.00 |
| processing_loss_percent | 2.00 |
| packing_loss_percent | 1.00 |
| non_grade_a_percent | 10.00 |
| reject_percent | 2.00 |

#### Labor Norms (1 norm)

| Field | Value |
|-------|-------|
| hours_per_m2 | 0.5 |
| cost_per_m2 | 15000.00 |

#### Irrigation Norms (1 norm)

| Field | Value |
|-------|-------|
| frequency | daily |
| water_amount | 5.0 |
| unit | liters_per_m2_per_day |
| requires_actual_log | false |

#### Fertilizer Norms (1 norm)

| Field | Value |
|-------|-------|
| fertilizer_name | NPK 16-16-8 |
| amount | 0.5 |
| unit | kg |
| application_day_range | 7-14 |

#### Supply Contracts (1 contract)

| Field | Value |
|-------|-------|
| customer_name | Nhà hàng Xanh |
| customer_type | restaurant |
| quantity | 1000.00 |
| unit | kg |
| frequency | weekly |
| start_date | 2026-05-01 |
| end_date | 2026-12-31 |
| status | active |

#### Supply Demands (1 demand)

| Field | Value |
|-------|-------|
| quantity | 100.00 |
| unit | kg |
| frequency | weekly |
| target_date | 2026-05-19 |
| status | pending |

#### Production Plans (1 plan)

| Field | Value |
|-------|-------|
| quantity | 100.00 |
| unit | kg |
| target_delivery_date | 2026-05-19 |
| status | draft |

### Canonical Seed File Structure

```
database/seeders/
├── DatabaseSeeder.php          # Entry point, calls all seeders
├── CanonicalSeeder.php         # Happy path seed data
├── UserSeeder.php              # Users with roles
├── FarmSeeder.php              # Farm and plots/beds
├── CropSeeder.php              # Crops, varieties, stages
├── NormSeeder.php              # All norm types
├── ContractSeeder.php         # Supply contracts and demands
├── PlanningSeeder.php          # Production plans
└── CanonicalDemoSeeder.php     # Full end-to-end demo (MVP-1)
```

### Implementation Guidelines

```php
// Example: CanonicalSeeder.php
class CanonicalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users first (authentication dependency)
        $this->call(UserSeeder::class);
        
        // 2. Farm structure (spatial dependency)
        $this->call(FarmSeeder::class);
        
        // 3. Crops and master data (planning dependency)
        $this->call(CropSeeder::class);
        
        // 4. Norms (planning calculation dependency)
        $this->call(NormSeeder::class);
        
        // 5. Contracts and planning (business dependency)
        $this->call(ContractSeeder::class);
        $this->call(PlanningSeeder::class);
    }
}
```

---

## Part 2: Negative Seed Strategy

### Overview

Negative seed creates edge case data for testing:
- Missing required data
- Invalid state transitions
- Constraint violations
- Orphan records prevention

### Negative Test Data Scenarios

#### Scenario 1: Crop Without Loss Profile

| Field | Value |
|-------|-------|
| name | Cà chua không có loss |
| group | fruit |
| sale_unit | kg |
| production_unit | cây |

**Purpose:** Test planning calculation when loss_profile is missing → should return domain error.

#### Scenario 2: Plot With Status "rest" (Fallow)

| Code | Name | Area m² | Status |
|------|------|---------|--------|
| PLOT-REST | Lô Nghỉ Đất | 500.00 | rest |

**Purpose:** Test allocation warning/block when plot is in rest status.

#### Scenario 3: Plot With Status "suspended"

| Code | Name | Area m² | Status |
|------|------|---------|--------|
| PLOT-SUSPEND | Lô Tạm Ngưng | 300.00 | suspended |

**Purpose:** Test allocation rejection when plot is suspended.

#### Scenario 4: Supply Contract "cancelled"

| Field | Value |
|-------|-------|
| customer_name | Khách hàng đã hủy |
| status | cancelled |

**Purpose:** Test that cancelled contracts don't generate active demands.

#### Scenario 5: Supply Demand "fulfilled"

| Field | Value |
|-------|-------|
| status | fulfilled |
| quantity | 500.00 |

**Purpose:** Test that fulfilled demands can be queried for reporting but don't drive new planning.

#### Scenario 6: Production Plan "completed"

| Field | Value |
|-------|-------|
| status | completed |
| target_delivery_date | 2026-04-01 |

**Purpose:** Test historical data query without blocking new planning.

#### Scenario 7: Production Plan "cancelled"

| Field | Value |
|-------|-------|
| status | cancelled |
| notes | Hủy do không đủ nguồn lực |

**Purpose:** Test audit trail for cancelled plans.

#### Scenario 8: Variety "inactive"

| Field | Value |
|-------|-------|
| name | Giống cũ không còn sử dụng |
| code | OLD001 |
| status | inactive |

**Purpose:** Test that inactive varieties are excluded from planning dropdowns.

#### Scenario 9: Crop With "fruit_tree" Group

| Field | Value |
|-------|-------|
| name | Bưởi |
| group | fruit_tree |
| sale_unit | trái |
| production_unit | cây |

**Purpose:** Test different unit handling for fruit_tree group vs leafy/fruit/root.

#### Scenario 10: Farm "inactive"

| Field | Value |
|-------|-------|
| name | Trang trại cũ không hoạt động |
| code | FARM-OLD |
| status | inactive |

**Purpose:** Test that inactive farms are excluded from user assignment dropdowns.

### Negative Seed File Structure

```php
// database/seeders/NegativeSeeder.php
class NegativeSeeder extends Seeder
{
    public function run(): void
    {
        // Edge cases for constraint testing
        $this->seedCropWithoutLossProfile();
        $this->seedPlotsWithInvalidStatus();
        $this->seedCancelledContracts();
        $this->seedFulfilledDemands();
        $this->seedCompletedPlans();
        $this->seedInactiveVarieties();
        $this->seedFruitTreeCrop();
        $this->seedInactiveFarm();
    }
}
```

### Execution Command

```bash
# Run canonical seed only
php artisan db:seed --class=CanonicalSeeder

# Run negative seed for testing
php artisan db:seed --class=NegativeSeeder

# Run all seeds (canonical + negative)
php artisan db:seed
```

### Smoke Test After Seed

```bash
# Verify canonical seed
php artisan test --filter=CanonicalSeedTest

# Verify negative seed constraints
php artisan test --filter=NegativeSeedTest

# Full migration + seed
php artisan migrate:fresh --seed --env=testing
```

---

## Part 3: Data Quality Validation

### After Seed Validation Queries

```sql
-- Verify no orphan records in critical relationships
SELECT COUNT(*) FROM plots WHERE farm_id NOT IN (SELECT id FROM farms);
SELECT COUNT(*) FROM beds WHERE plot_id NOT IN (SELECT id FROM plots);
SELECT COUNT(*) FROM crop_varieties WHERE crop_id NOT IN (SELECT id FROM crops);
SELECT COUNT(*) FROM supply_demands WHERE crop_id NOT IN (SELECT id FROM crops);
SELECT COUNT(*) FROM production_plans WHERE farm_id NOT IN (SELECT id FROM farms);

-- Verify enum value consistency
SELECT DISTINCT status FROM plots WHERE status NOT IN ('available','preparing','planting','harvesting','rest','restoring','suspended');
SELECT DISTINCT role FROM users WHERE role NOT IN ('admin','farm_owner','farm_manager','technician','worker','warehouse','delivery');

-- Verify quantity fields have positive values
SELECT id, quantity FROM supply_contracts WHERE quantity <= 0;
SELECT id, quantity FROM supply_demands WHERE quantity <= 0;
SELECT id, quantity FROM production_plans WHERE quantity <= 0;

-- Verify area fields are non-negative
SELECT id, area_m2 FROM plots WHERE area_m2 < 0;
SELECT id, area_m2 FROM beds WHERE area_m2 < 0;
SELECT id, total_area_m2 FROM farms WHERE total_area_m2 < 0;

-- Verify date logic
SELECT id FROM supply_contracts WHERE end_date < start_date;
SELECT id FROM production_plans WHERE target_delivery_date < CURRENT_DATE;

-- Verify percentage fields in valid range
SELECT id, harvest_loss_percent FROM loss_profiles WHERE harvest_loss_percent > 100;
SELECT id, grade_a_percent FROM harvest_models WHERE grade_a_percent > 100;
```

---

**End of Seed Strategy**