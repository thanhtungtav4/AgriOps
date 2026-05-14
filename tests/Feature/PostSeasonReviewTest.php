<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PostSeasonReview;
use App\Models\ProductionPlan;
use App\Models\Farm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSeasonReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $farmManager;
    private User $worker;
    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::factory()->create();

        // Use valid roles from migration
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

    public function test_admin_can_list_reviews(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $response = $this->getJson('/api/v1/post-season-reviews');

        $response->assertOk();
    }

    public function test_farm_manager_can_create_review(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);

        $response = $this->postJson('/api/v1/post-season-reviews', [
            'production_plan_id' => $plan->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.production_plan_id', $plan->id)
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_cannot_create_duplicate_review_for_same_plan(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);
        PostSeasonReview::factory()->create(['production_plan_id' => $plan->id]);

        $response = $this->postJson('/api/v1/post-season-reviews', [
            'production_plan_id' => $plan->id,
        ]);

        $response->assertStatus(409);
    }

    public function test_worker_cannot_create_review(): void
    {
        $this->actingAs($this->worker, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);

        $response = $this->postJson('/api/v1/post-season-reviews', [
            'production_plan_id' => $plan->id,
        ]);

        $response->assertForbidden();
    }

    public function test_farm_manager_can_submit_review(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);
        $review = PostSeasonReview::factory()->create([
            'production_plan_id' => $plan->id,
            'status' => 'draft',
        ]);

        $response = $this->postJson("/api/v1/post-season-reviews/{$review->id}/submit");

        $response->assertOk()
            ->assertJsonPath('data.status', 'submitted');
    }

    public function test_admin_can_approve_review(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);
        $review = PostSeasonReview::factory()->create([
            'production_plan_id' => $plan->id,
            'status' => 'submitted',
        ]);

        $response = $this->postJson("/api/v1/post-season-reviews/{$review->id}/approve");

        $response->assertOk()
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_admin_can_reject_review(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);
        $review = PostSeasonReview::factory()->create([
            'production_plan_id' => $plan->id,
            'status' => 'submitted',
        ]);

        $response = $this->postJson("/api/v1/post-season-reviews/{$review->id}/reject", [
            'reason' => 'Missing yield data',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.rejected_reason', 'Missing yield data');
    }

    public function test_farm_manager_can_approve_review(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);
        $review = PostSeasonReview::factory()->create([
            'production_plan_id' => $plan->id,
            'status' => 'submitted',
        ]);

        $response = $this->postJson("/api/v1/post-season-reviews/{$review->id}/approve");

        $response->assertOk()
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_draft_review_can_be_updated(): void
    {
        $this->actingAs($this->farmManager, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);
        $review = PostSeasonReview::factory()->create([
            'production_plan_id' => $plan->id,
            'status' => 'draft',
            'yield_analysis' => null,
        ]);

        $response = $this->patchJson("/api/v1/post-season-reviews/{$review->id}", [
            'yield_analysis' => 'Good yield this season',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.yield_analysis', 'Good yield this season');
    }

    public function test_approved_review_cannot_be_updated(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);
        $review = PostSeasonReview::factory()->create([
            'production_plan_id' => $plan->id,
            'status' => 'approved',
        ]);

        $response = $this->patchJson("/api/v1/post-season-reviews/{$review->id}", [
            'yield_analysis' => 'Changed after approval',
        ]);

        // 422 because only draft can be updated
        $response->assertStatus(422);
    }

    public function test_admin_can_delete_draft_review(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);
        $review = PostSeasonReview::factory()->create([
            'production_plan_id' => $plan->id,
            'status' => 'draft',
        ]);

        $response = $this->deleteJson("/api/v1/post-season-reviews/{$review->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('post_season_reviews', ['id' => $review->id]);
    }

    public function test_admin_cannot_delete_submitted_review(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $plan = ProductionPlan::factory()->create(['farm_id' => $this->farm->id]);
        $review = PostSeasonReview::factory()->create([
            'production_plan_id' => $plan->id,
            'status' => 'submitted',
        ]);

        $response = $this->deleteJson("/api/v1/post-season-reviews/{$review->id}");

        // 422 because only draft can be deleted
        $response->assertStatus(422);
    }
    
    public function test_farm_manager_cannot_create_review_for_another_farm_plan(): void
    {
        $anotherFarm = Farm::factory()->create();
        $anotherPlan = ProductionPlan::factory()->create(['farm_id' => $anotherFarm->id]);
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        $response = $this->postJson('/api/v1/post-season-reviews', [
            'production_plan_id' => $anotherPlan->id,
        ]);
        
        $response->assertForbidden();
    }
    
    public function test_farm_manager_cannot_submit_another_farm_review(): void
    {
        $anotherFarm = Farm::factory()->create();
        $anotherPlan = ProductionPlan::factory()->create(['farm_id' => $anotherFarm->id]);
        $review = PostSeasonReview::factory()->create([
            'production_plan_id' => $anotherPlan->id,
            'status' => 'draft',
        ]);
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        $response = $this->postJson("/api/v1/post-season-reviews/{$review->id}/submit");
        $response->assertForbidden();
    }
    
    public function test_farm_manager_cannot_approve_another_farm_review(): void
    {
        $anotherFarm = Farm::factory()->create();
        $anotherPlan = ProductionPlan::factory()->create(['farm_id' => $anotherFarm->id]);
        $review = PostSeasonReview::factory()->create([
            'production_plan_id' => $anotherPlan->id,
            'status' => 'submitted',
        ]);
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        $response = $this->postJson("/api/v1/post-season-reviews/{$review->id}/approve");
        $response->assertForbidden();
    }
    
    public function test_farm_manager_cannot_reject_another_farm_review(): void
    {
        $anotherFarm = Farm::factory()->create();
        $anotherPlan = ProductionPlan::factory()->create(['farm_id' => $anotherFarm->id]);
        $review = PostSeasonReview::factory()->create([
            'production_plan_id' => $anotherPlan->id,
            'status' => 'submitted',
        ]);
        
        $this->actingAs($this->farmManager, 'sanctum');
        
        $response = $this->postJson("/api/v1/post-season-reviews/{$review->id}/reject", [
            'reason' => 'Test rejection reason',
        ]);
        $response->assertForbidden();
    }
}