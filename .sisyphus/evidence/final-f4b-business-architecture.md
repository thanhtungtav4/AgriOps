# Business Architecture Guardrails - Agent D

Date: 2026-05-12
Agent: D Business Rules
Status: DRAFT - Needs Integrator Review
Source: BRD sections 5.1, 5.4, 8, 9, 11, 12, 12B, 14, 16.1, 21, 24, 25, 25B

---

## Executive Summary

This document captures the hard business rules that prevent technically correct but operationally wrong implementation. These guardrails must be enforced in domain services, not just validated in controllers.

---

## 1. Season/Climate Planning Rules

### Rule BR-1.1: Climate Zone Affects Planning
- Planning formula MUST consider `climate_zone` when data is available
- If climate_zone is missing, use default assumptions and return them in response
- Climate zone affects: growing cycle length, irrigation needs, harvest timing

### Rule BR-1.2: Season/Crop Cycle Awareness
- Crop cycles vary by season (đầu vụ / chính vụ / cuối vụ)
- Multi-harvest crops use different yield factors per phase:
  - Đầu vụ: typically 40% of main season yield
  - Chính vụ: 100% yield
  - Cuối vụ: typically 50% of main season yield
- Planning MUST include harvest phase in calculation

### Rule BR-1.3: Default Assumptions Required
- When norm data is incomplete, return explicit defaults in planning response
- Example: `{assumptions: {soil_quality: "average", climate_zone: "default"}}`

---

## 2. Soil History & Crop Rotation Rules

### Rule BR-2.1: Soil History Tracking Required
- Every plot MUST maintain soil history including:
  - Previous crop family
  - Last batch ID
  - Rest period duration
  - Incidents encountered
  - Pesticide/fertilizer used
- Allocation service MUST read soil history before assigning plot to batch

### Rule BR-2.2: Crop Rotation Warnings
- System MUST warn (or block) allocation if:
  - Plot recently planted same crop family (monoculture risk)
  - Rest period not yet elapsed
  - Previous batch had disease incidents
- Rotation rules configurable per crop family

### Rule BR-2.3: Plot Status Gates
| Plot Status | Can Be Allocated? | Notes |
|-------------|-------------------|-------|
| Đang trống | ✅ Yes | Default available |
| Đang chuẩn bị đất | ❌ No | In preparation |
| Đang trồng | ❌ No | Already in use |
| Đang thu hoạch | ⚠️ Conditional | Only if same batch |
| Đang nghỉ đất | ⚠️ Warning | Check rest period |
| Đang cải tạo đất | ❌ No | Under renovation |
| Tạm ngưng sử dụng | ❌ No | Inactive |

---

## 3. Multi-Harvest Batch Lifecycle Rules

### Rule BR-3.1: Batch → Multiple Harvest Lots
- One planting batch MAY produce multiple harvest lots over time
- Batch actual yield = SUM of all harvest lots linked to batch
- Each harvest lot linked to: batch, date, worker, grade breakdown

### Rule BR-3.2: Actual vs Plan Separation
- Plan records: expected dates, quantities, resources
- Actual records: real dates, quantities, resources used
- Actual records NEVER overwrite plan snapshots
- All adjustments require reason + audit trail

### Rule BR-3.3: Batch Lifecycle States (14 states)
```
1. Đang lên kế hoạch
2. Chờ duyệt kế hoạch
3. Đã duyệt — chờ làm đất
4. Đang làm đất
5. Đang gieo/trồng
6. Đang chăm sóc (cây con)
7. Đang sinh trưởng
8. Đang ra hoa
9. Đang nuôi trái / tạo củ
10. Đang thu hoạch
11. Kết thúc thu hoạch
12. Đang cải tạo đất
13. Hoàn tất
14. Hủy
```

### Rule BR-3.4: State Transition Invariants
- Cannot skip states (except optional ones like "Ra hoa")
- Transition requires: user_id, timestamp, reason (if non-standard)
- Backward transitions only allowed for: Hủy (cancel)

---

## 4. Demand Fulfillment & Shortage Rules

### Rule BR-4.1: Fulfillment Status Response
Planning response MUST include fulfillment status:
- `land_status`: sufficient | insufficient | unavailable
- `planting_status`: sufficient | insufficient | unavailable
- `labor_status`: sufficient | insufficient | unavailable
- `delivery_risk`: none | moderate | high (based on timeline)
- `shortage_warnings`: array of specific warnings

### Rule BR-4.2: Shortage Handling
- System calculates requirements, not allocates automatically
- Manager manually selects farms to fulfill demand
- System shows available capacity per farm
- If total capacity < demand, show shortage quantity

### Rule BR-4.3: Delivery Date Risk Calculation
- Risk increases when:
  - Required lead time > available time
  - Multiple farms needed but not all confirmed
  - Season is outside optimal planting window

---

## 5. Quality Grade & Reject Taxonomy

### Rule BR-5.1: Grade Classification
| Grade | Criteria | Use |
|-------|----------|-----|
| A | Meets all standards | Primary sale to supermarket |
| B | Minor deviations | Secondary channels |
| C | Major deviations but edible | Processing/subsale |
| Reject | Not saleable | Dispose or process |

### Rule BR-5.2: Reject Reason Canonical List
```
1. Quá size (Oversized)
2. Non (Underripe)
3. Già (Overripe)
4. Sâu bệnh (Pest/disease)
5. Dập nát (Bruised/damaged)
6. Cong/vẹo (Deformed)
7. Không đều màu (Uneven color)
8. Nứt trái (Cracked)
9. Thối/hư (Rotten/spoiled)
10. Dính đất/bẩn (Dirty)
11. Sai trọng lượng (Wrong weight)
12. Không đạt cảm quan (Failed sensory)
13. Khác (Other)
```

