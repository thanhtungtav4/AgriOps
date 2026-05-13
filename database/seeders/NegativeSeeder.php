<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Farm;
use App\Models\Plot;
use App\Models\ProductStandard;
use App\Models\ProductionPlan;
use App\Models\SupplyContract;
use App\Models\SupplyDemand;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NegativeSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedInactiveFarm();
        $this->seedPlotsWithEdgeStatus();
        $this->seedInactiveVariety();
        $this->seedCancelledContract();
        $this->seedFulfilledDemand();
        $this->seedCompletedPlan();
        $this->seedCancelledPlan();
        $this->seedFruitTreeCrop();
    }

    private function seedInactiveFarm(): void
    {
        Farm::firstOrCreate(
            ['code' => 'FARM-OLD'],
            [
                'name' => 'Trang trại cũ không hoạt động',
                'status' => 'inactive',
                'total_area_m2' => 0,
            ]
        );
    }

    private function seedPlotsWithEdgeStatus(): void
    {
        $farm = Farm::where('code', 'FARM001')->first();
        if (!$farm) {
            return;
        }

        Plot::firstOrCreate(
            ['farm_id' => $farm->id, 'code' => 'PLOT-REST'],
            [
                'name' => 'Lô Nghỉ Đất',
                'area_m2' => 500.00,
                'status' => 'rest',
            ]
        );

        Plot::firstOrCreate(
            ['farm_id' => $farm->id, 'code' => 'PLOT-SUSPEND'],
            [
                'name' => 'Lô Tạm Ngưng',
                'area_m2' => 300.00,
                'status' => 'suspended',
            ]
        );
    }

    private function seedInactiveVariety(): void
    {
        $crop = Crop::where('name', 'Rau muống')->first();
        if (!$crop) {
            return;
        }

        CropVariety::firstOrCreate(
            ['crop_id' => $crop->id, 'code' => 'OLD001'],
            [
                'name' => 'Giống cũ không còn sử dụng',
                'avg_growth_days' => 30,
                'status' => 'inactive',
            ]
        );
    }

    private function seedCancelledContract(): void
    {
        $crop = Crop::where('name', 'Rau muống')->first();
        if (!$crop) {
            return;
        }

        SupplyContract::firstOrCreate(
            ['customer_name' => 'Khách hàng đã hủy'],
            [
                'customer_type' => 'restaurant',
                'crop_id' => $crop->id,
                'quantity' => 500.00,
                'unit' => 'kg',
                'frequency' => 'weekly',
                'start_date' => '2026-01-01',
                'end_date' => '2026-03-31',
                'status' => 'cancelled',
            ]
        );
    }

    private function seedFulfilledDemand(): void
    {
        $crop = Crop::where('name', 'Rau muống')->first();
        if (!$crop) {
            return;
        }

        SupplyDemand::firstOrCreate(
            ['crop_id' => $crop->id, 'quantity' => 500.00, 'unit' => 'kg', 'status' => 'fulfilled'],
            [
                'frequency' => 'once',
                'target_date' => '2026-04-01',
                'status' => 'fulfilled',
            ]
        );
    }

    private function seedCompletedPlan(): void
    {
        $farm = Farm::where('code', 'FARM001')->first();
        $crop = Crop::where('name', 'Rau muống')->first();
        if (!$farm || !$crop) {
            return;
        }

        ProductionPlan::firstOrCreate(
            ['farm_id' => $farm->id, 'crop_id' => $crop->id, 'status' => 'completed'],
            [
                'quantity' => 50.00,
                'unit' => 'kg',
                'target_delivery_date' => '2026-04-01',
                'status' => 'completed',
            ]
        );
    }

    private function seedCancelledPlan(): void
    {
        $farm = Farm::where('code', 'FARM001')->first();
        $crop = Crop::where('name', 'Rau muống')->first();
        if (!$farm || !$crop) {
            return;
        }

        ProductionPlan::firstOrCreate(
            ['farm_id' => $farm->id, 'crop_id' => $crop->id, 'status' => 'cancelled'],
            [
                'quantity' => 30.00,
                'unit' => 'kg',
                'target_delivery_date' => '2026-03-15',
                'status' => 'cancelled',
                'notes' => 'Hủy do không đủ nguồn lực',
            ]
        );
    }

    private function seedFruitTreeCrop(): void
    {
        Crop::firstOrCreate(
            ['name' => 'Bưởi'],
            [
                'group' => 'fruit_tree',
                'sale_unit' => 'trái',
                'production_unit' => 'cây',
                'can_harvest_multiple' => false,
                'has_multiple_cycles' => false,
                'avg_growth_days' => 365,
                'harvest_exploitation_days' => 30,
                'rest_days' => 0,
            ]
        );
    }
}
