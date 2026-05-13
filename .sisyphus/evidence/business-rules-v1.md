# Business Rules v1 - Agent D

Date: 2026-05-12
Agent: D Business Rules
Status: DRAFT - Needs Integrator Review
Source: BRD v1, BRD expanded (yeu_cau_crm_nong_nghiep_v1.md), MVP Plan

---

## 1. Season/Climate Planning Rules

### 1.1 Climate Zone Planning

| Climate Zone Factor | Impact | Default When Missing |
|---------------------|--------|---------------------|
| Growing cycle length | ±days to harvest | +10% default |
| Irrigation frequency | Water needs | 1.0x baseline |
| Pest pressure | Incident likelihood | Medium risk |
| Optimal planting window | Season dates | Use calendar avg |

**Formula Assumption Object:**
```json
{
  "climate_zone": "string|null",
  "season": "spring|summer|autumn|winter|null",
  "assumptions_applied": {
    "growing_cycle_multiplier": 1.0,
    "water_needs_multiplier": 1.0,
    "pest_risk_level": "low|medium|high"
  }
}
```

### 1.2 Harvest Phase Yield Factors

For crops with multiple harvests (dưa leo, cà chua, ớt):

| Phase | Days Range | Yield Factor | Grade A % |
|-------|------------|--------------|-----------|
| Đầu vụ (Early) | Day 35-45 | 0.40 | 60% |
| Chính vụ (Main) | Day 46-70 | 1.00 | 85% |
| Cuối vụ (Late) | Day 71-85 | 0.50 | 70% |

**Calculation:**
```
Expected harvest = base_yield × phase_factor × area_m2
```

---

## 2. Soil History & Crop Rotation Rules

### 2.1 Soil History Record Structure

```json
{
  "plot_id": "integer",
  "record_id": "integer",
  "previous_crop_family": "string|null",
  "last_batch_id": "integer|null",
  "planting_date": "date",
  "harvest_date": "date|null",
  "actual_yield_kg": "decimal|null",
  "yield_loss_rate": "decimal|null",
  "incidents_encountered": ["string"],
  "pesticide_used": ["string"],
  "fertilizer_used": ["string"],
  "rest_period_days": "integer",
  "last_soil_reform_date": "date|null",
  "notes": "text|null"
}
```

### 2.2 Crop Rotation Rules

**Warning Threshold:**
- Same crop family within 2 seasons → WARNING
- Same crop family within 1 season → BLOCK with reason
- Soil rest period < required minimum → BLOCK

**Crop Family Groups:**
| Family | Example Crops |
|--------|---------------|
| Solanaceae | Cà chua, ớt, khổ qua |
| Cucurbitaceae | Dưa leo, dưa hấu, bí |
| Brassicaceae | Bắp cải, cải xanh |
| Alliums | Hành, tỏi |
| Root vegetables | Cà rốt, củ dền |

### 2.3 Allocation Validation Flow

```
1. Check plot status
   └── If not "Đang trống" → BLOCK

2. Read soil history
   └── If rest period < minimum → BLOCK
   └── If same crop family < 2 seasons ago → WARN/BLOCK
   └── If previous incidents > threshold → WARN

3. Check capacity
   └── If available area < required → WARN insufficient

4. Create allocation or return warnings
```

---

## 3. Multi-Harvest Batch Lifecycle Rules

### 3.1 Batch State Machine

```
┌─────────────────┐
│ 1. Lên kế hoạch │
└────────┬────────┘
         ▼
┌─────────────────┐
│ 2. Chờ duyệt KH │
└────────┬────────┘
         ▼
┌─────────────────┐
│ 3. Đã duyệt     │ ←── [Reject → 14. Hủy]
└────────┬────────┘
         ▼
┌─────────────────┐
│ 4. Làm đất      │
└────────┬────────┘
         ▼
┌─────────────────┐
│ 5. Gieo/Trồng   │
└────────┬────────┘
         ▼
┌─────────────────┐
│ 6. Cây con      │
└────────┬────────┘
         ▼
┌─────────────────┐
│ 7. Sinh trưởng  │
└────────┬────────┘
         ▼
    ┌────────┐
    │ 8. Ra  │ ← Optional (skip for leaf vegetables)
    │ hoa    │
    └────┬───┘
         ▼
┌─────────────────┐
│ 9. Nuôi trái   │
└────────┬────────┘
         ▼
┌─────────────────┐
│ 10. Thu hoạch   │ ←── [Can loop back for multi-harvest]
└────────┬────────┘
         ▼
┌─────────────────┐
│ 11. Kết thúc TH │
└────────┬────────┘
         ▼
┌─────────────────┐
│ 12. Cải tạo đất │
└────────┬────────┘
         ▼
┌─────────────────┐
│ 13. Hoàn tất    │
└─────────────────┘
```

