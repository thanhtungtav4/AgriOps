# PostgreSQL Constraint/Index Checklist - AgriOps MVP-0

**Generated:** 2026-05-12
**Agent:** B - DB/QA
**Project:** /Users/macbook/Herd/ariops

---

## Existing PostgreSQL Constraints & Indexes

### Identity/RBAC Module

#### users
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | users_pkey | id | ✅ | OK |
| UNIQUE | users_email_unique | email | ✅ | OK |
| INDEX | users_role_index | role | ✅ | OK |
| INDEX | users_farm_id_index | farm_id | ✅ | OK |
| FOREIGN KEY | users_farm_id_foreign | farm_id → farms.id | ON DELETE SET NULL | ⚠️ CHECK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Role must be valid enum
ALTER TABLE users ADD CONSTRAINT users_role_check
  CHECK (role IN ('admin','farm_owner','farm_manager','technician','worker','warehouse','delivery'));
```

---

### Master Data Module

#### farms
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | farms_pkey | id | ✅ | OK |
| UNIQUE | farms_code_unique | code | ✅ | OK |
| INDEX | farms_status_index | status | ✅ | OK |
| INDEX | farms_climate_zone_index | climate_zone | ✅ | OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Total area must be non-negative
ALTER TABLE farms ADD CONSTRAINT farms_total_area_m2_check
  CHECK (total_area_m2 >= 0);

-- Status must be valid enum
ALTER TABLE farms ADD CONSTRAINT farms_status_check
  CHECK (status IN ('active','inactive'));

-- Certification should be valid JSONB if not null
ALTER TABLE farms ADD CONSTRAINT farms_certification_json_check
  CHECK (certification IS NULL OR jsonb_typeof(certification) = 'object');
```

---

#### plots
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | plots_pkey | id | ✅ | OK |
| UNIQUE | plots_farm_id_code_unique | (farm_id, code) | ✅ | OK |
| INDEX | plots_status_index | status | ✅ | OK |
| INDEX | plots_current_batch_id_index | current_batch_id | ✅ | OK |
| FOREIGN KEY | plots_farm_id_foreign | farm_id → farms.id | ON DELETE CASCADE | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Area must be non-negative
ALTER TABLE plots ADD CONSTRAINT plots_area_m2_check
  CHECK (area_m2 >= 0);

-- Status must be valid enum
ALTER TABLE plots ADD CONSTRAINT plots_status_check
  CHECK (status IN ('available','preparing','planting','harvesting','rest','restoring','suspended'));

-- Validate area against farm total (requires function or computed check)
-- Note: Farm area constraint is complex; handle in application logic
```

---

#### beds
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | beds_pkey | id | ✅ | OK |
| UNIQUE | beds_plot_id_code_unique | (plot_id, code) | ✅ | OK |
| INDEX | beds_plot_id_index | plot_id | ✅ | OK |
| FOREIGN KEY | beds_plot_id_foreign | plot_id → plots.id | ON DELETE SET NULL | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Length must be non-negative
ALTER TABLE beds ADD CONSTRAINT beds_length_m_check
  CHECK (length_m >= 0);

-- Width must be non-negative
ALTER TABLE beds ADD CONSTRAINT beds_width_m_check
  CHECK (width_m >= 0);

-- Area must be non-negative
ALTER TABLE beds ADD CONSTRAINT beds_area_m2_check
  CHECK (area_m2 >= 0);

-- Area should match length × width (approximate due to rounding)
ALTER TABLE beds ADD CONSTRAINT beds_area_calculation_check
  CHECK (abs(area_m2 - (length_m * width_m)) < 0.01);

-- Expected plants must be non-negative
ALTER TABLE beds ADD CONSTRAINT beds_expected_plants_check
  CHECK (expected_plants >= 0);

-- Status must be valid enum
ALTER TABLE beds ADD CONSTRAINT beds_status_check
  CHECK (status IN ('available','preparing','planting','growing','harvesting','rest'));
```

---

