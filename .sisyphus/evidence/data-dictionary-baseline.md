# Data Dictionary Baseline - AgriOps MVP-0

**Generated:** 2026-05-12
**Agent:** B - DB/QA
**Project:** /Users/macbook/Herd/ariops
**Status:** DRAFT - Requires PostgreSQL validation

---

## 1. Identity/RBAC Module

### users

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Auth | internal | |
| name | varchar(255) | NOT NULL | - | Auth | public | Display name |
| email | varchar(255) | UNIQUE, NOT NULL | - | Auth | sensitive | Login identifier |
| email_verified_at | timestamp | NULLABLE | - | Auth | internal | |
| password | varchar(255) | NOT NULL | - | Auth | sensitive | Hashed |
| role | enum | NOT NULL | 'worker' | Auth | internal | See Role enum |
| farm_id | bigint unsigned | FK → farms.id, NULLABLE, SET NULL | - | Auth | internal | Farm membership |
| avatar_url | varchar(255) | NULLABLE | - | Auth | public | Profile image |
| last_login_at | timestamp | NULLABLE | - | Auth | internal | Audit |
| remember_token | varchar(100) | NULLABLE | - | Auth | internal | |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Role Enum:**
- `admin` - Full system access
- `farm_owner` - Farm owner
- `farm_manager` - Farm manager
- `technician` - Technical staff
- `worker` - Field worker
- `warehouse` - Warehouse staff
- `delivery` - Delivery staff

**Indexes:**
- PRIMARY (id)
- UNIQUE (email)
- INDEX (role)
- INDEX (farm_id)
- FOREIGN KEY (farm_id) → farms(id) ON DELETE SET NULL

**Relationships:**
- BelongsTo: Farm (optional)
- HasMany: personal_access_tokens (Sanctum)

---

## 2. Master Data Module

### farms

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Master | internal | |
| name | varchar(255) | NOT NULL | - | Master | public | Farm name |
| code | varchar(255) | UNIQUE, NOT NULL | - | Master | public | Business code |
| address | text | NULLABLE | - | Master | public | Physical address |
| climate_zone | varchar(255) | NULLABLE | - | Master | internal | Climate classification |
| responsible_person | varchar(255) | NULLABLE | - | Master | internal | Contact person |
| total_area_m2 | decimal(12,2) | NOT NULL | 0 | Master | internal | Total farm area |
| status | enum | NOT NULL | 'active' | Master | internal | active/inactive |
| certification | jsonb | NULLABLE | - | Master | sensitive | Certifications |
| image_url | varchar(255) | NULLABLE | - | Master | public | Farm image |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Indexes:**
- PRIMARY (id)
- UNIQUE (code)
- INDEX (status)
- INDEX (climate_zone)

**Relationships:**
- HasMany: plots
- HasMany: users
- HasMany: production_plans

---

### plots

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Master | internal | |
| farm_id | bigint unsigned | FK → farms.id, NOT NULL | - | Master | internal | Parent farm |
| code | varchar(255) | NOT NULL | - | Master | public | Plot code (unique within farm) |
| name | varchar(255) | NOT NULL | - | Master | public | Plot name |
| area_m2 | decimal(12,2) | NOT NULL | 0 | Master | internal | Plot area |
| soil_type | varchar(255) | NULLABLE | - | Master | internal | Soil classification |
| water_source | varchar(255) | NULLABLE | - | Master | internal | Water source |
| status | enum | NOT NULL | 'available' | Master | internal | See PlotStatus enum |
| current_crop_id | bigint unsigned | NULLABLE | - | Master | internal | Active crop |
| current_batch_id | bigint unsigned | NULLABLE | - | Master | internal | Active batch |
| notes | text | NULLABLE | - | Master | internal | |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**PlotStatus Enum:**
- `available` - Ready for planting
- `preparing` - Being prepared
- `planting` - Currently planting
- `harvesting` - Being harvested
- `rest` - Resting/fallow
- `restoring` - Being restored
- `suspended` - Temporarily unavailable

**Indexes:**
- PRIMARY (id)
- UNIQUE (farm_id, code) - Composite unique
- INDEX (status)
- INDEX (current_batch_id)
- FOREIGN KEY (farm_id) → farms(id) ON DELETE CASCADE

