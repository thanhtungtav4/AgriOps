<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Farm;
use App\Models\FertilizerNorm;
use App\Models\GrowthStage;
use App\Models\HarvestModel;
use App\Models\IrrigationNorm;
use App\Models\LaborNorm;
use App\Models\LossProfile;
use App\Models\Plot;
use App\Models\ProductStandard;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CanonicalSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedFarmStructure();
        $this->seedCropData();
        $this->seedNorms();
    }

    private function seedUsers(): void
    {
        $farm = Farm::where('code', 'FARM001')->first();

        $users = [
            ['email' => 'admin@agriops.test', 'name' => 'Quản trị viên', 'role' => User::ROLE_ADMIN],
            ['email' => 'owner@agriops.test', 'name' => 'Ông Nguyễn Văn A', 'role' => User::ROLE_FARM_OWNER],
            ['email' => 'manager@agriops.test', 'name' => 'Bà Trần Thị B', 'role' => User::ROLE_FARM_MANAGER],
            ['email' => 'tech@agriops.test', 'name' => 'Anh Lê Văn C', 'role' => User::ROLE_TECHNICIAN],
            ['email' => 'worker@agriops.test', 'name' => 'Chị Phạm Thị D', 'role' => User::ROLE_WORKER],
            ['email' => 'delivery@agriops.test', 'name' => 'Anh Hoàng Văn E', 'role' => User::ROLE_DELIVERY],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'role' => $u['role'],
                    'farm_id' => $u['role'] === User::ROLE_ADMIN ? null : ($farm?->id),
                ]
            );
        }
    }

    private function seedFarmStructure(): void
    {
        $farm = Farm::firstOrCreate(
            ['code' => 'FARM001'],
            [
                'name' => 'Trang trại Rau An Toàn',
                'address' => '123 Đường Nguyễn Trãi, Quận 1, TP.HCM',
                'climate_zone' => 'tropical',
                'total_area_m2' => 5000.00,
                'status' => 'active',
            ]
        );

        $plotA = Plot::firstOrCreate(
            ['farm_id' => $farm->id, 'code' => 'PLOT-A'],
            [
                'name' => 'Lô A - Nhà kính',
                'area_m2' => 1500.00,
                'status' => 'planting',
            ]
        );

        $plotB = Plot::firstOrCreate(
            ['farm_id' => $farm->id, 'code' => 'PLOT-B'],
            [
                'name' => 'Lô B - Ngoài trời',
                'area_m2' => 1000.00,
                'status' => 'available',
            ]
        );

        $bedConfigs = [
            ['plot' => $plotA, 'code' => 'A-01', 'length_m' => 50.00, 'width_m' => 3.00],
            ['plot' => $plotA, 'code' => 'A-02', 'length_m' => 50.00, 'width_m' => 3.00],
            ['plot' => $plotA, 'code' => 'A-03', 'length_m' => 50.00, 'width_m' => 3.00],
            ['plot' => $plotB, 'code' => 'B-01', 'length_m' => 40.00, 'width_m' => 2.50],
            ['plot' => $plotB, 'code' => 'B-02', 'length_m' => 40.00, 'width_m' => 2.50],
            ['plot' => $plotB, 'code' => 'B-03', 'length_m' => 40.00, 'width_m' => 2.50],
        ];

        foreach ($bedConfigs as $c) {
            Bed::firstOrCreate(
                ['plot_id' => $c['plot']->id, 'code' => $c['code']],
                [
                    'length_m' => $c['length_m'],
                    'width_m' => $c['width_m'],
                    'area_m2' => $c['length_m'] * $c['width_m'],
                    'expected_plants' => (int) ($c['length_m'] * $c['width_m'] * 20),
                    'status' => 'available',
                ]
            );
        }
    }

    private function seedCropData(): void
    {
        $crop = Crop::firstOrCreate(
            ['name' => 'Rau muống'],
            [
                'group' => 'leafy',
                'sale_unit' => 'kg',
                'production_unit' => 'm2',
                'can_harvest_multiple' => true,
                'has_multiple_cycles' => true,
                'avg_growth_days' => 30,
                'harvest_exploitation_days' => 15,
                'rest_days' => 7,
                'sale_price_per_unit' => 15000.00,
            ]
        );

        $variety = CropVariety::firstOrCreate(
            ['crop_id' => $crop->id, 'code' => 'VM001'],
            [
                'name' => 'Muống Nhật (Japanese Water Spinach)',
                'avg_growth_days' => 25,
                'avg_yield_per_plant' => 0.15,
                'planting_density_per_m2' => 20,
                'disease_resistance' => 'Medium',
                'suitable_season' => 'All seasons',
                'status' => 'active',
            ]
        );

        $stages = [
            ['name' => 'Gieo giống', 'order' => 1, 'duration' => 5],
            ['name' => 'Cây con', 'order' => 2, 'duration' => 10],
            ['name' => 'Sinh trưởng', 'order' => 3, 'duration' => 10],
            ['name' => 'Thu hoạch', 'order' => 4, 'duration' => 15],
        ];

        foreach ($stages as $s) {
            GrowthStage::firstOrCreate(
                ['crop_id' => $crop->id, 'order' => $s['order']],
                [
                    'name' => $s['name'],
                    'duration_days' => $s['duration'],
                ]
            );
        }

        ProductStandard::firstOrCreate(
            ['crop_id' => $crop->id, 'code' => 'PS001'],
            [
                'name' => 'Rau muống loại 1',
                'allowed_defect_percent' => 5.00,
                'grade' => 'A',
            ]
        );

        HarvestModel::firstOrCreate(
            ['crop_id' => $crop->id],
            [
                'variety_id' => $variety->id,
                'harvest_type' => 'multiple',
                'avg_yield_per_plant' => 0.15,
                'min_yield_per_plant' => 0.10,
                'max_yield_per_plant' => 0.20,
                'planting_density_per_m2' => 20,
                'survival_rate' => 95.00,
                'grade_a_percent' => 85.00,
                'grade_b_percent' => 10.00,
                'grade_c_percent' => 3.00,
                'reject_percent' => 2.00,
                'days_to_first_harvest' => 25,
                'harvest_duration_days' => 15,
            ]
        );

        LossProfile::firstOrCreate(
            ['crop_id' => $crop->id],
            [
                'variety_id' => $variety->id,
                'harvest_loss_percent' => 5.00,
                'processing_loss_percent' => 2.00,
                'packing_loss_percent' => 1.00,
                'non_grade_a_percent' => 10.00,
                'reject_percent' => 2.00,
            ]
        );
    }

    private function seedNorms(): void
    {
        $crop = Crop::where('name', 'Rau muống')->first();
        if (!$crop) {
            return;
        }

        $variety = CropVariety::where('crop_id', $crop->id)->first();

        LaborNorm::firstOrCreate(
            ['crop_id' => $crop->id],
            [
                'variety_id' => $variety?->id,
                'hours_per_m2' => 0.5,
                'cost_per_m2' => 15000.00,
            ]
        );

        IrrigationNorm::firstOrCreate(
            ['crop_id' => $crop->id],
            [
                'variety_id' => $variety?->id,
                'frequency' => 'daily',
                'water_amount' => 5.0,
                'unit' => 'liters_per_m2_per_day',
                'requires_actual_log' => false,
            ]
        );

        FertilizerNorm::firstOrCreate(
            ['crop_id' => $crop->id],
            [
                'variety_id' => $variety?->id,
                'fertilizer_name' => 'NPK 16-16-8',
                'amount' => 0.5,
                'unit' => 'kg',
                'application_day_range' => '7-14',
            ]
        );
    }
}