#### crops
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | crops_pkey | id | ✅ | OK |
| INDEX | crops_group_index | group | ✅ | OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Group must be valid enum
ALTER TABLE crops ADD CONSTRAINT crops_group_check
  CHECK (group IN ('leafy','fruit','root','fruit_tree'));

-- Sale unit must be valid enum
ALTER TABLE crops ADD CONSTRAINT crops_sale_unit_check
  CHECK (sale_unit IN ('kg','trái','bó','thùng'));

-- Production unit must be valid enum
ALTER TABLE crops ADD CONSTRAINT crops_production_unit_check
  CHECK (production_unit IN ('cây','m2','luống'));

-- Numeric fields must be non-negative
ALTER TABLE crops ADD CONSTRAINT crops_avg_growth_days_check
  CHECK (avg_growth_days >= 0);
ALTER TABLE crops ADD CONSTRAINT crops_harvest_exploitation_days_check
  CHECK (harvest_exploitation_days >= 0);
ALTER TABLE crops ADD CONSTRAINT crops_rest_days_check
  CHECK (rest_days >= 0);

-- Sale price must be non-negative (if not null)
ALTER TABLE crops ADD CONSTRAINT crops_sale_price_check
  CHECK (sale_price_per_unit IS NULL OR sale_price_per_unit >= 0);
```

---

#### crop_varieties
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | crop_varieties_pkey | id | ✅ | OK |
| UNIQUE | crop_varieties_crop_id_code_unique | (crop_id, code) | ✅ | OK |
| INDEX | crop_varieties_status_index | status | ✅ | OK |
| FOREIGN KEY | crop_varieties_crop_id_foreign | crop_id → crops.id | ON DELETE CASCADE | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Code can be NULL but if present must be unique per crop (partial unique handled by DB)
-- Status must be valid enum
ALTER TABLE crop_varieties ADD CONSTRAINT crop_varieties_status_check
  CHECK (status IN ('active','inactive'));

-- Numeric fields non-negative
ALTER TABLE crop_varieties ADD CONSTRAINT crop_varieties_avg_growth_days_check
  CHECK (avg_growth_days >= 0);
ALTER TABLE crop_varieties ADD CONSTRAINT crop_varieties_avg_yield_check
  CHECK (avg_yield_per_plant IS NULL OR avg_yield_per_plant >= 0);
ALTER TABLE crop_varieties ADD CONSTRAINT crop_varieties_density_check
  CHECK (planting_density_per_m2 IS NULL OR planting_density_per_m2 > 0);
```

---

#### growth_stages
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | growth_stages_pkey | id | ✅ | OK |
| INDEX | growth_stages_crop_id_order_index | (crop_id, order) | ✅ | OK |
| FOREIGN KEY | growth_stages_crop_id_foreign | crop_id → crops.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | growth_stages_variety_id_foreign | variety_id → crop_varieties.id | ON DELETE CASCADE | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Order must be non-negative
ALTER TABLE growth_stages ADD CONSTRAINT growth_stages_order_check
  CHECK (order >= 0);

-- Duration must be non-negative
ALTER TABLE growth_stages ADD CONSTRAINT growth_stages_duration_days_check
  CHECK (duration_days >= 0);
```

---

#### product_standards
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | product_standards_pkey | id | ✅ | OK |
| INDEX | product_standards_crop_id_index | crop_id | ✅ | OK |
| FOREIGN KEY | product_standards_crop_id_foreign | crop_id → crops.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | product_standards_variety_id_foreign | variety_id → crop_varieties.id | ON DELETE SET NULL | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Allowed defect must be 0-100
ALTER TABLE product_standards ADD CONSTRAINT product_standards_defect_check
  CHECK (allowed_defect_percent >= 0 AND allowed_defect_percent <= 100);

-- Grade must be valid enum
ALTER TABLE product_standards ADD CONSTRAINT product_standards_grade_check
  CHECK (grade IN ('A','B','C','reject'));
```

---

### Planning Module

