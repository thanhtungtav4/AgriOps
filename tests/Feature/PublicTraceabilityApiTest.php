<?php

namespace Tests\Feature;

use App\Models\ChemicalUsage;
use App\Models\Crop;
use App\Models\Farm;
use App\Models\HarvestLot;
use App\Models\PackingLot;
use App\Models\PackingLotSource;
use App\Models\PlantingBatch;
use App\Models\ProcessingRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTraceabilityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_qr_endpoint_returns_consumer_traceability_without_auth(): void
    {
        $packingLot = $this->createTraceabilityFixture();

        $response = $this->getJson("/api/v1/traceability/{$packingLot->qr_code}");

        $response
            ->assertOk()
            ->assertJsonPath('data.qr_code', 'qr_public_001')
            ->assertJsonPath('data.packing_lot_code', 'PL-PUBLIC-001')
            ->assertJsonPath('data.packed_at', '2026-05-16')
            ->assertJsonPath('data.crop_names.0', 'Tomato')
            ->assertJsonPath('data.farms.0.name', 'Farm Public')
            ->assertJsonPath('data.farms.0.certification.vietgap', 'VG-001')
            ->assertJsonPath('data.sources.0.harvest_date', '2026-05-15')
            ->assertJsonPath('data.sources.0.planted_at', '2026-05-01')
            ->assertJsonPath('data.safety_status', 'safety_controls_compliant');
    }

    public function test_public_qr_endpoint_does_not_leak_private_operational_fields(): void
    {
        $packingLot = $this->createTraceabilityFixture(withChemicalUsage: true);

        $response = $this->getJson("/api/v1/traceability/{$packingLot->qr_code}");

        $response->assertOk()
            ->assertJsonPath('data.safety_status', 'pending_isolation_clearance');

        $payload = $response->getContent();

        $this->assertStringNotContainsString('Private Fungicide', $payload);
        $this->assertStringNotContainsString('Secret Ingredient', $payload);
        $this->assertStringNotContainsString('dosage', $payload);
        $this->assertStringNotContainsString('applied_by_user_id', $payload);
        $this->assertStringNotContainsString('internal@example.com', $payload);
        $this->assertStringNotContainsString('cost', $payload);
        $this->assertStringNotContainsString('margin', $payload);
    }

    public function test_public_traceability_page_renders_for_qr_code(): void
    {
        $packingLot = $this->createTraceabilityFixture();

        $this->get("/traceability/{$packingLot->qr_code}")
            ->assertOk()
            ->assertSee('AgriOps Traceability')
            ->assertSee('PL-PUBLIC-001')
            ->assertSee('Farm Public')
            ->assertSee('Tuân thủ kiểm soát an toàn');
    }

    public function test_unknown_qr_code_returns_not_found(): void
    {
        $this->getJson('/api/v1/traceability/missing-code')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'RESOURCE_NOT_FOUND');
    }

    private function createTraceabilityFixture(bool $withChemicalUsage = false): PackingLot
    {
        $farm = Farm::create([
            'name' => 'Farm Public',
            'code' => 'FP',
            'status' => 'active',
            'certification' => ['vietgap' => 'VG-001'],
        ]);

        $crop = Crop::create([
            'name' => 'Tomato',
            'group' => 'fruit',
            'sale_unit' => 'kg',
            'production_unit' => 'cây',
        ]);

        $batch = PlantingBatch::create([
            'farm_id' => $farm->id,
            'crop_id' => $crop->id,
            'code' => 'TM-FP-2026-001',
            'status' => 'harvesting',
            'planned_start_date' => '2026-05-01',
        ]);

        $harvestLot = HarvestLot::create([
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'code' => 'HL-PUBLIC-001',
            'harvest_date' => '2026-05-15',
            'raw_quantity' => 80,
            'unit' => 'kg',
            'status' => 'packed',
        ]);

        ProcessingRecord::create([
            'farm_id' => $farm->id,
            'harvest_lot_id' => $harvestLot->id,
            'processed_at' => '2026-05-15 13:00:00',
            'input_quantity' => 80,
            'output_quantity' => 76,
            'loss_quantity' => 4,
            'loss_rate' => 0.05,
            'status' => 'completed',
        ]);

        $packingLot = PackingLot::create([
            'farm_id' => $farm->id,
            'code' => 'PL-PUBLIC-001',
            'packed_at' => '2026-05-16 08:00:00',
            'status' => 'published',
            'total_input_quantity' => 80,
            'total_output_quantity' => 76,
            'unit' => 'kg',
            'qr_code' => 'qr_public_001',
            'metadata' => ['internal_margin_percent' => 22.5],
        ]);

        PackingLotSource::create([
            'packing_lot_id' => $packingLot->id,
            'harvest_lot_id' => $harvestLot->id,
            'farm_id' => $farm->id,
            'planting_batch_id' => $batch->id,
            'quantity' => 80,
            'unit' => 'kg',
        ]);

        if ($withChemicalUsage) {
            $user = User::factory()->create(['email' => 'internal@example.com']);

            ChemicalUsage::create([
                'farm_id' => $farm->id,
                'planting_batch_id' => $batch->id,
                'applied_by_user_id' => $user->id,
                'product_name' => 'Private Fungicide',
                'product_type' => 'chemical',
                'active_ingredient' => 'Secret Ingredient',
                'dosage_value' => 12.5,
                'dosage_unit' => 'ml/l',
                'quantity_used' => 3,
                'quantity_unit' => 'l',
                'cost_amount' => 199.99,
                'isolation_days' => 7,
                'applied_at' => now(),
                'isolation_ends_at' => now()->addDays(2),
            ]);
        }

        return $packingLot;
    }
}
