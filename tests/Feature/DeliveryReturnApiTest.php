<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\DeliveryNote;
use App\Models\Farm;
use App\Models\HarvestLot;
use App\Models\PackingLot;
use App\Models\PackingLotSource;
use App\Models\PlantingBatch;
use App\Models\SupplyContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryReturnApiTest extends TestCase
{
    use RefreshDatabase;

    private Farm $farm;
    private User $deliveryUser;
    private PackingLot $packingLot;
    private SupplyContract $contract;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farm = Farm::create(['name' => 'Farm Delivery', 'code' => 'FD', 'status' => 'active']);
        $this->deliveryUser = User::factory()->create([
            'role' => User::ROLE_DELIVERY,
            'farm_id' => $this->farm->id,
        ]);

        $crop = Crop::create([
            'name' => 'Tomato',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $this->contract = SupplyContract::create([
            'farm_id' => $this->farm->id,
            'customer_name' => 'Sieu thi Delivery',
            'customer_type' => 'retail',
            'crop_id' => $crop->id,
            'quantity' => 300,
            'unit' => 'kg',
            'frequency' => 'monthly',
            'start_date' => '2026-05-01',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $this->farm->id,
            'crop_id' => $crop->id,
            'code' => 'TM-FD-2026-001',
            'status' => 'harvesting',
        ]);

        $harvestLot = HarvestLot::create([
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $batch->id,
            'code' => 'HL-DELIVERY-001',
            'harvest_date' => '2026-05-15',
            'raw_quantity' => 120,
            'unit' => 'kg',
            'status' => 'packed',
        ]);

        $this->packingLot = PackingLot::create([
            'farm_id' => $this->farm->id,
            'code' => 'PL-DELIVERY-001',
            'packed_at' => '2026-05-16 08:00:00',
            'status' => 'published',
            'total_input_quantity' => 120,
            'total_output_quantity' => 100,
            'unit' => 'kg',
            'qr_code' => 'qr_delivery_001',
        ]);

        PackingLotSource::create([
            'packing_lot_id' => $this->packingLot->id,
            'harvest_lot_id' => $harvestLot->id,
            'farm_id' => $this->farm->id,
            'planting_batch_id' => $batch->id,
            'quantity' => 120,
            'unit' => 'kg',
        ]);
    }

    public function test_delivery_revenue_snapshot_is_calculated_from_accepted_quantity_and_unit_price(): void
    {
        Sanctum::actingAs($this->deliveryUser);

        $response = $this->postJson('/api/v1/deliveries', [
            'packing_lot_id' => $this->packingLot->id,
            'supply_contract_id' => $this->contract->id,
            'delivered_at' => '2026-05-17 08:00:00',
            'planned_quantity' => 100,
            'accepted_quantity' => 96,
            'unit' => 'kg',
            'unit_price' => 25000,
            'accepted_by_name' => 'Receiver A',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.customer_name', 'Sieu thi Delivery')
            ->assertJsonPath('data.accepted_quantity', '96.000')
            ->assertJsonPath('data.gross_revenue', '2400000.00')
            ->assertJsonPath('data.net_revenue', '2400000.00')
            ->assertJsonPath('data.acceptance_record.accepted_by_name', 'Receiver A');

        $deliveryId = $response->json('data.id');

        $this->getJson("/api/v1/deliveries/{$deliveryId}/revenue")
            ->assertOk()
            ->assertJsonPath('data.accepted_quantity', 96)
            ->assertJsonPath('data.returned_quantity', 0)
            ->assertJsonPath('data.unit_price', 25000)
            ->assertJsonPath('data.net_revenue', 2400000)
            ->assertJsonPath('data.price_snapshot.unit_price', 25000);
    }

    public function test_return_record_updates_delivery_net_revenue_and_links_to_packing_lot(): void
    {
        Sanctum::actingAs($this->deliveryUser);

        $delivery = DeliveryNote::create([
            'farm_id' => $this->farm->id,
            'packing_lot_id' => $this->packingLot->id,
            'supply_contract_id' => $this->contract->id,
            'delivered_by_user_id' => $this->deliveryUser->id,
            'code' => 'DN-RETURN-001',
            'customer_name' => 'Sieu thi Delivery',
            'customer_type' => 'retail',
            'delivered_at' => '2026-05-17 08:00:00',
            'planned_quantity' => 100,
            'accepted_quantity' => 100,
            'net_quantity' => 100,
            'unit' => 'kg',
            'unit_price' => 25000,
            'gross_revenue' => 2500000,
            'net_revenue' => 2500000,
            'status' => 'accepted',
        ]);

        $response = $this->postJson('/api/v1/returns', [
            'delivery_note_id' => $delivery->id,
            'returned_at' => '2026-05-18 09:00:00',
            'quantity' => 5,
            'unit' => 'kg',
            'reason' => 'wrong_size',
            'handling_action' => 'record_loss',
            'notes' => 'Size mismatch reported by supermarket',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.quantity', '5.000')
            ->assertJsonPath('data.revenue_deduction', '125000.00')
            ->assertJsonPath('data.packing_lot_id', $this->packingLot->id);

        $this->getJson("/api/v1/deliveries/{$delivery->id}/revenue")
            ->assertOk()
            ->assertJsonPath('data.accepted_quantity', 100)
            ->assertJsonPath('data.returned_quantity', 5)
            ->assertJsonPath('data.net_quantity', 95)
            ->assertJsonPath('data.return_deduction', 125000)
            ->assertJsonPath('data.net_revenue', 2375000);
    }

    public function test_return_quantity_cannot_exceed_accepted_quantity(): void
    {
        Sanctum::actingAs($this->deliveryUser);

        $delivery = DeliveryNote::create([
            'farm_id' => $this->farm->id,
            'packing_lot_id' => $this->packingLot->id,
            'delivered_by_user_id' => $this->deliveryUser->id,
            'code' => 'DN-RETURN-INVALID',
            'customer_name' => 'Sieu thi Delivery',
            'delivered_at' => '2026-05-17 08:00:00',
            'planned_quantity' => 10,
            'accepted_quantity' => 10,
            'net_quantity' => 10,
            'unit' => 'kg',
            'unit_price' => 25000,
            'gross_revenue' => 250000,
            'net_revenue' => 250000,
            'status' => 'accepted',
        ]);

        $this->postJson('/api/v1/returns', [
            'delivery_note_id' => $delivery->id,
            'returned_at' => '2026-05-18 09:00:00',
            'quantity' => 11,
            'reason' => 'wrong_weight',
            'handling_action' => 'record_loss',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'RETURN_QUANTITY_INVALID');
    }
}