#### harvest_models
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | harvest_models_pkey | id | ✅ | OK |
| INDEX | harvest_models_crop_id_variety_id_index | (crop_id, variety_id) | ✅ | OK |
| FOREIGN KEY | harvest_models_crop_id_foreign | crop_id → crops.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | harvest_models_variety_id_foreign | variety_id → crop_varieties.id | ON DELETE CASCADE | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Harvest type must be valid enum
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_type_check
  CHECK (harvest_type IN ('single','multiple'));

-- Yields must be non-negative
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_avg_yield_check
  CHECK (avg_yield_per_plant >= 0);
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_min_yield_check
  CHECK (min_yield_per_plant IS NULL OR min_yield_per_plant >= 0);
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_max_yield_check
  CHECK (max_yield_per_plant IS NULL OR max_yield_per_plant >= 0);
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_max_min_check
  CHECK (min_yield_per_plant IS NULL OR max_yield_per_plant IS NULL OR max_yield_per_plant >= min_yield_per_plant);

-- Planting density must be positive
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_density_check
  CHECK (planting_density_per_m2 > 0);

-- Percentages must be 0-100
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_survival_rate_check
  CHECK (survival_rate >= 0 AND survival_rate <= 100);
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_grade_a_check
  CHECK (grade_a_percent >= 0 AND grade_a_percent <= 100);
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_grade_b_check
  CHECK (grade_b_percent >= 0 AND grade_b_percent <= 100);
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_grade_c_check
  CHECK (grade_c_percent >= 0 AND grade_c_percent <= 100);
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_reject_check
  CHECK (reject_percent >= 0 AND reject_percent <= 100);

-- Grade percentages should sum to 100 (warning, not hard constraint)
-- Days must be non-negative
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_days_check
  CHECK (days_to_first_harvest >= 0);
ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_duration_check
  CHECK (harvest_duration_days >= 0);
```

---

#### loss_profiles
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | loss_profiles_pkey | id | ✅ | OK |
| INDEX | loss_profiles_crop_id_variety_id_index | (crop_id, variety_id) | ✅ | OK |
| FOREIGN KEY | loss_profiles_crop_id_foreign | crop_id → crops.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | loss_profiles_variety_id_foreign | variety_id → crop_varieties.id | ON DELETE CASCADE | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- All percentage fields must be 0-100
ALTER TABLE loss_profiles ADD CONSTRAINT loss_profiles_harvest_check
  CHECK (harvest_loss_percent >= 0 AND harvest_loss_percent <= 100);
ALTER TABLE loss_profiles ADD CONSTRAINT loss_profiles_processing_check
  CHECK (processing_loss_percent >= 0 AND processing_loss_percent <= 100);
ALTER TABLE loss_profiles ADD CONSTRAINT loss_profiles_packing_check
  CHECK (packing_loss_percent >= 0 AND packing_loss_percent <= 100);
ALTER TABLE loss_profiles ADD CONSTRAINT loss_profiles_non_grade_a_check
  CHECK (non_grade_a_percent >= 0 AND non_grade_a_percent <= 100);
ALTER TABLE loss_profiles ADD CONSTRAINT loss_profiles_reject_check
  CHECK (reject_percent >= 0 AND reject_percent <= 100);

-- Total loss should not exceed reasonable threshold (e.g., 80%)
ALTER TABLE loss_profiles ADD CONSTRAINT loss_profiles_total_check
  CHECK ((harvest_loss_percent + processing_loss_percent + packing_loss_percent) <= 80);
```

---

#### labor_norms
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | labor_norms_pkey | id | ✅ | OK |
| INDEX | labor_norms_crop_id_variety_id_index | (crop_id, variety_id) | ✅ | OK |
| FOREIGN KEY | labor_norms_crop_id_foreign | crop_id → crops.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | labor_norms_variety_id_foreign | variety_id → crop_varieties.id | ON DELETE CASCADE | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Hours and cost must be non-negative
ALTER TABLE labor_norms ADD CONSTRAINT labor_norms_hours_check
  CHECK (hours_per_m2 >= 0);
