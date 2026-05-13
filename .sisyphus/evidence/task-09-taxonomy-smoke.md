# Task 9 Smoke Evidence: Reject Reason Taxonomy Normalization

## TDD Red-Green-Refactor Log

### RED Phase (2026-05-13)

Test `test_rejects_invalid_reject_reason` failed as expected:
```
Expected response status code [422] but received 201.
```
This confirmed that free-text reject reasons were being accepted without validation.

### GREEN Phase (2026-05-13)

After implementing `RejectReason` enum and validation in `HarvestLotService`:
- `test_rejects_invalid_reject_reason` now passes (returns 422 with `HARVEST_VALIDATION_FAILED`)
- `test_accepts_valid_canonical_reject_reasons` passes with canonical reasons
- `test_reject_reason_other_requires_note` passes
- Full suite passes: 292 tests / 1005 assertions

### REFACTOR Phase

- Added `VALID_REJECT_REASONS` constant to `HarvestLot` model
- Added `getRejectReasonsSummaryAttribute()` accessor for reporting
- Added `DEFORMED` reason to match existing test data

---

## Files Changed

| File | Action |
|------|--------|
| `app/Enums/RejectReason.php` | Created canonical taxonomy enum |
| `app/Models/HarvestLot.php` | Added VALID_REJECT_REASONS constant, accessor |
| `app/Services/HarvestLotService.php` | Added reject reason validation |
| `tests/Feature/HarvestLotApiTest.php` | Added 5 new tests |

---

## Canonical Reject Reason Taxonomy

```json
{
  "DISEASE_PEST_DAMAGE": "disease_pest_damage",
  "PHYSICAL_DAMAGE": "physical_damage",
  "SIZE_WEIGHT_OUT_OF_SPEC": "size_weight_out_of_spec",
  "COLOR_MATURITY_OUT_OF_SPEC": "color_maturity_out_of_spec",
  "CONTAMINATION": "contamination",
  "OVERRIPE": "overripe",
  "UNDERRIPE": "underripe",
  "DEFORMED": "deformed",
  "OTHER": "other"
}
```

---

## Smoke Test: curl Commands

### 1. Create Harvest Lot with Valid Canonical Reject Reasons

```bash
curl -X POST http://localhost:8000/api/v1/harvest-lots \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "planting_batch_id": 1,
    "harvest_date": "2026-05-15",
    "raw_quantity": 100,
    "grade_a_quantity": 70,
    "grade_b_quantity": 10,
    "grade_c_quantity": 5,
    "reject_quantity": 15,
    "reject_reasons": {
      "disease_pest_damage": 5,
      "color_maturity_out_of_spec": 4,
      "contamination": 3,
      "size_weight_out_of_spec": 2,
      "underripe": 1
    },
    "notes": "Morning harvest batch"
  }'
```

Expected response (201):
```json
{
  "data": {
    "id": 1,
    "planting_batch_id": 1,
    "raw_quantity": "100.000",
    "grade_a_quantity": "70.000",
    "grade_b_quantity": "10.000",
    "grade_c_quantity": "5.000",
    "reject_quantity": "15.000",
    "reject_reasons": {
      "disease_pest_damage": 5,
      "color_maturity_out_of_spec": 4,
      "contamination": 3,
      "size_weight_out_of_spec": 2,
      "underripe": 1
    },
    "reject_reasons_summary": [
      {"reason": "disease_pest_damage", "label": "Disease/Pest Damage", "quantity": 5},
      {"reason": "color_maturity_out_of_spec", "label": "Color/Maturity Out of Spec", "quantity": 4},
      {"reason": "contamination", "label": "Contamination", "quantity": 3},
      {"reason": "size_weight_out_of_spec", "label": "Size/Weight Out of Spec", "quantity": 2},
      {"reason": "underripe", "label": "Underripe", "quantity": 1}
    ]
  }
}
```

### 2. Reject Invalid Reject Reason (Free Text)

```bash
curl -X POST http://localhost:8000/api/v1/harvest-lots \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "planting_batch_id": 1,
    "harvest_date": "2026-05-15",
    "raw_quantity": 100,
    "grade_a_quantity": 80,
    "grade_b_quantity": 10,
    "grade_c_quantity": 5,
    "reject_quantity": 5,
    "reject_reasons": {
      "disease_pest_damage": 2,
      "random_free_text_reason": 3
    }
  }'
```

Expected response (422):
```json
{
  "error": {
    "code": "HARVEST_VALIDATION_FAILED",
    "message": "Invalid reject reasons: random_free_text_reason"
  }
}
```

### 3. Reject "Other" Without Note

```bash
curl -X POST http://localhost:8000/api/v1/harvest-lots \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "planting_batch_id": 1,
    "harvest_date": "2026-05-15",
    "raw_quantity": 100,
    "grade_a_quantity": 80,
    "grade_b_quantity": 10,
    "grade_c_quantity": 5,
    "reject_quantity": 5,
    "reject_reasons": {
      "other": 5
    },
    "notes": null
  }'
```

Expected response (422):
```json
{
  "error": {
    "code": "HARVEST_VALIDATION_FAILED",
    "message": "Reject reason \"other\" requires a note for audit trail."
  }
}
```

### 4. Accept "Other" With Note

```bash
curl -X POST http://localhost:8000/api/v1/harvest-lots \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "planting_batch_id": 1,
    "harvest_date": "2026-05-15",
    "raw_quantity": 100,
    "grade_a_quantity": 80,
    "grade_b_quantity": 10,
    "grade_c_quantity": 5,
    "reject_quantity": 5,
    "reject_reasons": {
      "other": 5
    },
    "notes": "Weather damage from unexpected storm"
  }'
```

Expected response (201):
```json
{
  "data": {
    "reject_reasons": {"other": 5},
    "notes": "Weather damage from unexpected storm"
  }
}
```

---

## Test Results

```bash
php artisan test tests/Feature/HarvestLotApiTest.php
```

```
Tests: 12 passed, 12 total
Assertions: 44 passed
```

```bash
php artisan test
```

```
Tests: 292 passed, 292 total
Assertions: 1005 passed
```

---

## Quality Reporting

The `reject_reasons_summary` accessor provides human-readable labels for aggregation:

| Reason Key | Label | Purpose |
|------------|-------|---------|
| `disease_pest_damage` | Disease/Pest Damage | Pathogen/insect damage |
| `physical_damage` | Physical Damage | Bruising, cuts |
| `size_weight_out_of_spec` | Size/Weight Out of Spec | Dimension non-compliance |
| `color_maturity_out_of_spec` | Color/Maturity Out of Spec | Appearance issues |
| `contamination` | Contamination | Foreign matter |
| `overripe` | Overripe | Over maturity |
| `underripe` | Underripe | Under maturity |
| `deformed` | Deformed | Shape abnormalities |
| `other` | Other (see notes) | Requires mandatory note |

---

## BRD Alignment

- **Quality standard + reject reason taxonomy**: Grade A/B/C/reject now maps with product standard and reject reason canonical
- **Return feedback loop**: Reject reasons link back to harvest lot for quality reporting
- **Audit trail**: "Other" category requires mandatory note for traceability