**Relationships:**
- BelongsTo: Farm
- HasMany: beds

---

### beds

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Master | internal | |
| plot_id | bigint unsigned | NULLABLE, INDEX | - | Master | internal | Parent plot |
| code | varchar(255) | NOT NULL | - | Master | public | Bed code (unique within plot) |
| length_m | decimal(8,2) | NOT NULL | 0 | Master | internal | Bed length |
| width_m | decimal(8,2) | NOT NULL | 0 | Master | internal | Bed width |
| area_m2 | decimal(10,2) | NOT NULL | 0 | Master | internal | Calculated area |
| expected_plants | int | NOT NULL | 0 | Master | internal | Planting capacity |
| status | enum | NOT NULL | 'available' | Master | internal | See BedStatus enum |
| notes | text | NULLABLE | - | Master | internal | |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**BedStatus Enum:**
- `available` - Ready for planting
- `preparing` - Being prepared
- `planting` - Currently planting
- `growing` - Growing phase
- `harvesting` - Being harvested
- `rest` - Resting

**Indexes:**
- PRIMARY (id)
- UNIQUE (plot_id, code) - Composite unique
- INDEX (plot_id)

**Relationships:**
- BelongsTo: Plot (optional)

---

### crops

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Master | internal | |
| name | varchar(255) | NOT NULL | - | Master | public | Crop name |
| group | enum | NOT NULL | 'leafy' | Master | internal | Crop group |
| sale_unit | enum | NOT NULL | 'kg' | Master | internal | Sales unit |
| production_unit | enum | NOT NULL | 'cây' | Master | internal | Production unit |
| can_harvest_multiple | boolean | NOT NULL | false | Master | internal | Multi-harvest flag |
| has_multiple_cycles | boolean | NOT NULL | false | Master | internal | Multi-cycle flag |
| avg_growth_days | int | NOT NULL | 0 | Master | internal | Average growth period |
| harvest_exploitation_days | int | NOT NULL | 0 | Master | internal | Harvest window |
| rest_days | int | NOT NULL | 0 | Master | internal | Rest period |
| sale_price_per_unit | decimal(14,2) | NULLABLE | - | Master | public | Base price |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Group Enum:**
- `leafy` - Leafy vegetables
- `fruit` - Fruit vegetables
- `root` - Root vegetables
- `fruit_tree` - Fruit trees

**SaleUnit Enum:**
- `kg` - Kilogram
- `trái` - Pieces
- `bó` - Bundles
- `thùng` - Boxes

**ProductionUnit Enum:**
- `cây` - Plants
- `m2` - Square meters
- `luống` - Beds

**Indexes:**
- PRIMARY (id)
- INDEX (group)

**Relationships:**
- HasMany: crop_varieties
- HasMany: product_standards
- HasMany: growth_stages
- HasMany: harvest_models
- HasMany: loss_profiles
- HasMany: labor_norms
- HasMany: irrigation_norms
- HasMany: fertilizer_norms
- HasMany: supply_contracts
- HasMany: supply_demands

---

### crop_varieties

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Master | internal | |
| crop_id | bigint unsigned | FK → crops.id, NOT NULL | - | Master | internal | Parent crop |
| name | varchar(255) | NOT NULL | - | Master | public | Variety name |
| code | varchar(255) | NULLABLE | - | Master | public | Variety code |
| supplier | varchar(255) | NULLABLE | - | Master | internal | Seed supplier |
| description | text | NULLABLE | - | Master | public | Description |
| avg_growth_days | int | NOT NULL | 0 | Master | internal | Growth period |
| avg_yield_per_plant | decimal(12,4) | NULLABLE | - | Master | internal | Expected yield |
| planting_density_per_m2 | decimal(10,2) | NULLABLE | - | Master | internal | Spacing |
| disease_resistance | varchar(255) | NULLABLE | - | Master | internal | Resistance info |
| suitable_season | varchar(255) | NULLABLE | - | Master | internal | Best season |
| suitable_climate_zone | varchar(255) | NULLABLE | - | Master | internal | Climate zones |
| care_requirements | text | NULLABLE | - | Master | internal | Care notes |
| image_url | varchar(255) | NULLABLE | - | Master | public | Variety image |
| status | enum | NOT NULL | 'active' | Master | internal | active/inactive |
| notes | text | NULLABLE | - | Master | internal | |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Indexes:**
- PRIMARY (id)
- UNIQUE (crop_id, code) - Composite unique (code nullable)
- INDEX (status)
- FOREIGN KEY (crop_id) → crops(id) ON DELETE CASCADE