ALTER TABLE labor_norms ADD CONSTRAINT labor_norms_cost_check
  CHECK (cost_per_m2 >= 0);
```

---

#### irrigation_norms
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | irrigation_norms_pkey | id | ✅ | OK |
| INDEX | irrigation_norms_crop_id_variety_id_index | (crop_id, variety_id) | ✅ | OK |
| FOREIGN KEY | irrigation_norms_crop_id_foreign | crop_id → crops.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | irrigation_norms_variety_id_foreign | variety_id → crop_varieties.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | irrigation_norms_growth_stage_id_foreign | growth_stage_id → growth_stages.id | ON DELETE CASCADE | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Unit must be valid enum
ALTER TABLE irrigation_norms ADD CONSTRAINT irrigation_norms_unit_check
  CHECK (unit IN ('liters_per_m2_per_day','liters_per_plant_per_day','liters_per_bed_per_day'));

-- Water amount must be non-negative
ALTER TABLE irrigation_norms ADD CONSTRAINT irrigation_norms_water_check
  CHECK (water_amount >= 0);
```

---

#### fertilizer_norms
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | fertilizer_norms_pkey | id | ✅ | OK |
| INDEX | fertilizer_norms_crop_id_variety_id_index | (crop_id, variety_id) | ✅ | OK |
| FOREIGN KEY | fertilizer_norms_crop_id_foreign | crop_id → crops.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | fertilizer_norms_variety_id_foreign | variety_id → crop_varieties.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | fertilizer_norms_growth_stage_id_foreign | growth_stage_id → growth_stages.id | ON DELETE CASCADE | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Amount must be non-negative
ALTER TABLE fertilizer_norms ADD CONSTRAINT fertilizer_norms_amount_check
  CHECK (amount >= 0);

-- Unit should be a valid unit string (no strict enum in migration)
```

---

#### supply_contracts
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | supply_contracts_pkey | id | ✅ | OK |
| INDEX | supply_contracts_crop_id_index | crop_id | ✅ | OK |
| FOREIGN KEY | supply_contracts_crop_id_foreign | crop_id → crops.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | supply_contracts_product_standard_id_foreign | product_standard_id → product_standards.id | ON DELETE SET NULL | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Customer type must be valid enum
ALTER TABLE supply_contracts ADD CONSTRAINT supply_contracts_customer_type_check
  CHECK (customer_type IN ('restaurant','wholesale','retail','export','other'));

-- Unit must be valid enum
ALTER TABLE supply_contracts ADD CONSTRAINT supply_contracts_unit_check
  CHECK (unit IN ('kg','trái','bó','thùng'));

-- Frequency must be valid enum
ALTER TABLE supply_contracts ADD CONSTRAINT supply_contracts_frequency_check
  CHECK (frequency IN ('once','daily','weekly','monthly','seasonal'));

-- Quantity must be positive
ALTER TABLE supply_contracts ADD CONSTRAINT supply_contracts_quantity_check
  CHECK (quantity > 0);

-- End date must be >= start date (if not null)
ALTER TABLE supply_contracts ADD CONSTRAINT supply_contracts_dates_check
  CHECK (end_date IS NULL OR end_date >= start_date);

-- Status must be valid enum
ALTER TABLE supply_contracts ADD CONSTRAINT supply_contracts_status_check
  CHECK (status IN ('active','completed','cancelled'));
```

---

#### supply_demands
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | supply_demands_pkey | id | ✅ | OK |
| INDEX | supply_demands_supply_contract_id_index | supply_contract_id | ✅ | OK |
| INDEX | supply_demands_crop_id_index | crop_id | ✅ | OK |
| INDEX | supply_demands_status_index | status | ✅ | OK |
| FOREIGN KEY | supply_demands_supply_contract_id_foreign | supply_contract_id → supply_contracts.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | supply_demands_crop_id_foreign | crop_id → crops.id | ON DELETE CASCADE | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Unit must be valid enum
ALTER TABLE supply_demands ADD CONSTRAINT supply_demands_unit_check
  CHECK (unit IN ('kg','trái','bó','thùng'));