### 3.2 Actual vs Plan Reconciliation

**Plan Snapshot (immutable once approved):**
```json
{
  "plan_id": "integer",
  "planned_plants": 1000,
  "planned_area_m2": 333,
  "planned_yield_kg": 500,
  "planned_start_date": "date",
  "planned_harvest_date": "date",
  "assumptions": {...}
}
```

**Actual Record (mutable, linked to plan):**
```json
{
  "actual_id": "integer",
  "plan_id": "integer",
  "actual_plants": 980,
  "actual_area_m2": 330,
  "actual_yield_kg": 485,
  "actual_start_date": "date",
  "actual_harvest_dates": ["date1", "date2"],
  "variance_reason": "string|null"
}
```

**Reconciliation Metrics:**
- Yield variance % = (actual - planned) / planned × 100
- Timeline variance days = actual_harvest - planned_harvest
- Flag if variance > 10%

### 3.3 Multi-Harvest Aggregation

```json
{
  "batch_id": "DL-FA01-2026-001",
  "total_planned_yield": 500,
  "harvest_lots": [
    {"lot_id": 1, "date": "2026-06-15", "qty": 150, "grade_a": 127, "grade_b": 15, "grade_c": 5, "reject": 3},
    {"lot_id": 2, "date": "2026-06-17", "qty": 180, "grade_a": 153, "grade_b": 18, "grade_c": 6, "reject": 3},
    {"lot_id": 3, "date": "2026-06-19", "qty": 160, "grade_a": 136, "grade_b": 16, "grade_c": 5, "reject": 3}
  ],
  "cumulative_yield": 490,
  "remaining_plan": 10,
  "fulfillment_rate": 98.0
}
```

---

## 4. Demand Fulfillment & Shortage Rules

### 4.1 Fulfillment Status Response

```json
{
  "demand_id": "integer",
  "requested_quantity": 100,
  "requested_unit": "kg",
  "fulfillment": {
    "land": {
      "status": "insufficient",
      "required_m2": 50,
      "available_m2": 30,
      "shortage_m2": 20,
      "farms_needed": 2
    },
    "plants": {
      "status": "sufficient",
      "required": 500,
      "available": 550
    },
    "labor": {
      "status": "warning",
      "required_hours": 40,
      "available_hours": 35,
      "shortage_hours": 5
    },
    "delivery_risk": {
      "level": "moderate",
      "reason": "Harvest window overlaps with high-rain forecast",
      "recommended_advance_days": 3
    }
  },
  "shortage_warnings": [
    "Insufficient land - consider second farm",
    "Labor shortage on peak harvest days"
  ]
}
```

### 4.2 Shortage Handling Flow

```
1. Calculate total requirements
   └── From demand: quantity, frequency, start_date

2. Query available capacity per farm
   └── Available land, plants, labor

3. If total_capacity < requirement
   └── Generate shortage warnings
   └── List farms with availability

4. Manager selection (manual)
   └── Choose which farms fulfill
   └── System validates total >= requirement

5. Create production plans per farm
```

---

## 5. Quality Grade & Reject Taxonomy

### 5.1 Grade Classification Criteria

| Grade | Size | Color | Defect Rate | Texture |
|-------|------|-------|-------------|---------|
| A | Standard ±10% | Uniform | <2% | Firm |
| B | Standard ±20% | Acceptable | 2-5% | Acceptable |
| C | Outside std | Noticeable | 5-15% | Soft acceptable |
| Reject | N/A | N/A | >15% | N/A |