**Relationships:**
- BelongsTo: Crop

---

### growth_stages

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Master | internal | |
| crop_id | bigint unsigned | FK → crops.id, NOT NULL | - | Master | internal | Parent crop |
| variety_id | bigint unsigned | FK → crop_varieties.id, NULLABLE | - | Master | internal | Specific variety |
| name | varchar(255) | NOT NULL | - | Master | public | Stage name |
| code | varchar(255) | NULLABLE | - | Master | public | Stage code |
| order | smallint unsigned | NOT NULL | 0 | Master | internal | Execution order |
| duration_days | smallint unsigned | NOT NULL | 0 | Master | internal | Duration |
| description | text | NULLABLE | - | Master | public | Stage description |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Indexes:**
- PRIMARY (id)
- INDEX (crop_id, order)
- FOREIGN KEY (crop_id) → crops(id) ON DELETE CASCADE
- FOREIGN KEY (variety_id) → crop_varieties(id) ON DELETE CASCADE

**Relationships:**
- BelongsTo: Crop
- BelongsTo: CropVariety (optional)

---

### product_standards

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Master | internal | |
| crop_id | bigint unsigned | FK → crops.id, NOT NULL | - | Master | internal | Parent crop |
| variety_id | bigint unsigned | FK → crop_varieties.id, NULLABLE, SET NULL | - | Master | internal | Specific variety |
| name | varchar(255) | NOT NULL | - | Master | public | Standard name |
| code | varchar(255) | NULLABLE | - | Master | public | Standard code |
| specifications | text | NULLABLE | - | Master | internal | Spec details |
| allowed_defect_percent | decimal(5,2) | NOT NULL | 0 | Master | internal | Tolerance |
| packing_spec | varchar(255) | NULLABLE | - | Master | internal | Packing requirements |
| grade | enum | NOT NULL | 'A' | Master | internal | Default grade |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Grade Enum:**
- `A` - Grade A (premium)
- `B` - Grade B (standard)
- `C` - Grade C (economy)
- `reject` - Reject grade

**Indexes:**
- PRIMARY (id)
- INDEX (crop_id)
- FOREIGN KEY (crop_id) → crops(id) ON DELETE CASCADE
- FOREIGN KEY (variety_id) → crop_varieties(id) ON DELETE SET NULL

---

### harvest_models

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Planning | internal | |
| crop_id | bigint unsigned | FK → crops.id, NOT NULL | - | Planning | internal | Parent crop |
| variety_id | bigint unsigned | FK → crop_varieties.id, NULLABLE | - | Planning | internal | Specific variety |
| harvest_type | enum | NOT NULL | 'single' | Planning | internal | single/multiple |
| avg_yield_per_plant | decimal(12,4) | NOT NULL | - | Planning | internal | Expected yield |
| min_yield_per_plant | decimal(12,4) | NULLABLE | - | Planning | internal | Min yield |
| max_yield_per_plant | decimal(12,4) | NULLABLE | - | Planning | internal | Max yield |
| planting_density_per_m2 | decimal(10,2) | NOT NULL | - | Planning | internal | Spacing |
| survival_rate | decimal(5,2) | NOT NULL | 100 | Planning | internal | Survival % |
| grade_a_percent | decimal(5,2) | NOT NULL | 100 | Planning | internal | Grade A % |
| grade_b_percent | decimal(5,2) | NOT NULL | 0 | Planning | internal | Grade B % |
| grade_c_percent | decimal(5,2) | NOT NULL | 0 | Planning | internal | Grade C % |
| reject_percent | decimal(5,2) | NOT NULL | 0 | Planning | internal | Reject % |
| days_to_first_harvest | smallint unsigned | NOT NULL | 0 | Planning | internal | Days to harvest |
| harvest_duration_days | smallint unsigned | NOT NULL | 0 | Planning | internal | Harvest window |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**HarvestType Enum:**
- `single` - Single harvest
- `multiple` - Multiple harvests