-- Frequency must be valid enum
ALTER TABLE supply_demands ADD CONSTRAINT supply_demands_frequency_check
  CHECK (frequency IN ('once','daily','weekly','monthly','seasonal'));

-- Quantity must be positive
ALTER TABLE supply_demands ADD CONSTRAINT supply_demands_quantity_check
  CHECK (quantity > 0);

-- Status must be valid enum
ALTER TABLE supply_demands ADD CONSTRAINT supply_demands_status_check
  CHECK (status IN ('pending','planned','fulfilled'));
```

---

#### production_plans
| Type | Name | Column(s) | PostgreSQL | Status |
|------|------|-----------|------------|--------|
| PRIMARY KEY | production_plans_pkey | id | ✅ | OK |
| INDEX | production_plans_crop_id_index | crop_id | ✅ | OK |
| INDEX | production_plans_farm_id_index | farm_id | ✅ | OK |
| INDEX | production_plans_supply_contract_id_index | supply_contract_id | ✅ | OK |
| FOREIGN KEY | production_plans_supply_contract_id_foreign | supply_contract_id → supply_contracts.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | production_plans_supply_demand_id_foreign | supply_demand_id → supply_demands.id | ON DELETE SET NULL | ✅ OK |
| FOREIGN KEY | production_plans_crop_id_foreign | crop_id → crops.id | ON DELETE CASCADE | ✅ OK |
| FOREIGN KEY | production_plans_variety_id_foreign | variety_id → crop_varieties.id | ON DELETE SET NULL | ✅ OK |
| FOREIGN KEY | production_plans_farm_id_foreign | farm_id → farms.id | ON DELETE CASCADE | ✅ OK |

**PostgreSQL CHECK Recommendations:**
```sql
-- Unit must be valid enum
ALTER TABLE production_plans ADD CONSTRAINT production_plans_unit_check
  CHECK (unit IN ('kg','trái','bó','thùng'));

-- Quantity must be positive
ALTER TABLE production_plans ADD CONSTRAINT production_plans_quantity_check
  CHECK (quantity > 0);

-- Estimated cost must be non-negative (if not null)
ALTER TABLE production_plans ADD CONSTRAINT production_plans_cost_check
  CHECK (estimated_cost IS NULL OR estimated_cost >= 0);

-- Estimated revenue must be non-negative (if not null)
ALTER TABLE production_plans ADD CONSTRAINT production_plans_revenue_check
  CHECK (estimated_revenue IS NULL OR estimated_revenue >= 0);

-- Margin percent must be reasonable (e.g., -100 to 500)
ALTER TABLE production_plans ADD CONSTRAINT production_plans_margin_check
  CHECK (margin_percent IS NULL OR (margin_percent >= -100 AND margin_percent <= 500));

-- Status must be valid enum
ALTER TABLE production_plans ADD CONSTRAINT production_plans_status_check
  CHECK (status IN ('draft','planning','approved','in_progress','completed','cancelled'));
```

---

## Recommended Migration for CHECK Constraints

Create a new migration file:

```php
// database/migrations/2026_05_12_XXXXXX_add_postgresql_check_constraints.php