### 5.2 Reject Reason Taxonomy

| Code | Vietnamese | Category | Traceable? |
|------|------------|----------|------------|
| R01 | Quá size | Size | Yes |
| R02 | Non | Maturity | Yes |
| R03 | Già | Maturity | Yes |
| R04 | Sâu bệnh | Pest/Disease | Yes |
| R05 | Dập nát | Damage | Yes |
| R06 | Cong/vẹo | Shape | Yes |
| R07 | Không đều màu | Color | Yes |
| R08 | Nứt trái | Damage | Yes |
| R09 | Thối/hư | Decay | Yes |
| R10 | Dính đất/bẩn | Cleanliness | Yes |
| R11 | Sai trọng lượng | Weight | Yes |
| R12 | Không đạt cảm quan | Sensory | Yes |
| R99 | Khác | Other | Yes |

### 5.3 Quality Validation Rules

```php
// Grade breakdown must sum correctly
validate: grade_a + grade_b + grade_c + reject == raw_quantity

// Reject must have reason
validate: reject > 0 → reject_reasons.length > 0

// Deviation threshold
flag: abs(grade_breakdown - expected_grade_profile) > 5%
```

---

## 6. Override Governance Matrix

### 6.1 Override Action Matrix

| Action | Role Required | Conditions | Reason Min | Expiry | Review |
|--------|---------------|------------|------------|--------|--------|
| Harvest eligibility override | admin | Emergency | 50 chars | 24h | Required |
| Isolation bypass | admin, farm_owner | Safety cert | 30 chars | 48h | Required |
| Quality standard deviation | admin, farm_owner, farm_manager | Customer OK | 20 chars | 7 days | Optional |
| Plan modification (major) | admin, farm_owner | Business need | 50 chars | None | N/A |
| Plan modification (minor) | admin, farm_owner, farm_manager | Technical | 20 chars | None | N/A |
| Batch cancellation | admin, farm_owner | Valid reason | 50 chars | None | N/A |

### 6.2 Override Record Fields

```json
{
  "override_id": "uuid",
  "entity_type": "harvest_inspection|batch|plan",
  "entity_id": "integer",
  "action": "override_eligibility|bypass_isolation|deviate_quality",
  "original_value": "object",
  "new_value": "object",
  "reason": "string (min length varies)",
  "conditions_met": ["string"],
  "override_by_user_id": "integer",
  "override_at": "datetime",
  "farm_id": "integer",
  "expiry_at": "datetime|null",
  "review_required": "boolean",
  "review_by_user_id": "integer|null",
  "review_at": "datetime|null",
  "review_notes": "text|null",
  "auto_expire": "boolean"
}
```

### 6.3 Override Audit Requirements

Every override MUST:
1. Store original and new value
2. Record reason (minimum character length varies)
3. Link to user who performed override
4. Link to farm scope
5. Set expiry/review date when applicable
6. Be included in batch audit trail
7. Be included in compliance reports

---

## 7. Material Usage & Cost Snapshot Rules

### 7.1 Cost Categories

| Category | Description | Unit | Inventory Required? |
|----------|-------------|------|-------------------|
| seed | Seeds, seedlings | cây/kg | No (snapshots only) |
| fertilizer | Fertilizers | kg | No |
| pesticide | Chemical treatments | L/kg | No |
| biological | Biological controls | L/kg | No |
| water | Irrigation water | m³ | No |
| labor | Manpower | công/giờ | No |
| land_rent | Land rental | VND | No |
| machinery | Equipment use | giờ | No |
| other | Miscellaneous | - | No |

### 7.2 Cost Snapshot Structure

```json
{
  "cost_id": "integer",
  "batch_id": "integer",
  "category": "seed|fertilizer|pesticide|biological|water|labor|other",
  "item_name": "string",
  "quantity": "decimal",
  "unit": "string",
  "unit_cost_vnd": "decimal",
  "total_cost_vnd": "decimal",
  "recorded_at": "datetime",
  "recorded_by": "integer",
  "invoice_ref": "string|null",
  "notes": "text|null"
}
```

### 7.3 Revenue Snapshot (for Delivery)