**Indexes:**
- PRIMARY (id)
- INDEX (crop_id, variety_id)
- FOREIGN KEY (crop_id) → crops(id) ON DELETE CASCADE
- FOREIGN KEY (variety_id) → crop_varieties(id) ON DELETE CASCADE

---

### loss_profiles

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Planning | internal | |
| crop_id | bigint unsigned | FK → crops.id, NOT NULL | - | Planning | internal | Parent crop |
| variety_id | bigint unsigned | FK → crop_varieties.id, NULLABLE | - | Planning | internal | Specific variety |
| harvest_loss_percent | decimal(5,2) | NOT NULL | 0 | Planning | internal | Harvest loss |
| processing_loss_percent | decimal(5,2) | NOT NULL | 0 | Planning | internal | Processing loss |
| packing_loss_percent | decimal(5,2) | NOT NULL | 0 | Planning | internal | Packing loss |
| non_grade_a_percent | decimal(5,2) | NOT NULL | 0 | Planning | internal | Non-grade-A % |
| reject_percent | decimal(5,2) | NOT NULL | 0 | Planning | internal | Reject % |
| notes | text | NULLABLE | - | Planning | internal | |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Indexes:**
- PRIMARY (id)
- INDEX (crop_id, variety_id)
- FOREIGN KEY (crop_id) → crops(id) ON DELETE CASCADE
- FOREIGN KEY (variety_id) → crop_varieties(id) ON DELETE CASCADE

---

### labor_norms

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Planning | internal | |
| crop_id | bigint unsigned | FK → crops.id, NOT NULL | - | Planning | internal | Parent crop |
| variety_id | bigint unsigned | FK → crop_varieties.id, NULLABLE | - | Planning | internal | Specific variety |
| hours_per_m2 | decimal(10,4) | NOT NULL | 0 | Planning | internal | Labor hours |
| cost_per_m2 | decimal(14,2) | NOT NULL | 0 | Planning | internal | Labor cost |
| notes | text | NULLABLE | - | Planning | internal | |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Indexes:**
- PRIMARY (id)
- INDEX (crop_id, variety_id)
- FOREIGN KEY (crop_id) → crops(id) ON DELETE CASCADE
- FOREIGN KEY (variety_id) → crop_varieties(id) ON DELETE CASCADE

---

### irrigation_norms

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Planning | internal | |
| crop_id | bigint unsigned | FK → crops.id, NOT NULL | - | Planning | internal | Parent crop |
| variety_id | bigint unsigned | FK → crop_varieties.id, NULLABLE | - | Planning | internal | Specific variety |
| growth_stage_id | bigint unsigned | FK → growth_stages.id, NULLABLE | - | Planning | internal | Stage-specific |
| frequency | varchar(255) | NULLABLE | - | Planning | internal | Irrigation frequency |
| water_amount | decimal(12,4) | NOT NULL | 0 | Planning | internal | Water volume |
| unit | enum | NOT NULL | 'liters_per_m2_per_day' | Planning | internal | Water unit |
| timing | varchar(255) | NULLABLE | - | Planning | internal | Timing info |
| requires_actual_log | boolean | NOT NULL | false | Planning | internal | Log requirement |
| notes | text | NULLABLE | - | Planning | internal | |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Unit Enum:**
- `liters_per_m2_per_day`
- `liters_per_plant_per_day`
- `liters_per_bed_per_day`

**Indexes:**
- PRIMARY (id)
- INDEX (crop_id, variety_id)
- FOREIGN KEY (crop_id) → crops(id) ON DELETE CASCADE
- FOREIGN KEY (variety_id) → crop_varieties(id) ON DELETE CASCADE
- FOREIGN KEY (growth_stage_id) → growth_stages(id) ON DELETE CASCADE

---

