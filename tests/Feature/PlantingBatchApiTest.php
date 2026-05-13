<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\PlantingBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlantingBatchApiTest extends TestCase
{
    use RefreshDatabase;

    protected Farm $farm;
    protected Crop $crop;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::create([
            'name' => 'Test Farm',
            'code' => 'TF01',
            'total_area_m2' => 5000,
        ]);

        $this->crop = Crop::create([
            'name' => 'Dua Leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $this->user = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farm->id,
        ]);
    }

    // ========================================================================
    // HAPPY PATH: Create
    // ========================================================================

    public function test_authenticated_user_can_create_planting_batch(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/planting-batches', [
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'planned_area_m2' => 50,
            'planned_start_date' => '2026-06-01',
            'planned_harvest_date' => '2026-07-06',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'farm_id',
                    'crop_id',
                    'status',
                    'planned_quantity',
                    'planned_unit',
                ],
            ]);

        $this->assertDatabaseHas('planting_batches', [
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'status' => 'planned',
        ]);
    }

    public function test_create_batch_defaults_status_to_planned(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/planting-batches', [
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
        ]);

        $response->assertStatus(201);
        $this->assertEquals('planned', $response->json('data.status'));
    }

    // ========================================================================
    // HAPPY PATH: List
    // ========================================================================

    public function test_authenticated_user_can_list_planting_batches(): void
    {
        Sanctum::actingAs($this->user);

        PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 200,
            'planned_unit' => 'kg',
            'status' => 'approved',
        ]);

        $response = $this->getJson('/api/v1/planting-batches');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_list_batches_includes_relationships(): void
    {
        Sanctum::actingAs($this->user);

        PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->getJson('/api/v1/planting-batches');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'farm_id',
                        'crop_id',
                        'status',
                        'farm',
                        'crop',
                    ],
                ],
            ]);
    }

    // ========================================================================
    // HAPPY PATH: Show
    // ========================================================================

    public function test_authenticated_user_can_view_single_planting_batch(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->getJson("/api/v1/planting-batches/{$batch->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $batch->id)
            ->assertJsonPath('data.status', 'planned');
    }

    // ========================================================================
    // FARM SCOPE: Non-admin users restricted to their own farm
    // ========================================================================

    public function test_non_admin_cannot_list_another_farm_batches(): void
    {
        Sanctum::actingAs($this->user);

        // Create another farm with batches
        $otherFarm = Farm::create([
            'name' => 'Other Farm',
            'code' => 'OF01',
            'total_area_m2' => 3000,
        ]);

        PlantingBatch::create([
            'farm_id' => $otherFarm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->getJson('/api/v1/planting-batches');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_non_admin_cannot_view_another_farm_batch(): void
    {
        Sanctum::actingAs($this->user);

        $otherFarm = Farm::create([
            'name' => 'Other Farm',
            'code' => 'OF01',
            'total_area_m2' => 3000,
        ]);

        $otherBatch = PlantingBatch::create([
            'farm_id' => $otherFarm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->getJson("/api/v1/planting-batches/{$otherBatch->id}");

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_create_batch_for_another_farm(): void
    {
        Sanctum::actingAs($this->user);

        $otherFarm = Farm::create([
            'name' => 'Other Farm',
            'code' => 'OF01',
            'total_area_m2' => 3000,
        ]);

        $response = $this->postJson('/api/v1/planting-batches', [
            'farm_id' => $otherFarm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_access_any_farm_batches(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'farm_id' => null]);
        Sanctum::actingAs($admin);

        $otherFarm = Farm::create([
            'name' => 'Other Farm',
            'code' => 'OF01',
            'total_area_m2' => 3000,
        ]);

        PlantingBatch::create([
            'farm_id' => $otherFarm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->getJson('/api/v1/planting-batches');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    // ========================================================================
    // LIFECYCLE TRANSITIONS: Valid
    // ========================================================================

    public function test_can_transition_planned_to_approved(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'approved',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('planting_batches', [
            'id' => $batch->id,
            'status' => 'approved',
        ]);
    }

    public function test_can_transition_approved_to_soil_prep(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'approved',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'soil_prep',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'soil_prep');
    }

    public function test_can_transition_soil_prep_to_planting(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'soil_prep',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'planting',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'planting');
    }

    public function test_can_transition_planting_to_growing(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planting',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'growing',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'growing');
    }

    public function test_can_transition_growing_to_flowering(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'growing',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'flowering',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'flowering');
    }

    public function test_can_transition_flowering_to_fruiting(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'flowering',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'fruiting',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'fruiting');
    }

    public function test_can_transition_fruiting_to_harvesting(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'fruiting',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'harvesting',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'harvesting');
    }

    public function test_can_transition_harvesting_to_completed(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'harvesting',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'completed',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');
    }

    // ========================================================================
    // LIFECYCLE TRANSITIONS: Cancellation from any non-terminal active state
    // ========================================================================

    public function test_can_cancel_from_planned_state(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'cancelled',
            'reason' => 'Customer cancelled order',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_can_cancel_from_approved_state(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'approved',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'cancelled',
            'reason' => 'Weather emergency',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_can_cancel_from_growing_state(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'growing',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'cancelled',
            'reason' => 'Pest outbreak - crop destroyed',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_can_cancel_from_harvesting_state(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'harvesting',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'cancelled',
            'reason' => 'Contract terminated early',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
    }

    // ========================================================================
    // LIFECYCLE TRANSITIONS: Invalid
    // ========================================================================

    public function test_cannot_skip_forward_transition(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'growing',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_TRANSITION');
    }

    public function test_cannot_transition_backwards(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'approved',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'planned',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_TRANSITION');
    }

    public function test_cannot_transition_from_completed(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'completed',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'harvesting',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_TRANSITION');
    }

    public function test_cannot_transition_from_cancelled(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'cancelled',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'planned',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_TRANSITION');
    }

    public function test_cannot_transition_to_invalid_status(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
    }

    // ========================================================================
    // CANCELLATION REASON: Required
    // ========================================================================

    public function test_cancellation_requires_reason(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'cancelled',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'CANCELLATION_REASON_REQUIRED');
    }

    public function test_cancellation_reason_must_be_minimum_length(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'cancelled',
            'reason' => 'X', // Too short
        ]);

        $response->assertStatus(422);
    }

    public function test_normal_transitions_do_not_require_reason(): void
    {
        Sanctum::actingAs($this->user);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$batch->id}/transition", [
            'to_status' => 'approved',
            // No reason needed for normal transitions
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');
    }

    // ========================================================================
    // FARM SCOPE: Transition restrictions
    // ========================================================================

    public function test_cannot_transition_another_farm_batch(): void
    {
        Sanctum::actingAs($this->user);

        $otherFarm = Farm::create([
            'name' => 'Other Farm',
            'code' => 'OF01',
            'total_area_m2' => 3000,
        ]);

        $otherBatch = PlantingBatch::create([
            'farm_id' => $otherFarm->id,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
            'planned_unit' => 'kg',
            'status' => 'planned',
        ]);

        $response = $this->patchJson("/api/v1/planting-batches/{$otherBatch->id}/transition", [
            'to_status' => 'approved',
        ]);

        $response->assertStatus(403);
    }

    // ========================================================================
    // VALIDATION: Create endpoint
    // ========================================================================

    public function test_create_batch_requires_crop_id(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/planting-batches', [
            'farm_id' => $this->farm->id,
            'planned_quantity' => 100,
        ]);

        $response->assertStatus(422);
    }

    public function test_create_batch_requires_valid_farm(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/planting-batches', [
            'farm_id' => 99999,
            'crop_id' => $this->crop->id,
            'planned_quantity' => 100,
        ]);

        $response->assertStatus(422);
    }
}