```json
{
  "delivery_id": "integer",
  "packing_lot_id": "integer",
  "quantity_delivered": "decimal",
  "unit": "kg",
  "price_per_unit_vnd": "decimal",
  "price_snapshot_at": "datetime",
  "price_table_version": "string",
  "line_total_vnd": "decimal",
  "customer_id": "integer",
  "contract_id": "integer|null"
}
```

---

## 8. Customer/Contract Baseline Rules

### 8.1 Contract Structure

```json
{
  "contract_id": "integer",
  "contract_number": "string",
  "customer": {
    "id": "integer",
    "name": "string",
    "channel": "supermarket|wholesale|retail|export",
    "contact_name": "string",
    "contact_phone": "string",
    "delivery_address": "string"
  },
  "products": [
    {
      "crop_id": "integer",
      "variety_id": "integer|null",
      "quantity": "decimal",
      "unit": "kg|trái|bó|thùng",
      "frequency": "daily|weekly|monthly|seasonal|one_time",
      "tolerance_percent": 10,
      "start_date": "date",
      "end_date": "date|null",
      "price_reference": "price_table|floating|fixed",
      "price_value_vnd": "decimal|null"
    }
  ],
  "delivery_window": {
    "day_of_week": ["mon","tue","wed","thu","fri","sat","sun"],
    "time_range": "06:00-12:00",
    "advance_notice_hours": 24
  },
  "quality_requirements": {
    "grade_minimum": "A|B",
    "size_standard": "string",
    "packaging": "string",
    "certification_required": ["string]|null"
  },
  "status": "active|paused|completed|cancelled"
}
```

### 8.2 Demand Generation from Contract

```
For frequency = daily:
  demands = generate_daily_demands(contract.start_date, contract.end_date)

For frequency = weekly:
  demands = generate_weekly_demands(contract.start_date, contract.end_date)

Each demand includes:
  - contract_id
  - product specifications
  - quantity (with tolerance applied)
  - delivery_date
  - status (pending|planned|fulfilled|short|over)
```

---

## 9. Return Feedback Loop

### 9.1 Return Record Structure

```json
{
  "return_id": "integer",
  "return_number": "string",
  "original_delivery_id": "integer",
  "packing_lot_id": "integer",
  "source_harvest_lot_ids": ["integer"],
  "source_batch_id": "integer|null",
  "customer_id": "integer",
  "return_date": "date",
  "quantity_returned": "decimal",
  "unit": "kg",
  "return_reason_code": "R01|R02|R03|R04|R05|R06|R07|R08|R09|R10|R11|R12|R99",
  "return_reason_detail": "string",
  "photos": ["url"],
  "handler_user_id": "integer",
  "disposition": "destroy|resell|reprocess|compensate",
  "compensation_amount_vnd": "decimal|null",
  "feedback_impact": {
    "affects_quality_report": true,
    "affects_farmer_rating": true,
    "triggers_review": true
  }
}
```

### 9.2 Return → Source Traceability

```
Return
  └── Packing Lot
        └── Harvest Lot 1 (farm A, plot X)
        └── Harvest Lot 2 (farm B, plot Y)
              └── Planting Batch
                    └── Plot → Soil History
                    └── Farmer/Technician → Training flags
```

### 9.3 Return Aggregation Triggers

| Metric | Threshold | Alert To |
|--------|-----------|----------|
| Return rate > 5% | Per delivery | Farm Manager |
| Return rate > 10% | Per batch | Farm Owner |
| Same reason > 3x | Per plot | Technician |
| Same reason > 5x | Per crop variety | System Alert |

---

## 10. Open Business Questions & Release Blockers

### 10.1 Open Questions (Require Business Resolution)

| ID | Question | Impact | Owner | Priority |
|----|----------|--------|-------|----------|
| OQ-1 | Default rest period if soil history empty? | Allocation rules | Agronomist | HIGH |
| OQ-2 | Rotation rules vary by climate zone? | Allocation rules | Agronomist | MEDIUM |
| OQ-3 | Maximum reject rate before flag? | Quality alerts | QC Lead | MEDIUM |
| OQ-4 | Override approver if admin unavailable? | Emergency flow | CTO | HIGH |
| OQ-5 | Multi-farm contract harvest timing? | Demand planning | PM | MEDIUM |
| OQ-6 | Water cost calculation method? | Costing | Finance | LOW |
| OQ-7 | Labor hourly rate source? | Costing | HR | MEDIUM |