### fertilizer_norms

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Planning | internal | |
| crop_id | bigint unsigned | FK → crops.id, NOT NULL | - | Planning | internal | Parent crop |
| variety_id | bigint unsigned | FK → crop_varieties.id, NULLABLE | - | Planning | internal | Specific variety |
| growth_stage_id | bigint unsigned | FK → growth_stages.id, NULLABLE | - | Planning | internal | Stage-specific |
| fertilizer_name | varchar(255) | NOT NULL | - | Planning | internal | Fertilizer name |
| amount | decimal(12,4) | NOT NULL | 0 | Planning | internal | Application amount |
| unit | varchar(255) | NOT NULL | 'kg' | Planning | internal | Application unit |
| application_day_range | varchar(255) | NULLABLE | - | Planning | internal | Timing range |
| technical_notes | text | NULLABLE | - | Planning | internal | |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Indexes:**
- PRIMARY (id)
- INDEX (crop_id, variety_id)
- FOREIGN KEY (crop_id) → crops(id) ON DELETE CASCADE
- FOREIGN KEY (variety_id) → crop_varieties(id) ON DELETE CASCADE
- FOREIGN KEY (growth_stage_id) → growth_stages(id) ON DELETE CASCADE

---

## 3. Planning Module

### supply_contracts

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Planning | internal | |
| customer_name | varchar(255) | NOT NULL | - | Planning | public | Customer name |
| customer_type | enum | NOT NULL | 'other' | Planning | internal | Customer segment |
| crop_id | bigint unsigned | FK → crops.id, NOT NULL | - | Planning | internal | Contract crop |
| quantity | decimal(14,2) | NOT NULL | - | Planning | internal | Contract quantity |
| unit | enum | NOT NULL | - | Planning | internal | Quantity unit |
| frequency | enum | NOT NULL | 'once' | Planning | internal | Delivery frequency |
| start_date | date | NOT NULL | - | Planning | internal | Contract start |
| end_date | date | NULLABLE | - | Planning | internal | Contract end |
| product_standard_id | bigint unsigned | FK → product_standards.id, NULLABLE, SET NULL | - | Planning | internal | Quality standard |
| status | enum | NOT NULL | 'active' | Planning | internal | Contract status |
| notes | text | NULLABLE | - | Planning | internal | |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**CustomerType Enum:**
- `restaurant`
- `wholesale`
- `retail`
- `export`
- `other`

**Frequency Enum:**
- `once` - One-time delivery
- `daily` - Daily delivery
- `weekly` - Weekly delivery
- `monthly` - Monthly delivery
- `seasonal` - Seasonal delivery

**Status Enum:**
- `active`
- `completed`
- `cancelled`

**Indexes:**
- PRIMARY (id)
- INDEX (crop_id)
- FOREIGN KEY (crop_id) → crops(id) ON DELETE CASCADE
- FOREIGN KEY (product_standard_id) → product_standards(id) ON DELETE SET NULL

---

### supply_demands

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Planning | internal | |
| supply_contract_id | bigint unsigned | FK → supply_contracts.id, NULLABLE | - | Planning | internal | Parent contract |
| crop_id | bigint unsigned | FK → crops.id, NOT NULL | - | Planning | internal | Demand crop |
| quantity | decimal(14,2) | NOT NULL | - | Planning | internal | Demand quantity |
| unit | enum | NOT NULL | - | Planning | internal | Quantity unit |
| frequency | enum | NOT NULL | 'once' | Planning | internal | Delivery frequency |
| target_date | date | NOT NULL | - | Planning | internal | Target delivery |
| status | enum | NOT NULL | 'pending' | Planning | internal | Demand status |
| notes | text | NULLABLE | - | Planning | internal | |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Unit Enum:**
- `kg`
- `trái`
- `bó`
- `thùng`

**Frequency Enum:**
- `once`, `daily`, `weekly`, `monthly`, `seasonal`

**Status Enum:**
- `pending`
- `planned`
- `fulfilled`

**Indexes:**
- PRIMARY (id)
- INDEX (supply_contract_id)
- INDEX (crop_id)
- INDEX (status)
- FOREIGN KEY (supply_contract_id) → supply_contracts(id) ON DELETE CASCADE
- FOREIGN KEY (crop_id) → crops(id) ON DELETE CASCADE

