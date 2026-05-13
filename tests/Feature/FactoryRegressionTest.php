<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Farm;
use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Plot;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\WorkTask;
use App\Models\FarmingLog;
use App\Models\HarvestLot;
use App\Models\PackingLot;
use App\Models\DeliveryNote;
use App\Models\ReturnRecord;
use App\Models\Alert;
use App\Models\CostRecord;
use App\Models\PriceTable;
use App\Models\SupplyDemand;
use App\Models\ProductionPlan;
use App\Models\GrowthStage;
use App\Models\ProcessingRecord;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class FactoryRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_complete_farm_workflow_using_factories()
    {
        $farm = Farm::factory()->create();
        $crop = Crop::factory()->create();
        $variety = CropVariety::factory()->for($crop, 'crop')->create();
        $plot = Plot::factory()->for($farm)->create();
        
        $batch = PlantingBatch::factory()
            ->for($farm)
            ->for($crop)
            ->for($variety, 'variety')
            ->create();

        $supplyDemand = SupplyDemand::factory()
            ->for($crop)
            ->state(['farm_id' => $farm->id])
            ->create();

        $productionPlan = ProductionPlan::factory()
            ->for($farm)
            ->for($crop)
            ->for($variety, 'variety')
            ->for($supplyDemand, 'supplyDemand')
            ->create();

        $growthStage = GrowthStage::factory()
            ->for($crop)
            ->for($variety, 'variety')
            ->create();
            
        $allocation = PlantingBatchAllocation::factory()
            ->for($batch, 'batch')
            ->for($plot)
            ->create();
            
        $workTask = WorkTask::factory()
            ->for($farm)
            ->for($batch, 'plantingBatch')
            ->for($allocation, 'allocation')
            ->create();
            
        $farmingLog = FarmingLog::factory()
            ->for($farm)
            ->for($workTask)
            ->create();
            
        $harvestLot = HarvestLot::factory()
            ->for($farm)
            ->for($batch)
            ->create();

        $processingRecord = ProcessingRecord::factory()
            ->for($farm)
            ->for($harvestLot)
            ->create();
            
        $packingLot = PackingLot::factory()
            ->for($farm)
            ->create();
            
        $deliveryNote = DeliveryNote::factory()
            ->for($farm)
            ->for($packingLot)
            ->create();
            
        $returnRecord = ReturnRecord::factory()
            ->for($farm)
            ->for($deliveryNote)
            ->create();
            
        $alert = Alert::factory()
            ->for($farm)
            ->create();
            
        $costRecord = CostRecord::factory()
            ->for($farm)
            ->create();
            
        $priceTable = PriceTable::factory()
            ->for($crop)
            ->create();

        $this->assertNotNull($farm->id);
        $this->assertNotNull($crop->id);
        $this->assertNotNull($variety->id);
        $this->assertNotNull($plot->id);
        $this->assertNotNull($batch->id);
        $this->assertNotNull($supplyDemand->id);
        $this->assertNotNull($productionPlan->id);
        $this->assertNotNull($growthStage->id);
        $this->assertNotNull($allocation->id);
        $this->assertNotNull($workTask->id);
        $this->assertNotNull($farmingLog->id);
        $this->assertNotNull($harvestLot->id);
        $this->assertNotNull($processingRecord->id);
        $this->assertNotNull($packingLot->id);
        $this->assertNotNull($deliveryNote->id);
        $this->assertNotNull($returnRecord->id);
        $this->assertNotNull($alert->id);
        $this->assertNotNull($costRecord->id);
        $this->assertNotNull($priceTable->id);
    }

    public function test_cancelled_work_task_cannot_accept_logs()
    {
        $farm = Farm::factory()->create();
        $worker = User::factory()->create([
            'role' => User::ROLE_WORKER,
            'farm_id' => $farm->id,
        ]);
        $crop = Crop::factory()->create();
        $variety = CropVariety::factory()->for($crop, 'crop')->create();
        $plot = Plot::factory()->for($farm)->create();
        
        $batch = PlantingBatch::factory()
            ->for($farm)
            ->for($crop)
            ->for($variety, 'variety')
            ->create();
            
        $allocation = PlantingBatchAllocation::factory()
            ->for($batch, 'batch')
            ->for($plot)
            ->create();
            
        $workTask = WorkTask::factory()
            ->for($farm)
            ->for($batch, 'plantingBatch')
            ->for($allocation, 'allocation')
            ->state(['status' => 'cancelled'])
            ->create();

        Sanctum::actingAs($worker);

        $response = $this->postJson('/api/v1/work-tasks/' . $workTask->id . '/logs', [
            'notes' => 'Should not be accepted.',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'TASK_CANCELLED');

        $this->assertEquals(0, FarmingLog::count());
    }
}
