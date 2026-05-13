<?php

namespace Tests\Feature;

use App\Models\ChemicalUsage;
use App\Models\Crop;
use App\Models\Farm;
use App\Models\HarvestLot;
use App\Models\PackingLot;
use App\Models\PackingLotSource;
use App\Models\PlantingBatch;
use App\Models\ProcessingRecord;
use App\Models\User;
use App\Services\TraceabilityGraphService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraceabilityGraphServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_graph_includes_packing_lot_with_two_source_farms(): void
    {
        [$lot, $farmA, $farmB] = $this->createPackingGraphFixture();

        $graph = app(TraceabilityGraphService::class)->forPackingLot($lot);

        $this->assertSame('PL-TRACE-001', $graph['packing']['code']);
        $this->assertSame(2, $graph['packing']['source_count']);
        $this->assertEqualsCanonicalizing(
            [$farmA->code, $farmB->code],
            collect($graph['sources'])->pluck('farm_code')->all()
        );
    }

    public function test_graph_includes_processing_records_for_source_harvest_lots(): void
    {
        [$lot] = $this->createPackingGraphFixture(withProcessing: true);

        $graph = app(TraceabilityGraphService::class)->forPackingLot($lot);

        $processing = collect($graph['sources'])->flatMap(fn (array $source) => $source['processing']);
        $this->assertCount(2, $processing);
        $this->assertContains(0.1, $processing->pluck('loss_rate')->all());
        $this->assertContains('completed', $processing->pluck('status')->all());
    }

    public function test_graph_is_based_on_harvest_and_packing_records_not_plan_only_data(): void
    {
        [$lot] = $this->createPackingGraphFixture();

        $graph = app(TraceabilityGraphService::class)->forPackingLot($lot);

        $this->assertSame(100.0, $graph['packing']['total_input_quantity']);
        $this->assertSame(94.0, $graph['packing']['total_output_quantity']);
        $this->assertSame([60.0, 40.0], collect($graph['sources'])->pluck('quantity')->all());
        $this->assertStringNotContainsString('planned_quantity', json_encode($graph));
    }

    public function test_graph_excludes_sensitive_chemical_usage_fields(): void
    {
        [$lot, , , $batch] = $this->createPackingGraphFixture();
        $user = User::factory()->create(['email' => 'private@example.com']);

        ChemicalUsage::create([
            'farm_id' => $batch->farm_id,
            'planting_batch_id' => $batch->id,
            'applied_by_user_id' => $user->id,
            'product_name' => 'Private Fungicide',
            'product_type' => 'chemical',
            'active_ingredient' => 'Hidden Ingredient',
            'dosage_value' => 12.5,
            'dosage_unit' => 'ml/l',
            'cost_amount' => 199.99,
            'isolation_days' => 7,
            'applied_at' => now(),
            'isolation_ends_at' => now()->addDays(2),
        ]);

        $graph = app(TraceabilityGraphService::class)->forPackingLot($lot);
        $payload = json_encode($graph);

        $this->assertFalse($graph['sources'][0]['isolation_safe']);
        $this->assertStringNotContainsString('Private Fungicide', $payload);
        $this->assertStringNotContainsString('Hidden Ingredient', $payload);
        $this->assertStringNotContainsString('dosage', $payload);
        $this->assertStringNotContainsString('cost', $payload);
        $this->assertStringNotContainsString('private@example.com', $payload);
        $this->assertStringNotContainsString('applied_by_user_id', $payload);
    }

    public function test_missing_optional_relationships_do_not_break_graph_generation(): void
    {
        $farm = Farm::create(['name' => 'Farm Optional', 'code' => 'FO', 'status' => 'active']);
        $crop = Crop::create([
            'name' => 'Lettuce',
            'group' => 'leafy',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);
        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'status' => 'harvesting',
        ]);
        $lot = PackingLot::create([
            'farm_id' => $farm->id,
            'code' => 'PL-OPTIONAL',
            'packed_at' => '2026-05-16 10:00:00',
            'status' => 'packed',
            'total_input_quantity' => 20,
            'total_output_quantity' => 18,
        ]);
        $harvest = HarvestLot::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'code' => 'HL-OPTIONAL',
            'harvest_date' => '2026-05-16',
            'raw_quantity' => 20,
            'unit' => 'kg',
            'status' => 'packed',
        ]);
        PackingLotSource::create([
            'packing_lot_id' => $lot->id,
            'harvest_lot_id' => $harvest->id,
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'quantity' => 20,
            'unit' => 'kg',
        ]);

        $graph = app(TraceabilityGraphService::class)->forPackingLot($lot);

        $this->assertSame('HL-OPTIONAL', $graph['sources'][0]['harvest_lot_code']);
        $this->assertNull($graph['sources'][0]['batch']['code']);
        $this->assertSame('Lettuce', $graph['sources'][0]['batch']['crop_name']);
        $this->assertSame([], $graph['sources'][0]['processing']);
        $this->assertTrue($graph['sources'][0]['isolation_safe']);
    }

    public function test_graph_query_count_stays_flat_as_source_count_grows(): void
    {
        [$singleSourceLot] = $this->createPackingGraphFixture();
        [$multiSourceLot] = $this->createPackingGraphFixtureWithThreeSources();

        $singleSourceQueries = $this->captureQueries(fn () => app(TraceabilityGraphService::class)->forPackingLot($singleSourceLot));
        $multiSourceQueries = $this->captureQueries(fn () => app(TraceabilityGraphService::class)->forPackingLot($multiSourceLot));

        $this->assertLessThanOrEqual(
            $singleSourceQueries + 1,
            $multiSourceQueries,
            "Expected eager loading to keep query growth flat, but queries grew from {$singleSourceQueries} to {$multiSourceQueries}."
        );
    }

    private function createPackingGraphFixture(bool $withProcessing = false): array
    {
        $farmA = Farm::create(['name' => 'Farm A', 'code' => 'FA', 'status' => 'active']);
        $farmB = Farm::create(['name' => 'Farm B', 'code' => 'FB', 'status' => 'active']);
        $crop = Crop::create([
            'name' => 'Tomato',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);
        $batchA = PlantingBatch::create([
            'farm_id' => $farmA->id,
            'crop_id' => $crop->id,
            'code' => 'BATCH-A',
            'status' => 'harvesting',
            'planned_quantity' => 999,
        ]);
        $batchB = PlantingBatch::create([
            'farm_id' => $farmB->id,
            'crop_id' => $crop->id,
            'code' => 'BATCH-B',
            'status' => 'harvesting',
            'planned_quantity' => 888,
        ]);
        $harvestA = HarvestLot::create([
            'farm_id' => $farmA->id,
            'planting_batch_id' => $batchA->id,
            'code' => 'HL-A',
            'harvest_date' => '2026-05-15',
            'raw_quantity' => 60,
            'unit' => 'kg',
            'status' => 'packed',
        ]);
        $harvestB = HarvestLot::create([
            'farm_id' => $farmB->id,
            'planting_batch_id' => $batchB->id,
            'code' => 'HL-B',
            'harvest_date' => '2026-05-15',
            'raw_quantity' => 40,
            'unit' => 'kg',
            'status' => 'packed',
        ]);
        $lot = PackingLot::create([
            'farm_id' => $farmA->id,
            'code' => 'PL-TRACE-001',
            'packed_at' => '2026-05-15 12:00:00',
            'status' => 'packed',
            'total_input_quantity' => 100,
            'total_output_quantity' => 94,
            'unit' => 'kg',
        ]);

        PackingLotSource::create([
            'packing_lot_id' => $lot->id,
            'harvest_lot_id' => $harvestA->id,
            'farm_id' => $farmA->id,
            'planting_batch_id' => $batchA->id,
            'quantity' => 60,
            'unit' => 'kg',
        ]);
        PackingLotSource::create([
            'packing_lot_id' => $lot->id,
            'harvest_lot_id' => $harvestB->id,
            'farm_id' => $farmB->id,
            'planting_batch_id' => $batchB->id,
            'quantity' => 40,
            'unit' => 'kg',
        ]);

        if ($withProcessing) {
            ProcessingRecord::create([
                'farm_id' => $farmA->id,
                'harvest_lot_id' => $harvestA->id,
                'processed_at' => '2026-05-15 13:00:00',
                'input_quantity' => 60,
                'output_quantity' => 54,
                'loss_quantity' => 6,
                'loss_rate' => 0.1,
            ]);
            ProcessingRecord::create([
                'farm_id' => $farmB->id,
                'harvest_lot_id' => $harvestB->id,
                'processed_at' => '2026-05-15 13:30:00',
                'input_quantity' => 40,
                'output_quantity' => 38,
                'loss_quantity' => 2,
                'loss_rate' => 0.05,
            ]);
        }

        return [$lot, $farmA, $farmB, $batchA, $batchB];
    }

    private function createPackingGraphFixtureWithThreeSources(): array
    {
        $farmA = Farm::create(['name' => 'Farm A', 'code' => 'FA3', 'status' => 'active']);
        $farmB = Farm::create(['name' => 'Farm B', 'code' => 'FB3', 'status' => 'active']);
        $farmC = Farm::create(['name' => 'Farm C', 'code' => 'FC3', 'status' => 'active']);
        $crop = Crop::create([
            'name' => 'Cucumber',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $lot = PackingLot::create([
            'farm_id' => $farmA->id,
            'code' => 'PL-TRACE-THREE',
            'packed_at' => '2026-05-15 12:00:00',
            'status' => 'packed',
            'total_input_quantity' => 120,
            'total_output_quantity' => 110,
            'unit' => 'kg',
        ]);

        foreach ([[$farmA, 'A', 50], [$farmB, 'B', 40], [$farmC, 'C', 30]] as [$farm, $suffix, $quantity]) {
            $batch = PlantingBatch::create([
                'farm_id' => $farm->id,
                'crop_id' => $crop->id,
                'code' => "BATCH-{$suffix}3",
                'status' => 'harvesting',
            ]);

            $harvest = HarvestLot::create([
                'farm_id' => $farm->id,
                'planting_batch_id' => $batch->id,
                'code' => "HL-{$suffix}3",
                'harvest_date' => '2026-05-15',
                'raw_quantity' => $quantity,
                'unit' => 'kg',
                'status' => 'packed',
            ]);

            PackingLotSource::create([
                'packing_lot_id' => $lot->id,
                'harvest_lot_id' => $harvest->id,
                'farm_id' => $farm->id,
                'planting_batch_id' => $batch->id,
                'quantity' => $quantity,
                'unit' => 'kg',
            ]);
        }

        return [$lot];
    }

    private function captureQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $queries = count(DB::getQueryLog());

        DB::disableQueryLog();

        return $queries;
    }
}