---

### production_plans

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Planning | internal | |
| supply_contract_id | bigint unsigned | FK → supply_contracts.id, NULLABLE | - | Planning | internal | Parent contract |
| supply_demand_id | bigint unsigned | FK → supply_demands.id, NULLABLE, SET NULL | - | Planning | internal | Parent demand |
| crop_id | bigint unsigned | FK → crops.id, NOT NULL | - | Planning | internal | Plan crop |
| variety_id | bigint unsigned | FK → crop_varieties.id, NULLABLE, SET NULL | - | Planning | internal | Plan variety |
| farm_id | bigint unsigned | FK → farms.id, NOT NULL | - | Planning | internal | Target farm |
| quantity | decimal(14,2) | NOT NULL | - | Planning | internal | Planned quantity |
| unit | enum | NOT NULL | - | Planning | internal | Quantity unit |
| target_delivery_date | date | NOT NULL | - | Planning | internal | Target date |
| estimated_cost | decimal(14,2) | NULLABLE | - | Planning | sensitive | Cost estimate |
| estimated_revenue | decimal(14,2) | NULLABLE | - | Planning | sensitive | Revenue estimate |
| margin_percent | decimal(8,2) | NULLABLE | - | Planning | sensitive | Margin estimate |
| status | enum | NOT NULL | 'draft' | Planning | internal | Plan status |
| notes | text | NULLABLE | - | Planning | internal | |
| created_at | timestamp | NOT NULL | - | All | public | |
| updated_at | timestamp | NOT NULL | - | All | public | |

**Status Enum:**
- `draft` - Initial draft
- `planning` - Being planned
- `approved` - Approved
- `in_progress` - In execution
- `completed` - Completed
- `cancelled` - Cancelled

**Indexes:**
- PRIMARY (id)
- INDEX (crop_id)
- INDEX (farm_id)
- INDEX (supply_contract_id)
- FOREIGN KEY (supply_contract_id) → supply_contracts(id) ON DELETE CASCADE
- FOREIGN KEY (supply_demand_id) → supply_demands(id) ON DELETE SET NULL
- FOREIGN KEY (crop_id) → crops(id) ON DELETE CASCADE
- FOREIGN KEY (variety_id) → crop_varieties(id) ON DELETE SET NULL
- FOREIGN KEY (farm_id) → farms(id) ON DELETE CASCADE

---

## 4. Platform Tables (Laravel)

### personal_access_tokens

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Auth | internal | |
| tokenable_type | varchar(255) | NOT NULL | - | Auth | internal | Model type |
| tokenable_id | bigint unsigned | NOT NULL | - | Auth | internal | Model ID |
| name | varchar(255) | NOT NULL | - | Auth | internal | Token name |
| token | varchar(64) | UNIQUE, NOT NULL | - | Auth | sensitive | Hashed token |
| abilities | text | NULLABLE | - | Auth | internal | Permissions |
| last_used_at | timestamp | NULLABLE | - | Auth | internal | Last use |
| expires_at | timestamp | NULLABLE | - | Auth | internal | Expiry |
| created_at | timestamp | NOT NULL | - | All | internal | |
| updated_at | timestamp | NOT NULL | - | All | internal | |

### sessions

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | varchar(255) | PRIMARY | - | Auth | internal | |
| user_id | bigint unsigned | NULLABLE, INDEX | - | Auth | internal | User reference |
| ip_address | varchar(45) | NULLABLE | - | Auth | internal | Client IP |
| user_agent | text | NULLABLE | - | Auth | internal | User agent |
| payload | longtext | NOT NULL | - | Auth | internal | Session data |
| last_activity | int | INDEX | - | Auth | internal | Activity timestamp |

### password_reset_tokens

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| email | varchar(255) | PRIMARY | - | Auth | internal | |
| token | varchar(255) | NOT NULL | - | Auth | sensitive | Reset token |
| created_at | timestamp | NULLABLE | - | Auth | internal | Creation time |

### cache

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| key | varchar(255) | PRIMARY | - | Platform | internal | Cache key |
| value | blob | NULLABLE | - | Platform | internal | Cache value |
| expiration | int | INDEX | - | Platform | internal | Expiry timestamp |

