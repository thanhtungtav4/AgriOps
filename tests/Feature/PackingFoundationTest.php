<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\HarvestLot;
use App\Models\PackingLot;
use App\Models\PackingLotSource;
use App\Models\PlantingBatch;
use App\Models\ProcessingRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PackingFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);
    }

    protected function createFarm(string $code = 'TF01'): Farm
    {
        return Farm::create([
            'name' => 'Test Farm',
            'code' => $code,
            'total_area_m2' => 5000,
        ]);
    }

    protected function createHarvestLot(Farm $farm): HarvestLot
    {
        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 50,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
            'status' => 'planned',
        ]);

        return HarvestLot::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'code' => 'HL-' . uniqid(),
            'harvest_date' => now()->toDateString(),
            'raw_quantity' => 50,
            'unit' => 'kg',
            'status' => 'available',
        ]);
    }

    public function test_processing_record_stores_input_and_output_quantities(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $harvestLot = $this->createHarvestLot($farm);

        $record = ProcessingRecord::create([
            'farm_id' => $farm->id,
            'harvest_lot_id' => $harvestLot->id,
            'processed_at' => now(),
            'input_quantity' => 100.500,
            'output_quantity' => 95.250,
            'loss_quantity' => 5.250,
            'loss_rate' => 0.0525,
            'status' => 'completed',
        ]);

        $this->assertNotNull($record->id);
        $this->assertEquals(100.500, $record->input_quantity);
        $this->assertEquals(95.250, $record->output_quantity);
        $this->assertEquals(5.250, $record->loss_quantity);
        $this->assertEquals(0.0525, $record->loss_rate);
        $this->assertEquals('completed', $record->status);
        $this->assertEquals('kg', $record->unit);
    }

    public function test_processing_record_computes_and_stores_loss_fields(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $harvestLot = $this->createHarvestLot($farm);

        $record = ProcessingRecord::create([
            'farm_id' => $farm->id,
            'harvest_lot_id' => $harvestLot->id,
            'processed_at' => now(),
            'input_quantity' => 100.000,
            'output_quantity' => 90.000,
            'loss_quantity' => 10.000,
            'loss_rate' => 0.1000,
            'status' => 'completed',
        ]);

        $record->refresh();
        $this->assertEquals(10.000, $record->loss_quantity);
        $this->assertEquals(0.1000, $record->loss_rate);
        $this->assertEquals(
            $record->input_quantity - $record->output_quantity,
            $record->loss_quantity
        );
    }

    public function test_processing_record_has_required_relationships(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $harvestLot = $this->createHarvestLot($farm);
        $user = User::factory()->create(['role' => User::ROLE_WORKER]);

        $record = ProcessingRecord::create([
            'farm_id' => $farm->id,
            'harvest_lot_id' => $harvestLot->id,
            'processed_by_user_id' => $user->id,
            'processed_at' => now(),
            'input_quantity' => 100,
            'output_quantity' => 95,
            'status' => 'completed',
        ]);

        $this->assertNotNull($record->farm);
        $this->assertEquals($farm->id, $record->farm->id);

        $this->assertNotNull($record->harvestLot);
        $this->assertEquals($harvestLot->id, $record->harvestLot->id);

        $this->assertNotNull($record->processedBy);
        $this->assertEquals($user->id, $record->processedBy->id);
    }

    public function test_packing_lot_can_have_two_sources_from_two_farms(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm1 = $this->createFarm('FARM01');
        $farm2 = $this->createFarm('FARM02');

        $harvestLot1 = $this->createHarvestLot($farm1);
        $harvestLot2 = $this->createHarvestLot($farm2);

        $packingLot = PackingLot::create([
            'code' => 'PL-' . uniqid(),
            'packed_at' => now(),
            'status' => 'draft',
        ]);

        $source1 = PackingLotSource::create([
            'packing_lot_id' => $packingLot->id,
            'harvest_lot_id' => $harvestLot1->id,
            'farm_id' => $farm1->id,
            'quantity' => 25.000,
            'unit' => 'kg',
        ]);

        $source2 = PackingLotSource::create([
            'packing_lot_id' => $packingLot->id,
            'harvest_lot_id' => $harvestLot2->id,
            'farm_id' => $farm2->id,
            'quantity' => 30.000,
            'unit' => 'kg',
        ]);

        $packingLot->refresh();
        $this->assertCount(2, $packingLot->sources);
        $this->assertTrue($packingLot->sources->contains($source1));
        $this->assertTrue($packingLot->sources->contains($source2));
    }

    public function test_source_row_exposes_actual_source_farm_and_harvest_lot(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm1 = $this->createFarm('FARM01');
        $farm2 = $this->createFarm('FARM02');

        $harvestLot1 = $this->createHarvestLot($farm1);
        $harvestLot2 = $this->createHarvestLot($farm2);

        $packingLot = PackingLot::create([
            'code' => 'PL-' . uniqid(),
            'packed_at' => now(),
            'status' => 'draft',
        ]);

        $source1 = PackingLotSource::create([
            'packing_lot_id' => $packingLot->id,
            'harvest_lot_id' => $harvestLot1->id,
            'farm_id' => $farm1->id,
            'quantity' => 25.000,
            'unit' => 'kg',
        ]);

        $source2 = PackingLotSource::create([
            'packing_lot_id' => $packingLot->id,
            'harvest_lot_id' => $harvestLot2->id,
            'farm_id' => $farm2->id,
            'quantity' => 30.000,
            'unit' => 'kg',
        ]);

        $this->assertEquals($farm1->id, $source1->farm->id);
        $this->assertEquals('FARM01', $source1->farm->code);
        $this->assertEquals($harvestLot1->id, $source1->harvestLot->id);

        $this->assertEquals($farm2->id, $source2->farm->id);
        $this->assertEquals('FARM02', $source2->farm->code);
        $this->assertEquals($harvestLot2->id, $source2->harvestLot->id);
    }

    public function test_unique_source_constraint_prevents_duplicate_harvest_lot_on_same_packing_lot(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $harvestLot = $this->createHarvestLot($farm);

        $packingLot = PackingLot::create([
            'code' => 'PL-' . uniqid(),
            'packed_at' => now(),
            'status' => 'draft',
        ]);

        PackingLotSource::create([
            'packing_lot_id' => $packingLot->id,
            'harvest_lot_id' => $harvestLot->id,
            'farm_id' => $farm->id,
            'quantity' => 25.000,
            'unit' => 'kg',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        PackingLotSource::create([
            'packing_lot_id' => $packingLot->id,
            'harvest_lot_id' => $harvestLot->id,
            'farm_id' => $farm->id,
            'quantity' => 30.000,
            'unit' => 'kg',
        ]);
    }

    public function test_model_casts_for_dates_json_decimals_behave_consistently(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $harvestLot = $this->createHarvestLot($farm);

        $record = ProcessingRecord::create([
            'farm_id' => $farm->id,
            'harvest_lot_id' => $harvestLot->id,
            'processed_at' => '2026-05-13 14:30:00',
            'input_quantity' => 100.123,
            'output_quantity' => 95.456,
            'loss_quantity' => 4.667,
            'loss_rate' => 0.0466,
            'metadata' => ['processor' => 'line-1', 'shift' => 'morning'],
            'status' => 'completed',
        ]);

        $record->refresh();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $record->processed_at);
        $this->assertEquals('2026-05-13', $record->processed_at->toDateString());
        $this->assertIsArray($record->metadata);
        $this->assertEquals('line-1', $record->metadata['processor']);
    }

    public function test_relationship_from_harvest_lot_to_packing_sources_works(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_FARM_MANAGER]));

        $farm = $this->createFarm();
        $harvestLot = $this->createHarvestLot($farm);

        $packingLot1 = PackingLot::create([
            'code' => 'PL-' . uniqid(),
            'packed_at' => now(),
            'status' => 'draft',
        ]);

        $packingLot2 = PackingLot::create([
            'code' => 'PL-' . uniqid(),
            'packed_at' => now(),
            'status' => 'draft',
        ]);

        PackingLotSource::create([
            'packing_lot_id' => $packingLot1->id,
            'harvest_lot_id' => $harvestLot->id,
            'farm_id' => $farm->id,
            'quantity' => 10.000,
            'unit' => 'kg',
        ]);

        PackingLotSource::create([
            'packing_lot_id' => $packingLot2->id,
            'harvest_lot_id' => $harvestLot->id,
            'farm_id' => $farm->id,
            'quantity' => 15.000,
            'unit' => 'kg',
        ]);

        $harvestLot->refresh();
        $this->assertCount(2, $harvestLot->packingSources);
    }

    public function test_processing_record_status_constant(): void
    {
        $this->assertEquals(['draft', 'completed', 'cancelled'], ProcessingRecord::STATUSES);
    }

    public function test_packing_lot_status_constant(): void
    {
        $this->assertEquals(['draft', 'packed', 'published', 'cancelled'], PackingLot::STATUSES);
    }

    public function test_packing_lot_has_sources_relationship(): void
    {
        $packingLot = new PackingLot();
        $this->assertTrue(method_exists($packingLot, 'sources'));
    }

    public function test_packing_lot_has_harvest_lots_relationship(): void
    {
        $packingLot = new PackingLot();
        $this->assertTrue(method_exists($packingLot, 'harvestLots'));
    }

    public function test_processing_record_model_has_relationships(): void
    {
        $record = new ProcessingRecord();
        $this->assertTrue(method_exists($record, 'farm'));
        $this->assertTrue(method_exists($record, 'harvestLot'));
        $this->assertTrue(method_exists($record, 'processedBy'));
    }

    public function test_packing_lot_source_model_has_relationships(): void
    {
        $source = new PackingLotSource();
        $this->assertTrue(method_exists($source, 'packingLot'));
        $this->assertTrue(method_exists($source, 'harvestLot'));
        $this->assertTrue(method_exists($source, 'farm'));
    }

    public function test_harvest_lot_model_has_relationships(): void
    {
        $harvestLot = new HarvestLot();
        $this->assertTrue(method_exists($harvestLot, 'processingRecords'));
        $this->assertTrue(method_exists($harvestLot, 'packingSources'));
    }
}