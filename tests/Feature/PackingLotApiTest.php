<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\HarvestLot;
use App\Models\PackingLot;
use App\Models\PackingLotSource;
use App\Models\PlantingBatch;
use App\Models\PreHarvestInspection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PackingLotApiTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farmA;
    private Farm $farmB;
    private User $managerA;
    private User $admin;
    private Crop $crop;
    private array $harvestLotsA = [];
    private array $harvestLotsB = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->farmA = Farm::create(['name' => 'Farm A', 'code' => 'FA', 'status' => 'active']);
        $this->farmB = Farm::create(['name' => 'Farm B', 'code' => 'FB', 'status' => 'active']);

        $this->managerA = User::create([
            'name' => 'Manager A',
            'email' => 'manager-a@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_FARM_MANAGER,
            'farm_id' => $this->farmA->id,
        ]);

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'farm_id' => null,
        ]);

        $this->crop = Crop::create([
            'name' => 'Tomato',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $this->harvestLotsA = $this->createHarvestLotsForFarm($this->farmA, 2);
        $this->harvestLotsB = $this->createHarvestLotsForFarm($this->farmB, 2);
    }

    private function createHarvestLotsForFarm(Farm $farm, int $count): array
    {
        $lots = [];
        for ($i = 0; $i < $count; $i++) {
            $batch = PlantingBatch::create([
                'farm_id' => $farm->id,
                'crop_id' => $this->crop->id,
                'status' => 'fruiting',
                'planned_start_date' => '2026-05-01',
            ]);

            PreHarvestInspection::create([
                'farm_id' => $farm->id,
                'planting_batch_id' => $batch->id,
                'inspector_user_id' => $this->managerA->id,
                'approved_by_user_id' => $this->managerA->id,
                'status' => 'approved',
                'inspected_at' => '2026-05-13 08:00:00',
                'approved_at' => '2026-05-13 08:00:00',
                'checklist' => [['key' => 'maturity', 'label' => 'Maturity', 'passed' => true]],
            ]);

            $lots[] = HarvestLot::create([
                'farm_id' => $farm->id,
                'planting_batch_id' => $batch->id,
                'code' => 'HL-' . strtoupper($farm->code) . '-' . ($i + 1),
                'harvest_date' => '2026-05-15',
                'raw_quantity' => 100,
                'unit' => 'kg',
                'status' => 'available',
            ]);
        }

        return $lots;
    }

    public function test_admin_can_create_packing_lot_mixed_from_two_farms(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/packing-lots', [
            'farm_id' => $this->farmA->id,
            'packed_at' => '2026-05-15 10:00:00',
            'total_output_quantity' => 95,
            'unit' => 'kg',
            'notes' => 'Mixed lot',
            'sources' => [
                ['harvest_lot_id' => $this->harvestLotsA[0]->id, 'quantity' => 60],
                ['harvest_lot_id' => $this->harvestLotsB[0]->id, 'quantity' => 40],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.total_output_quantity', '95.000')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'code',
                    'sources' => [
                        '*' => ['harvest_lot_id', 'quantity', 'farm_id'],
                    ],
                ],
            ]);

        $sourceFarms = collect($response->json('data.sources'))->pluck('farm_id')->toArray();
        $this->assertContains($this->farmA->id, $sourceFarms);
        $this->assertContains($this->farmB->id, $sourceFarms);

        $this->assertDatabaseHas('packing_lot_sources', [
            'harvest_lot_id' => $this->harvestLotsA[0]->id,
            'quantity' => 60,
        ]);
        $this->assertDatabaseHas('packing_lot_sources', [
            'harvest_lot_id' => $this->harvestLotsB[0]->id,
            'quantity' => 40,
        ]);
    }

    public function test_farm_manager_cannot_pack_other_farm_harvest_lot(): void
    {
        Sanctum::actingAs($this->managerA);

        $response = $this->postJson('/api/v1/packing-lots', [
            'farm_id' => $this->farmA->id,
            'packed_at' => '2026-05-15 10:00:00',
            'total_output_quantity' => 50,
            'unit' => 'kg',
            'sources' => [
                ['harvest_lot_id' => $this->harvestLotsB[0]->id, 'quantity' => 50],
            ],
        ]);

        $response->assertStatus(403);
    }

    public function test_packed_harvest_lot_is_rejected_with_422(): void
    {
        Sanctum::actingAs($this->admin);

        $this->harvestLotsA[0]->update(['status' => 'packed']);

        $response = $this->postJson('/api/v1/packing-lots', [
            'farm_id' => $this->farmA->id,
            'packed_at' => '2026-05-15 10:00:00',
            'total_output_quantity' => 50,
            'unit' => 'kg',
            'sources' => [
                ['harvest_lot_id' => $this->harvestLotsA[0]->id, 'quantity' => 50],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'PACKING_SOURCE_UNAVAILABLE');
    }

    public function test_reserved_harvest_lot_is_rejected_with_422(): void
    {
        Sanctum::actingAs($this->admin);

        $this->harvestLotsA[0]->update(['status' => 'reserved']);

        $response = $this->postJson('/api/v1/packing-lots', [
            'farm_id' => $this->farmA->id,
            'packed_at' => '2026-05-15 10:00:00',
            'total_output_quantity' => 50,
            'unit' => 'kg',
            'sources' => [
                ['harvest_lot_id' => $this->harvestLotsA[0]->id, 'quantity' => 50],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'PACKING_SOURCE_UNAVAILABLE');
    }

    public function test_cancelled_harvest_lot_is_rejected_with_422(): void
    {
        Sanctum::actingAs($this->admin);

        $this->harvestLotsA[0]->update(['status' => 'cancelled']);

        $response = $this->postJson('/api/v1/packing-lots', [
            'farm_id' => $this->farmA->id,
            'packed_at' => '2026-05-15 10:00:00',
            'total_output_quantity' => 50,
            'unit' => 'kg',
            'sources' => [
                ['harvest_lot_id' => $this->harvestLotsA[0]->id, 'quantity' => 50],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'PACKING_SOURCE_UNAVAILABLE');
    }

    public function test_duplicate_source_rows_are_rejected(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/packing-lots', [
            'farm_id' => $this->farmA->id,
            'packed_at' => '2026-05-15 10:00:00',
            'total_output_quantity' => 100,
            'unit' => 'kg',
            'sources' => [
                ['harvest_lot_id' => $this->harvestLotsA[0]->id, 'quantity' => 50],
                ['harvest_lot_id' => $this->harvestLotsA[0]->id, 'quantity' => 50],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'PACKING_DUPLICATE_SOURCES');
    }

    public function test_successful_packing_marks_harvest_lots_as_packed(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/packing-lots', [
            'farm_id' => $this->farmA->id,
            'packed_at' => '2026-05-15 10:00:00',
            'total_output_quantity' => 60,
            'unit' => 'kg',
            'sources' => [
                ['harvest_lot_id' => $this->harvestLotsA[0]->id, 'quantity' => 60],
            ],
        ]);

        $response->assertStatus(201);

        $this->harvestLotsA[0]->refresh();
        $this->assertEquals('packed', $this->harvestLotsA[0]->status);
    }

    public function test_empty_sources_are_rejected(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/packing-lots', [
            'farm_id' => $this->farmA->id,
            'packed_at' => '2026-05-15 10:00:00',
            'total_output_quantity' => 50,
            'unit' => 'kg',
            'sources' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_source_quantity_zero_is_rejected(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/packing-lots', [
            'farm_id' => $this->farmA->id,
            'packed_at' => '2026-05-15 10:00:00',
            'total_output_quantity' => 50,
            'unit' => 'kg',
            'sources' => [
                ['harvest_lot_id' => $this->harvestLotsA[0]->id, 'quantity' => 0],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_source_quantity_exceeds_harvest_lot_raw_quantity_is_rejected(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/packing-lots', [
            'farm_id' => $this->farmA->id,
            'packed_at' => '2026-05-15 10:00:00',
            'total_output_quantity' => 150,
            'unit' => 'kg',
            'sources' => [
                ['harvest_lot_id' => $this->harvestLotsA[0]->id, 'quantity' => 150],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'PACKING_QUANTITY_EXCEEDS');
    }

    public function test_list_packing_lots_includes_source_availability(): void
    {
        Sanctum::actingAs($this->admin);

        $packingLot = PackingLot::create([
            'farm_id' => $this->farmA->id,
            'created_by_user_id' => $this->admin->id,
            'code' => 'PL-TEST-001',
            'packed_at' => '2026-05-15 10:00:00',
            'total_input_quantity' => 60,
            'total_output_quantity' => 55,
            'unit' => 'kg',
            'status' => 'packed',
        ]);

        PackingLotSource::create([
            'packing_lot_id' => $packingLot->id,
            'harvest_lot_id' => $this->harvestLotsA[0]->id,
            'farm_id' => $this->farmA->id,
            'quantity' => 60,
            'unit' => 'kg',
        ]);

        $response = $this->getJson('/api/v1/packing-lots');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'status',
                        'total_output_quantity',
                        'sources' => [
                            '*' => [
                                'harvest_lot_id',
                                'quantity',
                                'harvest_lot' => ['id', 'status', 'raw_quantity'],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_show_packing_lot_includes_source_farm_details(): void
    {
        Sanctum::actingAs($this->admin);

        $packingLot = PackingLot::create([
            'farm_id' => $this->farmA->id,
            'created_by_user_id' => $this->admin->id,
            'code' => 'PL-TEST-002',
            'packed_at' => '2026-05-15 10:00:00',
            'total_input_quantity' => 100,
            'total_output_quantity' => 90,
            'unit' => 'kg',
            'status' => 'packed',
        ]);

        PackingLotSource::create([
            'packing_lot_id' => $packingLot->id,
            'harvest_lot_id' => $this->harvestLotsA[0]->id,
            'farm_id' => $this->farmA->id,
            'quantity' => 60,
            'unit' => 'kg',
        ]);

        PackingLotSource::create([
            'packing_lot_id' => $packingLot->id,
            'harvest_lot_id' => $this->harvestLotsB[0]->id,
            'farm_id' => $this->farmB->id,
            'quantity' => 40,
            'unit' => 'kg',
        ]);

        $response = $this->getJson("/api/v1/packing-lots/{$packingLot->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'sources' => [
                        '*' => [
                            'harvest_lot_id',
                            'farm_id',
                            'farm' => ['id', 'name', 'code'],
                        ],
                    ],
                ],
            ]);

        $sourceFarms = collect($response->json('data.sources'))->pluck('farm_id')->toArray();
        $this->assertContains($this->farmA->id, $sourceFarms);
        $this->assertContains($this->farmB->id, $sourceFarms);
    }
}