### jobs

| Column | Type | Constraints | Default | Owner | Privacy | Notes |
|--------|------|-------------|---------|-------|---------|-------|
| id | bigint unsigned | PK, AUTO_INCREMENT | - | Platform | internal | |
| queue | varchar(255) | NOT NULL | - | Platform | internal | Queue name |
| payload | longtext | NOT NULL | - | Platform | internal | Job data |
| attempts | unsignedTinyInt | NOT NULL | - | Platform | internal | Attempt count |
| reserved_at | int unsigned | NULLABLE | - | Platform | internal | Reserved time |
| available_at | int unsigned | NOT NULL | - | Platform | internal | Available time |
| created_at | int unsigned | NOT NULL | - | Platform | internal | Creation time |

---

## 5. Missing Tables (Planned)

The following tables are referenced in BRD but NOT YET MIGRATED:

### Production Lifecycle (MVP-1 scope)
- [ ] `planting_batches` - Batch lifecycle management (14 states per BRD 12B.2)
- [ ] `planting_batch_allocations` - Many-to-many batch ↔ plot/bed
- [ ] `work_tasks` - Task generation and assignment
- [ ] `farming_logs` - Actual work logging with photo support
- [ ] `incidents` - Pest/disease incidents
- [ ] `chemical_usages` - Chemical/biological application tracking
- [ ] `pre_harvest_inspections` - Inspection checklist before harvest
- [ ] `harvest_lots` - Harvest records with grade breakdown
- [ ] `processing_records` - Processing before packing
- [ ] `packing_lots` - Packing with multiple harvest sources
- [ ] `packing_lot_sources` - Many-to-many packing lot ↔ harvest lot

### Commercial/Reporting (MVP-2 scope)
- [ ] `deliveries` - Delivery notes
- [ ] `returns` - Return records
- [ ] `cost_records` - Cost tracking per batch
- [ ] `alerts` - Alert rules and notifications

### Platform (Future)
- [ ] `media` - Media file storage
- [ ] `audit_logs` - Audit trail for sensitive actions
- [ ] `domain_events` - Event sourcing
- [ ] `ai_suggestions` - AI recommendations waiting approval

---

## 6. Privacy Classification Legend

| Classification | Description |
|----------------|-------------|
| public | Can appear in QR/traceability public pages |
| internal | Only visible to authenticated users within farm |
| sensitive | Requires additional authorization to view |
| restricted | Only visible to admin/approver roles |

---

## 7. Validation Requirements

### Required Fields (NOT NULL)
- `farms.name, code`
- `plots.farm_id, code, name, area_m2, status`
- `beds.code`
- `crops.name, group, sale_unit, production_unit`
- `crop_varieties.crop_id, name`
- `growth_stages.crop_id, name, order`
- `product_standards.crop_id, name, grade`
- `harvest_models.crop_id, harvest_type, avg_yield_per_plant, planting_density_per_m2`
- `loss_profiles.crop_id`
- `labor_norms.crop_id`
- `irrigation_norms.crop_id, water_amount, unit`
- `fertilizer_norms.crop_id, fertilizer_name, amount, unit`
- `supply_contracts.customer_name, crop_id, quantity, unit, start_date`
- `supply_demands.crop_id, quantity, unit, target_date`
- `production_plans.crop_id, farm_id, quantity, unit, target_delivery_date`
- `users.name, email, password, role`

### Quantity Fields (Must have unit)
- `supply_contracts.quantity` → unit enum
- `supply_demands.quantity` → unit enum
- `production_plans.quantity` → unit enum

### Domain Enums (Must use canonical values)
- `farms.status`: active, inactive
- `plots.status`: available, preparing, planting, harvesting, rest, restoring, suspended
- `beds.status`: available, preparing, planting, growing, harvesting, rest
- `crops.group`: leafy, fruit, root, fruit_tree
- `crops.sale_unit`: kg, trái, bó, thùng
- `crops.production_unit`: cây, m2, luống
- `users.role`: admin, farm_owner, farm_manager, technician, worker, warehouse, delivery

---

**End of Data Dictionary Baseline**