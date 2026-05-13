<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\GrowthStage;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\Plot;
use App\Models\User;
use App\Models\WorkTask;
use App\Services\WorkTaskGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class WorkTaskGenerationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Farm $farm;
    protected Crop $crop;
    protected PlantingBatch $batch;
    protected WorkTaskGenerationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $this->crop = Crop::create([
            'name' => 'Dưa Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $this->batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-08-01',
            'status' => 'planned',
        ]);

        $this->service = new WorkTaskGenerationService();
    }

    public function test_generates_tasks_from_growth_stages(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Soil Preparation',
            'code' => 'soil_prep',
            'order' => 1,
            'duration_days' => 7,
        ]);

        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Planting',
            'code' => 'planting',
            'order' => 2,
            'duration_days' => 3,
        ]);

        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Growing',
            'code' => 'growing',
            'order' => 3,
            'duration_days' => 30,
        ]);

        $result = $this->service->generateFromBatch($this->batch);

        $this->assertEquals(3, $result['tasks']->count());
        $this->assertEquals(3, $result['created_count']);
        $this->assertEquals(0, $result['existing_count']);
        $this->assertEquals(0, $result['skipped_count']);

        $this->assertDatabaseCount('work_tasks', 3);
    }

    public function test_generates_one_task_per_growth_stage(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Seedling',
            'code' => 'seedling',
            'order' => 1,
            'duration_days' => 14,
        ]);

        $result = $this->service->generateFromBatch($this->batch);

        $task = $result['tasks']->first();
        $this->assertNotNull($task);
        $this->assertEquals($this->batch->farm_id, $task->farm_id);
        $this->assertEquals($this->batch->id, $task->planting_batch_id);
        $this->assertEquals('Seedling', $task->title);
        $this->assertEquals('nursery', $task->task_type);
    }

    public function test_planned_dates_based_on_batch_start_and_cumulative_duration(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Stage 1',
            'code' => 'stage1',
            'order' => 1,
            'duration_days' => 10,
        ]);

        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Stage 2',
            'code' => 'stage2',
            'order' => 2,
            'duration_days' => 5,
        ]);

        $result = $this->service->generateFromBatch($this->batch);

        $tasks = $result['tasks']->sortBy('growth_stage_id')->values();

        $this->assertEquals('2026-06-01', $tasks[0]->planned_start_date->toDateString());
        $this->assertEquals('2026-06-10', $tasks[0]->planned_due_date->toDateString());

        $this->assertEquals('2026-06-11', $tasks[1]->planned_start_date->toDateString());
        $this->assertEquals('2026-06-15', $tasks[1]->planned_due_date->toDateString());
    }

    public function test_idempotent_runs_do_not_duplicate_tasks(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Test Stage',
            'code' => 'test',
            'order' => 1,
            'duration_days' => 7,
        ]);

        $this->service->generateFromBatch($this->batch);
        $this->assertDatabaseCount('work_tasks', 1);

        $result = $this->service->generateFromBatch($this->batch);
        $this->assertDatabaseCount('work_tasks', 1);

        $this->assertEquals(1, $result['tasks']->count());
        $this->assertEquals(0, $result['created_count']);
        $this->assertEquals(1, $result['existing_count']);
        $this->assertEquals(1, $result['skipped_count']);
    }

    public function test_idempotent_with_different_allocation(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Test Stage',
            'code' => 'test',
            'order' => 1,
            'duration_days' => 7,
        ]);

        $plot = Plot::create([
            'farm_id' => $this->farm->id,
            'code' => 'P01',
            'name' => 'Plot 1',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $allocation = PlantingBatchAllocation::create([
            'planting_batch_id' => $this->batch->id,
            'plot_id' => $plot->id,
            'allocated_area_m2' => 50,
            'status' => 'allocated',
        ]);

        $result1 = $this->service->generateFromBatch($this->batch);
        $this->assertEquals(1, $result1['created_count']);
        $this->assertEquals(0, $result1['existing_count']);

        $result2 = $this->service->generateFromBatch($this->batch, $allocation->id);
        $this->assertEquals(1, $result2['created_count']);
        $this->assertEquals(0, $result2['existing_count']);

        $this->assertEquals(2, WorkTask::where('planting_batch_id', $this->batch->id)->count());
    }

    public function test_idempotent_runs_do_not_duplicate_allocation_tasks(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Allocation Stage',
            'code' => 'allocation_stage',
            'order' => 1,
            'duration_days' => 7,
        ]);

        $plot = Plot::create([
            'farm_id' => $this->farm->id,
            'code' => 'P02',
            'name' => 'Plot 2',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $allocation = PlantingBatchAllocation::create([
            'planting_batch_id' => $this->batch->id,
            'plot_id' => $plot->id,
            'allocated_area_m2' => 50,
            'status' => 'allocated',
        ]);

        $this->service->generateFromBatch($this->batch, $allocation->id);
        $result = $this->service->generateFromBatch($this->batch, $allocation->id);

        $this->assertEquals(0, $result['created_count']);
        $this->assertEquals(1, $result['existing_count']);
        $this->assertEquals(1, WorkTask::where('planting_batch_allocation_id', $allocation->id)->count());
    }

    public function test_rejects_allocation_from_another_batch(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Cross Batch Stage',
            'code' => 'cross_batch',
            'order' => 1,
            'duration_days' => 7,
        ]);

        $otherBatch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_start_date' => '2026-06-15',
            'planned_harvest_date' => '2026-08-15',
            'status' => 'planned',
        ]);

        $plot = Plot::create([
            'farm_id' => $this->farm->id,
            'code' => 'P03',
            'name' => 'Plot 3',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $allocation = PlantingBatchAllocation::create([
            'planting_batch_id' => $otherBatch->id,
            'plot_id' => $plot->id,
            'allocated_area_m2' => 50,
            'status' => 'allocated',
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->service->generateFromBatch($this->batch, $allocation->id);
    }

    public function test_generation_with_allocation_targets_plot_specific_tasks(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Plot Task',
            'code' => 'plot_task',
            'order' => 1,
            'duration_days' => 5,
        ]);

        $plot = Plot::create([
            'farm_id' => $this->farm->id,
            'code' => 'P01',
            'name' => 'Plot 1',
            'area_m2' => 100,
            'status' => 'available',
        ]);

        $allocation = PlantingBatchAllocation::create([
            'planting_batch_id' => $this->batch->id,
            'plot_id' => $plot->id,
            'allocated_area_m2' => 50,
            'status' => 'allocated',
        ]);

        $result = $this->service->generateFromBatch($this->batch, $allocation->id);

        $task = $result['tasks']->first();
        $this->assertNotNull($task);
        $this->assertEquals($plot->id, $task->plot_id);
        $this->assertEquals($allocation->id, $task->planting_batch_allocation_id);
    }

    public function test_returns_empty_when_no_growth_stages(): void
    {
        $result = $this->service->generateFromBatch($this->batch);

        $this->assertEquals(0, $result['tasks']->count());
        $this->assertEquals(0, $result['created_count']);
        $this->assertEquals(0, $result['existing_count']);
        $this->assertEquals(0, $result['skipped_count']);
    }

    public function test_derives_task_type_from_stage_code(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Harvest Time',
            'code' => 'harvest',
            'order' => 1,
            'duration_days' => 7,
        ]);

        $result = $this->service->generateFromBatch($this->batch);

        $task = $result['tasks']->first();
        $this->assertEquals('harvest', $task->task_type);
    }

    public function test_derives_task_type_from_stage_name(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Gieo Trồng',
            'code' => '',
            'order' => 1,
            'duration_days' => 3,
        ]);

        $result = $this->service->generateFromBatch($this->batch);

        $task = $result['tasks']->first();
        $this->assertEquals('planting', $task->task_type);
    }

    public function test_uses_today_if_no_planned_start_date(): void
    {
        $this->batch->update(['planned_start_date' => null]);

        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Test Stage',
            'code' => 'test',
            'order' => 1,
            'duration_days' => 5,
        ]);

        $result = $this->service->generateFromBatch($this->batch);

        $task = $result['tasks']->first();
        $this->assertEquals(now()->toDateString(), $task->planned_start_date->toDateString());
    }

    public function test_generates_tasks_for_variety_specific_stages(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'variety_id' => null,
            'name' => 'Generic Stage',
            'code' => 'generic',
            'order' => 1,
            'duration_days' => 5,
        ]);

        $result = $this->service->generateFromBatch($this->batch);

        $this->assertEquals(1, $result['created_count']);
    }

    public function test_tasks_include_metadata_with_stage_info(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Test Stage',
            'code' => 'test',
            'order' => 3,
            'duration_days' => 10,
        ]);

        $result = $this->service->generateFromBatch($this->batch);

        $task = $result['tasks']->first();
        $this->assertEquals(3, $task->metadata['growth_stage_order']);
        $this->assertEquals(10, $task->metadata['stage_duration_days']);
        $this->assertEquals('WorkTaskGenerationService', $task->metadata['generated_from']);
    }

    public function test_all_tasks_have_status_planned(): void
    {
        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Stage 1',
            'code' => 'stage1',
            'order' => 1,
            'duration_days' => 5,
        ]);

        GrowthStage::create([
            'crop_id' => $this->crop->id,
            'name' => 'Stage 2',
            'code' => 'stage2',
            'order' => 2,
            'duration_days' => 5,
        ]);

        $result = $this->service->generateFromBatch($this->batch);

        foreach ($result['tasks'] as $task) {
            $this->assertEquals('planned', $task->status);
            $this->assertEquals('normal', $task->priority);
        }
    }
}