### 10.2 MVP-1 Release Blockers

| Blocker | Severity | Description | Resolution Owner |
|---------|----------|-------------|------------------|
| BLK-1 | CRITICAL | Farm scope isolation not enforced | Backend Lead |
| BLK-2 | CRITICAL | Planning formula missing norm errors | Backend Lead |
| BLK-3 | HIGH | Soil history not validated in allocation | Backend Lead |
| BLK-4 | HIGH | Override governance not implemented | Backend Lead |
| BLK-5 | MEDIUM | Quality grade validation incomplete | Backend Lead |
| BLK-6 | MEDIUM | Return feedback loop not linked | Backend Lead |

### 10.3 Questions to Escalate

1. **Water Cost**: Should we estimate water cost by area or by actual usage meter reading?
2. **Labor Rate**: Use fixed rate per công or actual payroll breakdown?
3. **Price Snapshot**: Fixed price contract vs price table - which takes priority?
4. **Return Threshold**: Is 5% return rate the right alert threshold for MVP?

---

## Annex A: Cross-Reference Table

| Business Rule | BRD Section | ERD Entity | Domain Service |
|---------------|-------------|------------|----------------|
| BR-1.x (Season/Climate) | 5.1, 11 | Farm | PlanningCalculationService |
| BR-2.x (Soil History) | 5.4, 12B.3 | Plot, SoilHistory | AllocationService |
| BR-3.x (Batch Lifecycle) | 9, 12B, 14 | PlantingBatch | PlantingBatchLifecycleService |
| BR-4.x (Fulfillment) | 11, 12, 24 | Demand, ProductionPlan | PlanningCalculationService |
| BR-5.x (Quality) | 6.2, 16.1 | HarvestLot, GradeBreakdown | HarvestService |
| BR-6.x (Override) | 23 | OverrideLog, Approval | OverrideGovernanceService |
| BR-7.x (Cost) | 8, 25 | CostRecord | CostingService |
| BR-8.x (Contract) | 11, 20 | SupplyContract, Delivery | ContractService |
| BR-9.x (Return) | 21, 26.5 | ReturnRecord | ReturnFeedbackService |

---

## Annex B: Test Scenarios Required

| Rule | Test Scenario | Expected Result |
|------|---------------|-----------------|
| BR-2.1 | Allocate batch to plot with empty soil history | System uses default rules, warns user |
| BR-2.2 | Allocate to plot with same crop family last season | System warns or blocks |
| BR-3.1 | Create 3 harvest lots for 1 batch | Cumulative yield = SUM |
| BR-3.2 | Update actual without changing plan | Plan snapshot unchanged |
| BR-5.3 | Submit harvest with grade_a + b + c + reject > raw | Validation error |
| BR-6.1 | Worker tries harvest eligibility override | 403 Forbidden |
| BR-6.2 | Admin overrides without reason | Validation error |
| BR-9.2 | Create return, verify links to harvest/batch | Links correctly traced |

---

## Files Changed

- Created: `.sisyphus/evidence/final-f4b-business-architecture.md`
- Created: `.sisyphus/evidence/business-rules-v1.md`

## Evidence Paths

- `.sisyphus/evidence/final-f4b-business-architecture.md` - Architecture guardrails summary
- `.sisyphus/evidence/business-rules-v1.md` - This file, detailed rules

## Open Risks

1. Soil history defaults not validated with agronomist
2. Override governance may conflict with RBAC matrix detail level
3. Cost snapshot without inventory may miss actual usage verification
4. Return feedback loop requires upstream harvest/batch linkage

## Next Recommended Step

1. Review business rules with domain expert (agronomist)
2. Align override governance with RBAC matrix (finer-grained permissions)
3. Create test cases for each BR-5 and BR-6 validation
4. Validate soil history defaults before implementation