### Rule BR-5.3: Grade Breakdown Validation
- `grade_a + grade_b + grade_c + reject <= raw_quantity`
- Deviation > 5% triggers review flag
- Each reject quantity MUST have reason attached

---

## 6. Override Governance Matrix

### Rule BR-6.1: Override Requiring Approval
| Override Action | Who Can Override | Conditions | Audit Required | Review Expiry |
|-----------------|------------------|------------|----------------|---------------|
| Harvest eligibility | admin only | Emergency reason | ✅ Yes | 24 hours |
| Isolation period | admin, farm_owner | Safety inspection | ✅ Yes | 48 hours |
| Quality standard | admin, farm_owner, farm_manager | Customer requirement | ✅ Yes | 7 days |
| Plan modification | admin, farm_owner | Re-planning needed | ✅ Yes | None |
| Batch cancellation | admin, farm_owner | Valid reason | ✅ Yes | None |

### Rule BR-6.2: Override Record Structure
```json
{
  "override_id": "string",
  "action": "harvest_eligibility_override",
  "original_value": "not_approved",
  "override_value": "approved_with_conditions",
  "reason": "string (required, min 20 chars)",
  "override_by_user_id": "integer",
  "override_at": "datetime",
  "review_due_at": "datetime|null",
  "approved_by": "integer|null",
  "farm_id": "integer",
  "batch_id": "integer|null"
}
```

---

## 7. Material Usage & Cost Snapshot Rules

### Rule BR-7.1: No Full Inventory Required
- MVP does NOT implement detailed inventory ledger
- Material usage recorded as cost snapshots linked to batch
- Categories: seed, fertilizer, pesticide/biological, water, labor, other

### Rule BR-7.2: Cost Snapshot Structure
```json
{
  "batch_id": "integer",
  "category": "seed|fertilizer|pesticide|labor|water|other",
  "description": "string",
  "quantity": "decimal",
  "unit": "string",
  "unit_cost": "decimal",
  "total_cost": "decimal",
  "recorded_at": "datetime",
  "recorded_by": "integer"
}
```

### Rule BR-7.3: Price Snapshot for Revenue
- When delivery is confirmed, snapshot price at that moment
- Use price table active on delivery date
- Store: `delivery_price`, `price_snapshot_at`, `price_table_version`

---

## 8. Customer/Contract Baseline Rules

### Rule BR-8.1: Contract Data Requirements
- Customer: supermarket name, contact, channel
- Delivery cadence: daily | weekly | monthly | seasonal
- Quantity tolerance: percentage variance allowed
- Price reference: price table or fixed price
- Contract period: start_date, end_date

### Rule BR-8.2: Demand vs Contract
- Demand = calculated from contract frequency
- One contract MAY generate multiple demands
- One demand MAY be linked to multiple contracts (split fulfillment)

### Rule BR-8.3: Delivery Note Must Include
- Contract reference
- Packing lot QR code
- Expected qty vs actual qty vs received qty
- Return reason if any

---

## 9. Return Feedback Loop

### Rule BR-9.1: Return → Source Traceability
Every return MUST map to:
- Packing lot (required)
- Source harvest lot(s) (if available)
- Planting batch (if available)

### Rule BR-9.2: Return Reason Mapping
```
Return → Quality Issue → Harvest Batch → Plot → Soil History
         → Farmer → Training Need
```

### Rule BR-9.3: Feedback Aggregation
- System aggregates returns by:
  - Packing lot
  - Harvest lot
  - Batch
  - Plot
  - Crop variety
  - Customer
- Threshold alerts when return rate exceeds baseline

---

## 10. Open Business Questions & Release Blockers

### Open Questions
1. **OQ-1**: What is the default rest period if soil history is empty? (Need agronomist input)
2. **OQ-2**: Should crop rotation rules vary by climate zone?
3. **OQ-3**: What's the maximum acceptable reject rate before flagging for review?
4. **OQ-4**: Who approves override for harvest eligibility if admin is unavailable?
5. **OQ-5**: How to handle multi-farm contracts with different harvest timing?

### Release Blockers (MVP-1)
| Blocker | Severity | Status |
|---------|----------|--------|
| Farm scope isolation not enforced in API | CRITICAL | Open |
| Planning formula not tested with missing norms | HIGH | Open |
| Soil history not enforced in allocation | HIGH | Open |
| Quality grade validation missing | MEDIUM | Open |
| Override governance not implemented | MEDIUM | Open |

---

## Cross-References

| Rule | BRD Section | Plan Artifact |
|------|-------------|---------------|
| BR-1.x | 5.1, 11 | Planning Formula Spec |
| BR-2.x | 5.4, 12B.3 | DB Architecture |
| BR-3.x | 9, 12B, 14 | Lifecycle Spec |
| BR-4.x | 11, 12, 24 | Planning Formula Spec |
| BR-5.x | 6.2, 16.1 | Data Dictionary |
| BR-6.x | 23 | RBAC Matrix |
| BR-7.x | 8, 25 | Cost/Price Spec |
| BR-8.x | 11, 20 | Contract Spec |
| BR-9.x | 21, 26.5 | Report Spec |

---

## Files Changed

- Created: `.sisyphus/evidence/final-f4b-business-architecture.md`
- Created: `.sisyphus/evidence/business-rules-v1.md`

## Tests Run
- None (documentation only)

## Next Recommended Step
- Integrator review of guardrails completeness
- Alignment with planning formula implementation (Task 4)
- Conflict resolution with RBAC matrix (finer-grained than current)