public function up(): void
{
    // Users
    DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin','farm_owner','farm_manager','technician','worker','warehouse','delivery'))");

    // Farms
    DB::statement("ALTER TABLE farms ADD CONSTRAINT farms_total_area_m2_check CHECK (total_area_m2 >= 0)");
    DB::statement("ALTER TABLE farms ADD CONSTRAINT farms_status_check CHECK (status IN ('active','inactive'))");

    // Plots
    DB::statement("ALTER TABLE plots ADD CONSTRAINT plots_area_m2_check CHECK (area_m2 >= 0)");
    DB::statement("ALTER TABLE plots ADD CONSTRAINT plots_status_check CHECK (status IN ('available','preparing','planting','harvesting','rest','restoring','suspended'))");

    // Beds
    DB::statement("ALTER TABLE beds ADD CONSTRAINT beds_length_m_check CHECK (length_m >= 0)");
    DB::statement("ALTER TABLE beds ADD CONSTRAINT beds_width_m_check CHECK (width_m >= 0)");
    DB::statement("ALTER TABLE beds ADD CONSTRAINT beds_area_m2_check CHECK (area_m2 >= 0)");
    DB::statement("ALTER TABLE beds ADD CONSTRAINT beds_expected_plants_check CHECK (expected_plants >= 0)");
    DB::statement("ALTER TABLE beds ADD CONSTRAINT beds_status_check CHECK (status IN ('available','preparing','planting','growing','harvesting','rest'))");

    // Crops
    DB::statement("ALTER TABLE crops ADD CONSTRAINT crops_group_check CHECK (group IN ('leafy','fruit','root','fruit_tree'))");
    DB::statement("ALTER TABLE crops ADD CONSTRAINT crops_sale_unit_check CHECK (sale_unit IN ('kg','trái','bó','thùng'))");
    DB::statement("ALTER TABLE crops ADD CONSTRAINT crops_production_unit_check CHECK (production_unit IN ('cây','m2','luống'))");
    DB::statement("ALTER TABLE crops ADD CONSTRAINT crops_sale_price_check CHECK (sale_price_per_unit IS NULL OR sale_price_per_unit >= 0)");

    // Crop varieties
    DB::statement("ALTER TABLE crop_varieties ADD CONSTRAINT crop_varieties_status_check CHECK (status IN ('active','inactive'))");
    DB::statement("ALTER TABLE crop_varieties ADD CONSTRAINT crop_varieties_density_check CHECK (planting_density_per_m2 IS NULL OR planting_density_per_m2 > 0)");

    // Product standards
    DB::statement("ALTER TABLE product_standards ADD CONSTRAINT product_standards_defect_check CHECK (allowed_defect_percent >= 0 AND allowed_defect_percent <= 100)");
    DB::statement("ALTER TABLE product_standards ADD CONSTRAINT product_standards_grade_check CHECK (grade IN ('A','B','C','reject'))");

    // Harvest models
    DB::statement("ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_type_check CHECK (harvest_type IN ('single','multiple'))");
    DB::statement("ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_avg_yield_check CHECK (avg_yield_per_plant >= 0)");
    DB::statement("ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_density_check CHECK (planting_density_per_m2 > 0)");
    DB::statement("ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_grade_a_check CHECK (grade_a_percent >= 0 AND grade_a_percent <= 100)");
    DB::statement("ALTER TABLE harvest_models ADD CONSTRAINT harvest_models_survival_check CHECK (survival_rate >= 0 AND survival_rate <= 100)");

    // Loss profiles
    DB::statement("ALTER TABLE loss_profiles ADD CONSTRAINT loss_profiles_harvest_check CHECK (harvest_loss_percent >= 0 AND harvest_loss_percent <= 100)");
    DB::statement("ALTER TABLE loss_profiles ADD CONSTRAINT loss_profiles_processing_check CHECK (processing_loss_percent >= 0 AND processing_loss_percent <= 100)");
    DB::statement("ALTER TABLE loss_profiles ADD CONSTRAINT loss_profiles_packing_check CHECK (packing_loss_percent >= 0 AND packing_loss_percent <= 100)");

    // Labor norms
    DB::statement("ALTER TABLE labor_norms ADD CONSTRAINT labor_norms_hours_check CHECK (hours_per_m2 >= 0)");
    DB::statement("ALTER TABLE labor_norms ADD CONSTRAINT labor_norms_cost_check CHECK (cost_per_m2 >= 0)");

    // Irrigation norms
    DB::statement("ALTER TABLE irrigation_norms ADD CONSTRAINT irrigation_norms_unit_check CHECK (unit IN ('liters_per_m2_per_day','liters_per_plant_per_day','liters_per_bed_per_day'))");
    DB::statement("ALTER TABLE irrigation_norms ADD CONSTRAINT irrigation_norms_water_check CHECK (water_amount >= 0)");

    // Fertilizer norms
    DB::statement("ALTER TABLE fertilizer_norms ADD CONSTRAINT fertilizer_norms_amount_check CHECK (amount >= 0)");

    // Supply contracts
    DB::statement("ALTER TABLE supply_contracts ADD CONSTRAINT supply_contracts_customer_type_check CHECK (customer_type IN ('restaurant','wholesale','retail','export','other'))");
    DB::statement("ALTER TABLE supply_contracts ADD CONSTRAINT supply_contracts_unit_check CHECK (unit IN ('kg','trái','bó','thùng'))");
    DB::statement("ALTER TABLE supply_contracts ADD CONSTRAINT supply_contracts_frequency_check CHECK (frequency IN ('once','daily','weekly','monthly','seasonal'))");
    DB::statement("ALTER TABLE supply_contracts ADD CONSTRAINT supply_contracts_quantity_check CHECK (quantity > 0)");
    DB::statement("ALTER TABLE supply_contracts ADD CONSTRAINT supply_contracts_status_check CHECK (status IN ('active','completed','cancelled'))");

    // Supply demands
    DB::statement("ALTER TABLE supply_demands ADD CONSTRAINT supply_demands_unit_check CHECK (unit IN ('kg','trái','bó','thùng'))");
    DB::statement("ALTER TABLE supply_demands ADD CONSTRAINT supply_demands_frequency_check CHECK (frequency IN ('once','daily','weekly','monthly','seasonal'))");
    DB::statement("ALTER TABLE supply_demands ADD CONSTRAINT supply_demands_quantity_check CHECK (quantity > 0)");
    DB::statement("ALTER TABLE supply_demands ADD CONSTRAINT supply_demands_status_check CHECK (status IN ('pending','planned','fulfilled'))");

    // Production plans
    DB::statement("ALTER TABLE production_plans ADD CONSTRAINT production_plans_unit_check CHECK (unit IN ('kg','trái','bó','thùng'))");
    DB::statement("ALTER TABLE production_plans ADD CONSTRAINT production_plans_quantity_check CHECK (quantity > 0)");
    DB::statement("ALTER TABLE production_plans ADD CONSTRAINT production_plans_cost_check CHECK (estimated_cost IS NULL OR estimated_cost >= 0)");
    DB::statement("ALTER TABLE production_plans ADD CONSTRAINT production_plans_status_check CHECK (status IN ('draft','planning','approved','in_progress','completed','cancelled'))");
}
```

---

## Composite Index Recommendations

Based on query patterns from the plan:

| Table | Recommended Index | Columns | Purpose |
|-------|-------------------|---------|---------|
| plots | plots_farm_status_idx | (farm_id, status) | Filter plots by farm with status |
| beds | beds_plot_status_idx | (plot_id, status) | Filter beds by plot with status |
| crops | crops_group_sale_unit_idx | (group, sale_unit) | Filter by crop type and unit |
| crop_varieties | crop_varieties_crop_status_idx | (crop_id, status) | Active varieties per crop |
| supply_contracts | supply_contracts_crop_status_idx | (crop_id, status) | Active contracts by crop |
| supply_demands | supply_demands_contract_status_idx | (supply_contract_id, status) | Demands by contract and status |
| production_plans | production_plans_farm_status_idx | (farm_id, status) | Plans by farm with status |
| production_plans | production_plans_target_date_idx | (target_delivery_date) | Upcoming deliveries |

---

## Migration Order for Production

When running fresh migrations:
1. Identity (users, farms)
2. Master data (plots, beds, crops, varieties, standards, growth stages)
3. Planning norms (harvest_models, loss_profiles, labor_norms, irrigation_norms, fertilizer_norms)
4. Planning contracts (supply_contracts, supply_demands, production_plans)

---

**End of PostgreSQL Constraint/Index Checklist**