<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiErrorContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_crop_lookup_responses_include_standard_meta_trace_id(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Sanctum::actingAs($user);

        $crop = Crop::create([
            'name' => 'Rau muong',
            'group' => 'leafy',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $this->getJson('/api/v1/crops')
            ->assertOk()
            ->assertJsonPath('data.0.id', $crop->id)
            ->assertJsonStructure(['data', 'meta' => ['trace_id']]);

        $this->getJson('/api/v1/crops/' . $crop->id)
            ->assertOk()
            ->assertJsonPath('data.id', $crop->id)
            ->assertJsonStructure(['data', 'meta' => ['trace_id']]);
    }

    public function test_crop_variety_lookup_responses_include_standard_meta_trace_id(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Sanctum::actingAs($user);

        $crop = Crop::create([
            'name' => 'Dua leo',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $variety = CropVariety::create([
            'crop_id' => $crop->id,
            'name' => 'Dua leo baby',
            'code' => 'DLB',
        ]);

        $this->getJson('/api/v1/crop-varieties')
            ->assertOk()
            ->assertJsonPath('data.0.id', $variety->id)
            ->assertJsonStructure(['data', 'meta' => ['trace_id']]);

        $this->getJson('/api/v1/crop-varieties/' . $variety->id)
            ->assertOk()
            ->assertJsonPath('data.id', $variety->id)
            ->assertJsonStructure(['data', 'meta' => ['trace_id']]);
    }

    public function test_planning_domain_errors_use_standard_error_contract_with_details(): void
    {
        $farm = Farm::create([
            'name' => 'Farm A',
            'code' => 'FA',
            'total_area_m2' => 1000,
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $farm->id,
        ]);
        Sanctum::actingAs($user);

        $crop = Crop::create([
            'name' => 'Rau cai',
            'group' => 'leafy',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $this->postJson('/api/v1/planning/calculate', [
            'quantity' => 10,
            'unit' => 'unsupported_unit',
            'frequency' => 'daily',
            'crop_id' => $crop->id,
            'farm_id' => $farm->id,
            'target_date' => '2026-06-30',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'PLANNING_ERROR')
            ->assertJsonPath('error.details.field', 'unit')
            ->assertJsonStructure([
                'error' => [
                    'code',
                    'message',
                    'details' => ['field'],
                    'trace_id',
                ],
            ]);
    }

    public function test_laravel_validation_errors_normalized_to_standard_error_contract(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/supply-contracts', [])
            ->assertUnprocessable();

        $response->assertJsonStructure([
            'error' => [
                'code',
                'message',
                'details',
                'trace_id',
            ],
        ]);

        $response->assertJsonPath('error.code', 'VALIDATION_REQUIRED');

        $this->assertNotNull($response->json('error.trace_id'));
    }

    public function test_laravel_validation_error_contains_field_and_message(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/supply-contracts', [])
            ->assertUnprocessable();

        $this->assertNotEmpty($response->json('error.message'));

        $this->assertNotNull($response->json('error.details.field'));
    }

    public function test_auth_endpoint_validation_normalized_to_standard_contract(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable();

        $response->assertJsonStructure([
            'error' => ['code', 'message', 'details', 'trace_id'],
        ]);

        $response->assertJsonPath('error.code', 'VALIDATION_REQUIRED');
        $this->assertNotNull($response->json('error.trace_id'));
    }
}
