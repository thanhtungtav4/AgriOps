<?php

namespace Tests\Feature;

use App\Models\ChemicalUsage;
use App\Models\Crop;
use App\Models\Farm;
use App\Models\Incident;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\User;
use App\Services\IsolationGuardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IncidentChemicalUsageApiTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farm;
    private Farm $otherFarm;
    private User $technician;
    private Crop $crop;
    private PlantingBatch $batch;
    private Plot $plot;

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

        $this->technician = User::create([
            'name' => 'Technician',
            'email' => 'tech@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_TECHNICIAN,
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
            'status' => 'growing',
            'planned_start_date' => '2026-05-01',
        ]);

        $this->plot = Plot::create([
            'name' => 'Plot A',
            'code' => 'PLOT-A',
            'farm_id' => $this->farm->id,
            'area_m2' => 100,
            'status' => 'available',
        ]);
    }

    public function test_can_create_incident_and_link_chemical_usage_with_trace(): void
    {
        Sanctum::actingAs($this->technician);

        $incidentResponse = $this->postJson('/api/v1/incidents', [
            'planting_batch_id' => $this->batch->id,
            'plot_id' => $this->plot->id,
            'incident_type' => 'pest',
            'severity' => 'high',
            'detected_at' => '2026-05-13T08:00:00+07:00',
            'description' => 'Aphids detected on young leaves.',
        ]);

        $incidentResponse->assertStatus(201)
            ->assertJsonPath('data.farm_id', $this->farm->id)
            ->assertJsonPath('data.planting_batch_id', $this->batch->id)
            ->assertJsonPath('data.reported_by_user_id', $this->technician->id);

        $usageResponse = $this->postJson('/api/v1/chemical-usages', [
            'incident_id' => $incidentResponse->json('data.id'),
            'product_name' => 'Bio Pesticide A',
            'product_type' => 'biological',
            'active_ingredient' => 'Neem extract',
            'dosage_value' => 2.5,
            'dosage_unit' => 'ml/l',
            'quantity_value' => 10,
            'quantity_unit' => 'l',
            'cost_amount' => 150000,
            'isolation_days' => 7,
            'applied_at' => '2026-05-13T09:00:00+07:00',
        ]);

        $usageResponse->assertStatus(201)
            ->assertJsonPath('data.incident_id', $incidentResponse->json('data.id'))
            ->assertJsonPath('data.planting_batch_id', $this->batch->id)
            ->assertJsonPath('data.plot_id', $this->plot->id)
            ->assertJsonPath('data.applied_by_user_id', $this->technician->id)
            ->assertJsonPath('data.isolation_days', 7);

        $this->assertDatabaseHas('chemical_usages', [
            'incident_id' => $incidentResponse->json('data.id'),
            'product_name' => 'Bio Pesticide A',
        ]);
    }

    public function test_chemical_usage_calculates_isolation_end_date(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->postJson('/api/v1/chemical-usages', [
            'planting_batch_id' => $this->batch->id,
            'product_name' => 'Fungicide B',
            'product_type' => 'chemical',
            'isolation_days' => 7,
            'applied_at' => '2026-05-13T09:00:00+07:00',
        ]);

        $response->assertStatus(201);

        $usage = ChemicalUsage::first();
        $this->assertEquals('2026-05-20', $usage->isolation_ends_at->toDateString());
    }

    public function test_isolation_guard_blocks_harvest_before_period_ends(): void
    {
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

        app(IsolationGuardService::class)->assertCanHarvest($this->batch, '2026-05-16 09:00:00');
    }

    public function test_isolation_guard_allows_harvest_after_period_ends(): void
    {
        ChemicalUsage::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $this->batch->id,
            'product_name' => 'Fungicide B',
            'product_type' => 'chemical',
            'isolation_days' => 7,
            'applied_at' => '2026-05-13 09:00:00',
            'isolation_ends_at' => '2026-05-20 09:00:00',
        ]);

        app(IsolationGuardService::class)->assertCanHarvest($this->batch, '2026-05-21 09:00:00');

        $this->assertTrue(true);
    }

    public function test_user_cannot_create_incident_for_other_farm_batch(): void
    {
        Sanctum::actingAs($this->technician);

        $otherBatch = PlantingBatch::create([
            'farm_id' => $this->otherFarm->id,
            'crop_id' => $this->crop->id,
            'status' => 'growing',
        ]);

        $response = $this->postJson('/api/v1/incidents', [
            'planting_batch_id' => $otherBatch->id,
            'incident_type' => 'disease',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'FARM_MISMATCH');
    }

    public function test_user_cannot_view_other_farm_chemical_usage(): void
    {
        Sanctum::actingAs($this->technician);

        $usage = ChemicalUsage::create([
            'farm_id' => $this->otherFarm->id,
            'product_name' => 'Other Farm Product',
            'product_type' => 'chemical',
            'applied_at' => '2026-05-13 09:00:00',
        ]);

        $response = $this->getJson('/api/v1/chemical-usages/' . $usage->id);

        $response->assertStatus(403);
    }

    public function test_chemical_usage_requires_incident_or_batch_context(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->postJson('/api/v1/chemical-usages', [
            'product_name' => 'No Context Product',
            'product_type' => 'chemical',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_REQUIRED')
            ->assertJsonPath('error.details.field', 'planting_batch_id');
    }
}
