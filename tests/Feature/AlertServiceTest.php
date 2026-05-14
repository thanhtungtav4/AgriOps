<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\ChemicalProduct;
use App\Models\Farm;
use App\Models\HarvestLot;
use App\Models\PlantingBatch;
use App\Models\User;
use App\Models\WorkTask;
use App\Services\AlertService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertServiceTest extends TestCase
{
    use RefreshDatabase;

    private AlertService $alertService;
    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alertService = app(AlertService::class);
        $this->farm = Farm::factory()->create();
    }

    public function test_generates_overdue_work_task_alert(): void
    {
        $task = WorkTask::factory()->create([
            'farm_id' => $this->farm->id,
            'planned_due_date' => now()->subDays(2),
            'status' => 'in_progress',
            'priority' => 'normal',
            'title' => 'Spray pesticide',
        ]);

        $alerts = $this->alertService->triggerOverdueWorkTaskAlerts($this->farm->id);

        $this->assertGreaterThanOrEqual(1, $alerts->count());
        $this->assertDatabaseHas('alerts', [
            'alert_type' => 'work_task_overdue',
            'source_type' => WorkTask::class,
            'source_id' => $task->id,
            'farm_id' => $this->farm->id,
        ]);
    }

    public function test_generates_urgent_overdue_work_task_alert_as_critical(): void
    {
        $task = WorkTask::factory()->create([
            'farm_id' => $this->farm->id,
            'planned_due_date' => now()->subDays(1),
            'status' => 'in_progress',
            'priority' => 'urgent',
            'title' => 'Emergency harvest',
        ]);

        $alerts = $this->alertService->triggerOverdueWorkTaskAlerts($this->farm->id);

        $alert = $alerts->first(fn($a) => $a->source_id === $task->id);
        $this->assertNotNull($alert);
        $this->assertEquals('critical', $alert->severity);
    }

    public function test_generates_yield_shortfall_alert(): void
    {
        $batch = PlantingBatch::factory()->create([
            'farm_id' => $this->farm->id,
            'planned_quantity' => 100,
            'actual_quantity' => 70,
            'status' => 'completed',
        ]);

        $alerts = $this->alertService->triggerYieldShortfallAlerts($this->farm->id);

        $this->assertDatabaseHas('alerts', [
            'alert_type' => 'yield_shortfall',
            'source_type' => PlantingBatch::class,
            'source_id' => $batch->id,
            'farm_id' => $this->farm->id,
        ]);
    }

    public function test_generates_critical_alert_when_shortfall_over_25_percent(): void
    {
        $batch = PlantingBatch::factory()->create([
            'farm_id' => $this->farm->id,
            'planned_quantity' => 100,
            'actual_quantity' => 50,
            'status' => 'completed',
        ]);

        $alerts = $this->alertService->triggerYieldShortfallAlerts($this->farm->id);

        $alert = $alerts->first(fn($a) => $a->source_id === $batch->id);
        $this->assertNotNull($alert);
        $this->assertEquals('critical', $alert->severity);
    }

    public function test_generates_chemical_low_stock_alert(): void
    {
        $product = ChemicalProduct::factory()->create([
            'farm_id' => $this->farm->id,
            'stock_quantity' => 5,
            'min_stock_level' => 20,
            'is_active' => true,
        ]);

        $alerts = $this->alertService->triggerChemicalLowStockAlerts($this->farm->id);

        $this->assertDatabaseHas('alerts', [
            'alert_type' => 'chemical_low_stock',
            'source_type' => ChemicalProduct::class,
            'source_id' => $product->id,
        ]);
    }

    public function test_generates_chemical_expiry_alert(): void
    {
        $product = ChemicalProduct::factory()->create([
            'farm_id' => $this->farm->id,
            'expiry_date' => now()->addDays(15),
            'is_active' => true,
        ]);

        $alerts = $this->alertService->triggerChemicalExpiryAlerts($this->farm->id, 30);

        $this->assertDatabaseHas('alerts', [
            'alert_type' => 'chemical_expiry',
            'source_type' => ChemicalProduct::class,
            'source_id' => $product->id,
        ]);
    }

    public function test_generates_expired_alert_as_critical(): void
    {
        $product = ChemicalProduct::factory()->create([
            'farm_id' => $this->farm->id,
            'expiry_date' => now()->subDays(5),
            'is_active' => true,
        ]);

        $alerts = $this->alertService->triggerChemicalExpiryAlerts($this->farm->id);

        $alert = $alerts->first(fn($a) => $a->source_id === $product->id);
        $this->assertNotNull($alert);
        $this->assertEquals('critical', $alert->severity);
    }

    public function test_generates_harvest_due_alert(): void
    {
        $batch = PlantingBatch::factory()->create([
            'farm_id' => $this->farm->id,
            'planned_harvest_date' => now()->addDays(2),
            'status' => 'growing',
        ]);

        $alerts = $this->alertService->triggerHarvestDueAlerts($this->farm->id, 3);

        $this->assertDatabaseHas('alerts', [
            'alert_type' => 'harvest_due',
            'source_type' => PlantingBatch::class,
            'source_id' => $batch->id,
        ]);
    }

    public function test_generates_overdue_harvest_alert_as_critical(): void
    {
        $batch = PlantingBatch::factory()->create([
            'farm_id' => $this->farm->id,
            'planned_harvest_date' => now()->subDays(1),
            'status' => 'growing',
        ]);

        $alerts = $this->alertService->triggerHarvestDueAlerts($this->farm->id);

        $alert = $alerts->first(fn($a) => $a->source_id === $batch->id);
        $this->assertNotNull($alert);
        $this->assertEquals('critical', $alert->severity);
    }

    public function test_generates_harvest_lot_pending_alert(): void
    {
        $lot = HarvestLot::factory()->create([
            'farm_id' => $this->farm->id,
            'harvest_date' => now()->subDays(3),
            'status' => 'available',
        ]);

        $alerts = $this->alertService->triggerHarvestLotPendingAlerts($this->farm->id, 2);

        $this->assertDatabaseHas('alerts', [
            'alert_type' => 'harvest_lot_pending',
            'source_type' => HarvestLot::class,
            'source_id' => $lot->id,
        ]);
    }

    public function test_does_not_generate_alert_for_recently_harvested_lot(): void
    {
        $lot = HarvestLot::factory()->create([
            'farm_id' => $this->farm->id,
            'harvest_date' => now()->subDay(),
            'status' => 'available',
        ]);

        $alerts = $this->alertService->triggerHarvestLotPendingAlerts($this->farm->id, 2);

        $this->assertDatabaseMissing('alerts', [
            'source_type' => HarvestLot::class,
            'source_id' => $lot->id,
        ]);
    }

    public function test_does_not_duplicate_alerts(): void
    {
        $task = WorkTask::factory()->create([
            'farm_id' => $this->farm->id,
            'planned_due_date' => now()->subDays(2),
            'status' => 'in_progress',
        ]);

        // Run twice
        $this->alertService->triggerOverdueWorkTaskAlerts($this->farm->id);
        $alerts = $this->alertService->triggerOverdueWorkTaskAlerts($this->farm->id);

        // Should only have one alert
        $count = Alert::where('source_type', WorkTask::class)
            ->where('source_id', $task->id)
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_main_trigger_generates_all_types(): void
    {
        WorkTask::factory()->create([
            'farm_id' => $this->farm->id,
            'planned_due_date' => now()->subDays(1),
            'status' => 'in_progress',
        ]);

        ChemicalProduct::factory()->create([
            'farm_id' => $this->farm->id,
            'stock_quantity' => 2,
            'min_stock_level' => 20,
            'is_active' => true,
        ]);

        $alerts = $this->alertService->trigger($this->farm->id);

        $this->assertGreaterThanOrEqual(2, $alerts->count());
    }
}