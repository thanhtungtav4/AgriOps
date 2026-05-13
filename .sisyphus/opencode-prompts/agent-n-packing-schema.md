# Agent N - Packing Schema Foundation

You are Agent N for AgriOps. Work in `/Users/macbook/Herd/ariops`.

## Goal

Implement Task 10 database/model foundation for processing records and packing lots. This unblocks the packing API and traceability graph agents.

## Context

- This is a Laravel project.
- Use `rtk` before every command.
- Current passing suite baseline: `212 tests, 651 assertions`.
- Existing production trace models:
  - `App\Models\HarvestLot`
  - `App\Models\PlantingBatch`
  - `App\Models\Farm`
  - `App\Models\User`
- Follow existing migration/model/test style.

## Ownership

You own:

- migrations for:
  - `processing_records`
  - `packing_lots`
  - `packing_lot_sources`
- `app/Models/ProcessingRecord.php`
- `app/Models/PackingLot.php`
- `app/Models/PackingLotSource.php`
- relationship additions only in:
  - `app/Models/HarvestLot.php`
  - `app/Models/Farm.php`
  - `app/Models/User.php` if needed
- `tests/Feature/PackingFoundationTest.php`
- `.sisyphus/evidence/task-10-packing-foundation.md`

Do not touch:

- routes/api.php
- API controllers
- service classes
- Filament resources
- seeders
- public QR routes/controllers
- Task 11 implementation

## Required Schema

### processing_records

- `id`
- `farm_id` required FK cascade
- `harvest_lot_id` required FK cascade
- `processed_by_user_id` nullable FK users set null
- `processed_at` timestamp required
- `input_quantity` decimal(12,3) required
- `output_quantity` decimal(12,3) required
- `loss_quantity` decimal(12,3) required default 0
- `loss_rate` decimal(8,4) required default 0
- `unit` string default `kg`
- `status` string default `completed`
- `notes` nullable text
- `metadata` nullable json
- timestamps

Indexes:

- `[farm_id, processed_at]`
- `[harvest_lot_id, processed_at]`
- `[status, processed_at]`

### packing_lots

Packing can mix harvest lots from multiple farms, so keep `farm_id` as the owning/primary farm while each source row carries its actual source farm.

- `id`
- `farm_id` nullable FK farms set null
- `created_by_user_id` nullable FK users set null
- `code` unique string
- `packed_at` timestamp required
- `status` string default `draft`
- `total_input_quantity` decimal(12,3) required default 0
- `total_output_quantity` decimal(12,3) required default 0
- `unit` string default `kg`
- `qr_code` nullable unique string
- `notes` nullable text
- `metadata` nullable json
- timestamps

Indexes:

- `[farm_id, status]`
- `[packed_at, status]`
- `[qr_code]`

### packing_lot_sources

- `id`
- `packing_lot_id` required FK cascade
- `harvest_lot_id` required FK restrict or cascade consistently with existing style
- `farm_id` required FK farms restrict/cascade consistently with existing style
- `planting_batch_id` nullable FK
- `quantity` decimal(12,3) required
- `unit` string default `kg`
- `metadata` nullable json
- timestamps

Constraints/indexes:

- unique `[packing_lot_id, harvest_lot_id]`
- index `[farm_id, harvest_lot_id]`
- index `[packing_lot_id, harvest_lot_id]`

## Model Behavior

- Fillable/casts/attributes consistent with current models.
- Constants:
  - `PackingLot::STATUSES = ['draft', 'packed', 'published', 'cancelled']`
  - `ProcessingRecord::STATUSES = ['draft', 'completed', 'cancelled']`
- Relationships:
  - `ProcessingRecord`: farm, harvestLot, processedBy
  - `PackingLot`: farm, createdBy, sources, harvestLots through sources if practical
  - `PackingLotSource`: packingLot, harvestLot, farm, plantingBatch
  - `HarvestLot`: processingRecords, packingSources

## Tests

Create feature tests covering:

- processing record stores before/after quantity and computes/stores loss fields correctly when explicit values are supplied
- packing lot can have two sources from two farms
- source row exposes actual source farm and harvest lot
- unique source constraint prevents duplicate harvest lot on the same packing lot
- model casts for dates/json/decimals behave consistently with existing project style
- relationship from harvest lot to packing sources works

Run:

```bash
rtk php artisan test tests/Feature/PackingFoundationTest.php
rtk php artisan test
```

## Evidence

Write `.sisyphus/evidence/task-10-packing-foundation.md` with:

- files changed
- schema summary
- tests run and counts
- remaining risks

