<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SoilHistory;
use App\Models\Plot;
use App\Models\Farm;
use App\Models\Bed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoilHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $farmManager;
    private User $worker;
    private Farm $farm;
    private Plot $plot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::factory()->create();
        $this->plot = Plot::factory()->create(['farm_id' => $this->farm->id]);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->farmManager = User::factory()->create([
            'role' => 'farm_manager',
            'farm_id' => $this->farm->id,
        ]);
        $this->worker = User::factory()->create([
            'role' => 'worker',
            'farm_id' => $this->farm->id,
        ]);
    }

    public function test_farm_manager_can_create_soil_history(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $response = $this->postJson('/api/v1/soil-histories', [
            'plot_id' => $this->plot->id,
            'record_type' => 'test_result',
            'recorded_at' => now()->toDateString(),
            'ph' => 6.5,
            'nitrogen' => 100.5,
            'phosphorus' => 50.0,
            'potassium' => 200.0,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.plot_id', $this->plot->id)
            ->assertJsonPath('data.record_type', 'test_result');
    }

    public function test_worker_cannot_create_soil_history(): void
    {
        $this->actingAs($this->worker, 'sanctum');

        $response = $this->postJson('/api/v1/soil-histories', [
            'plot_id' => $this->plot->id,
            'record_type' => 'test_result',
            'recorded_at' => now()->toDateString(),
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_list_soil_history(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $response = $this->getJson('/api/v1/soil-histories');

        $response->assertOk();
    }

    public function test_can_filter_soil_history_by_plot(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $plot2 = Plot::factory()->create(['farm_id' => $this->farm->id]);

        SoilHistory::factory()->create(['plot_id' => $this->plot->id]);
        SoilHistory::factory()->create(['plot_id' => $plot2->id]);

        $response = $this->getJson("/api/v1/soil-histories?plot_id={$this->plot->id}&per_page=100");

        $response->assertOk()
            ->assertJsonCount(1, 'data.data');
    }

    public function test_can_filter_soil_history_by_record_type(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        SoilHistory::factory()->create(['record_type' => 'test_result']);
        SoilHistory::factory()->create(['record_type' => 'amendment']);

        $response = $this->getJson('/api/v1/soil-histories?record_type=amendment&per_page=100');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data');
    }

    public function test_admin_can_delete_soil_history(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $record = SoilHistory::factory()->create();

        $response = $this->deleteJson("/api/v1/soil-histories/{$record->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('soil_histories', ['id' => $record->id]);
    }

    public function test_farm_manager_cannot_delete_soil_history(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $record = SoilHistory::factory()->create();

        $response = $this->deleteJson("/api/v1/soil-histories/{$record->id}");

        $response->assertForbidden();
    }
    
    public function test_farm_manager_cannot_access_another_farm_soil_history(): void
    {
        $anotherFarm = Farm::factory()->create();
        $anotherPlot = Plot::factory()->create(['farm_id' => $anotherFarm->id]);
        
        $soilHistory = SoilHistory::factory()->create(['plot_id' => $anotherPlot->id]);
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        // Try to view another farm's soil history
        $response = $this->getJson("/api/v1/soil-histories/{$soilHistory->id}");
        $response->assertForbidden();
        
        // Try to update another farm's soil history
        $response = $this->patchJson("/api/v1/soil-histories/{$soilHistory->id}", [
            'record_type' => 'amendment',
        ]);
        $response->assertForbidden();
    }
    
    public function test_farm_manager_cannot_create_soil_history_for_another_farm_plot(): void
    {
        $anotherFarm = Farm::factory()->create();
        $anotherPlot = Plot::factory()->create(['farm_id' => $anotherFarm->id]);
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        $response = $this->postJson('/api/v1/soil-histories', [
            'plot_id' => $anotherPlot->id,
            'record_type' => 'test_result',
            'recorded_at' => now()->toDateString(),
        ]);
        
        $response->assertForbidden();
    }
    
    public function test_farm_manager_cannot_create_soil_history_for_another_farm_bed(): void
    {
        $anotherFarm = Farm::factory()->create();
        $anotherPlot = Plot::factory()->create(['farm_id' => $anotherFarm->id]);
        $bed = Bed::factory()->create(['plot_id' => $anotherPlot->id]);
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        $response = $this->postJson('/api/v1/soil-histories', [
            'bed_id' => $bed->id,
            'record_type' => 'test_result',
            'recorded_at' => now()->toDateString(),
        ]);
        
        $response->assertForbidden();
    }

    public function test_farm_manager_cannot_create_global_soil_history_without_plot_or_bed(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $response = $this->postJson('/api/v1/soil-histories', [
            'record_type' => 'test_result',
            'recorded_at' => now()->toDateString(),
        ]);

        $response->assertForbidden();
    }

    public function test_farm_manager_list_excludes_another_farm_bed_only_soil_history(): void
    {
        $ownBed = Bed::factory()->create(['plot_id' => $this->plot->id]);

        $anotherFarm = Farm::factory()->create();
        $anotherPlot = Plot::factory()->create(['farm_id' => $anotherFarm->id]);
        $anotherBed = Bed::factory()->create(['plot_id' => $anotherPlot->id]);

        SoilHistory::factory()->create(['plot_id' => null, 'bed_id' => $ownBed->id]);
        SoilHistory::factory()->create(['plot_id' => null, 'bed_id' => $anotherBed->id]);

        $this->actingAs($this->farmManager, 'sanctum');

        $response = $this->getJson('/api/v1/soil-histories?per_page=100');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.bed_id', $ownBed->id);
    }
}
