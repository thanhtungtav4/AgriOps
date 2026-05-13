<?php

namespace Tests\Feature;

use App\Models\ChemicalUsage;
use App\Models\Crop;
use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\PreHarvestInspection;
use App\Models\User;
use App\Services\HarvestEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PreHarvestInspectionApiTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farm;
    private Farm $otherFarm;
    private User $manager;
    private User $worker;
    private Crop $crop;
    private PlantingBatch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'FARM001',
            'status' => 'active',
        ]);

        $this->otherFarm = Farm::create([
            'name' => 'Other Farm',
            'code' => 'FARM002',
            'status' => 'active',
        ]);

        $this->manager = User::create([
            'name' => 'Manager',
            'email' => 'manager-inspection@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farm->id,
        ]);

        $this->worker = User::create([
            'name' => 'Worker',
            'email' => 'worker-inspection@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_WORKER,
            'farm_id' => $this->farm->id,
        ]);

        $this->crop = Crop::create([
            'name' => 'Tomato',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $this->batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'status' => 'fruiting',
            'planned_start_date' => '2026-05-01',
        ]);
    }

    public function test_passed_inspection_by_approver_is_approved_and_allows_harvest(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/v1/pre-harvest-inspections', [
            'planting_batch_id' => $this->batch->id,
            'inspected_at' => '2026-05-13T08:00:00+07:00',
            'checklist' => $this->passingChecklist(),
            'notes' => 'All criteria passed.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.approved_by_user_id', $this->manager->id);

        app(HarvestEligibilityService::class)->assertCanHarvest($this->batch, '2026-05-14 08:00:00');

        $this->assertTrue(true);
    }

    public function test_failed_inspection_rejects_and_blocks_harvest(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/v1/pre-harvest-inspections', [
            'planting_batch_id' => $this->batch->id,
            'checklist' => [
                ['key' => 'maturity', 'label' => 'Maturity', 'passed' => true],
                ['key' => 'pest_free', 'label' => 'No active pest pressure', 'passed' => false, 'note' => 'Aphids still present.'],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'rejected');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Latest pre-harvest inspection is not approved.');

        app(HarvestEligibilityService::class)->assertCanHarvest($this->batch, '2026-05-14 08:00:00');
    }

    public function test_worker_submission_stays_submitted_until_approver_approves(): void
    {
        Sanctum::actingAs($this->worker);

        $response = $this->postJson('/api/v1/pre-harvest-inspections', [
            'planting_batch_id' => $this->batch->id,
            'checklist' => $this->passingChecklist(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.approved_by_user_id', null);

        Sanctum::actingAs($this->manager);

        $approveResponse = $this->postJson('/api/v1/pre-harvest-inspections/' . $response->json('data.id') . '/approve');

        $approveResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.approved_by_user_id', $this->manager->id);
    }

    public function test_worker_cannot_approve_inspection(): void
    {
        $inspection = PreHarvestInspection::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'inspector_user_id' => $this->worker->id,
            'status' => 'submitted',
            'inspected_at' => now(),
            'checklist' => $this->passingChecklist(),
        ]);

        Sanctum::actingAs($this->worker);

        $response = $this->postJson('/api/v1/pre-harvest-inspections/' . $inspection->id . '/approve');

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INSPECTION_APPROVAL_BLOCKED');
    }

    public function test_user_cannot_create_inspection_for_other_farm_batch(): void
    {
        $otherBatch = PlantingBatch::create([
            'farm_id' => $this->otherFarm->id,
            'crop_id' => $this->crop->id,
            'status' => 'fruiting',
        ]);

        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/v1/pre-harvest-inspections', [
            'planting_batch_id' => $otherBatch->id,
            'checklist' => $this->passingChecklist(),
        ]);

        $response->assertStatus(403);
    }

    public function test_harvest_eligibility_still_blocks_when_isolation_is_active(): void
    {
        PreHarvestInspection::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'inspector_user_id' => $this->manager->id,
            'approved_by_user_id' => $this->manager->id,
            'status' => 'approved',
            'inspected_at' => '2026-05-13 08:00:00',
            'approved_at' => '2026-05-13 08:00:00',
            'checklist' => $this->passingChecklist(),
        ]);

        ChemicalUsage::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'product_name' => 'Fungicide B',
            'product_type' => 'chemical',
            'isolation_days' => 7,
            'applied_at' => '2026-05-13 09:00:00',
            'isolation_ends_at' => '2026-05-20 09:00:00',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot harvest before chemical isolation period ends on 2026-05-20.');

        app(HarvestEligibilityService::class)->assertCanHarvest($this->batch, '2026-05-16 09:00:00');
    }

    public function test_latest_rejected_inspection_overrides_previous_approved_inspection(): void
    {
        PreHarvestInspection::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'inspector_user_id' => $this->manager->id,
            'approved_by_user_id' => $this->manager->id,
            'status' => 'approved',
            'inspected_at' => '2026-05-13 08:00:00',
            'approved_at' => '2026-05-13 08:00:00',
            'checklist' => $this->passingChecklist(),
        ]);

        PreHarvestInspection::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'inspector_user_id' => $this->manager->id,
            'status' => 'rejected',
            'inspected_at' => '2026-05-14 08:00:00',
            'rejected_at' => '2026-05-14 08:00:00',
            'checklist' => [
                ['key' => 'maturity', 'label' => 'Maturity', 'passed' => false],
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Latest pre-harvest inspection is not approved.');

        app(HarvestEligibilityService::class)->assertCanHarvest($this->batch, '2026-05-15 08:00:00');
    }

    private function passingChecklist(): array
    {
        return [
            ['key' => 'maturity', 'label' => 'Maturity', 'passed' => true],
            ['key' => 'pest_free', 'label' => 'No active pest pressure', 'passed' => true],
            ['key' => 'isolation_reviewed', 'label' => 'Isolation reviewed', 'passed' => true],
        ];
    }
}
