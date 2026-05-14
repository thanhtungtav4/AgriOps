<?php

namespace Database\Seeders;

use App\Models\Farm;
use Illuminate\Database\Seeder;

class AgriFarmRegionalSeeder extends Seeder
{
    /**
     * Seed 3 regional farms with realistic Vietnamese agricultural profiles.
     * Uses firstOrCreate for idempotency.
     */
    public function run(): void
    {
        $farms = [
            [
                'code' => 'FARM-CC',
                'name' => 'Nông Trường Củ Chi',
                'address' => 'Xã Tân Thông Hội, Huyện Củ Chi, TP.HCM',
                'climate_zone' => 'tropical',
                'responsible_person' => 'Quản lý vùng Củ Chi',
                'total_area_m2' => 15000.00,
                'status' => 'active',
                'certification' => [
                    'region' => 'Đông Nam Bộ',
                    'elevation_m' => '10-25',
                    'avg_temp_c' => '25-35',
                    'rainy_season' => 'May-November',
                    'profile' => 'Hot tropical lowland; irrigation demand is 20-30% higher than highland farms.',
                ],
            ],
            [
                'code' => 'FARM-LD',
                'name' => 'Trang Trại Lâm Đồng',
                'address' => 'Phường 11, TP. Đà Lạt, Tỉnh Lâm Đồng',
                'climate_zone' => 'highland',
                'responsible_person' => 'Quản lý vùng Lâm Đồng',
                'total_area_m2' => 12000.00,
                'status' => 'active',
                'certification' => [
                    'region' => 'Tây Nguyên highland',
                    'elevation_m' => '1500',
                    'avg_temp_c' => '15-25',
                    'rainy_season' => 'May-October',
                    'profile' => 'Premier cool highland vegetable region, suitable for temperate and premium leafy crops.',
                ],
            ],
            [
                'code' => 'FARM-MD',
                'name' => 'Nông Trại Măng Đen',
                'address' => 'Thị trấn Măng Đen, Huyện Kon Plông, Tỉnh Kon Tum',
                'climate_zone' => 'highland',
                'responsible_person' => 'Quản lý vùng Măng Đen',
                'total_area_m2' => 10000.00,
                'status' => 'active',
                'certification' => [
                    'region' => 'Tây Nguyên highland',
                    'elevation_m' => '1200',
                    'avg_temp_c' => '18-26',
                    'rainy_season' => 'May-October',
                    'profile' => 'Emerging highland farming area, good for herbs, mixed leafy greens, roots, and fruiting vegetables.',
                ],
            ],
        ];

        foreach ($farms as $farmData) {
            Farm::updateOrCreate(
                ['code' => $farmData['code']],
                $farmData
            );
        }
    }
}
