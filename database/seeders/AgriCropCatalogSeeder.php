<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\GrowthStage;
use App\Models\HarvestModel;
use App\Models\IrrigationNorm;
use App\Models\LaborNorm;
use App\Models\LossProfile;
use App\Models\ProductStandard;
use Illuminate\Database\Seeder;

class AgriCropCatalogSeeder extends Seeder
{
    public const CROP_NAMES = [
        'Lá thuốc dòi tím',
        'Lá sương sâm',
        'Lá chùm ngây',
        'Lá húng chanh',
        'Lá đinh lăng',
        'Xà lách romain',
        'Xà lách lo lo xanh',
        'Khoai tây vàng',
        'Ngải cứu',
        'Cải kale',
        'Cải bó xôi',
        'Cải bẹ xanh',
        'Cải ngọt',
        'Rau lang',
        'Cải thìa',
        'Mồng tơi',
        'Cải cúc',
        'Rau muống',
        'Rau dền',
        'Cải ngồng',
        'Đậu ve',
        'Cà tím nhật',
        'Măng tây',
        'Hành lá',
        'Cải muối dưa',
        'Lá vối',
        'Lá giang',
        'Lá chanh',
        'Bắp nếp',
        'Khổ qua',
        'Cà chua beef',
        'Bắp ngọt nhật',
        'Ớt chỉ địa',
        'Hành paro',
        'Bẹ bạc hà',
        'Lá dứa',
        'Xà lách mỹ',
        'Rau ngót',
        'Xà lách mỡ',
        'Dưa leo',
        'Chanh',
        'Bắp sú tim',
        'Cải thảo',
        'Củ dền',
        'Súp lơ xanh',
        'Ớt chuông đỏ',
        'Lá dổi xá xị',
        'Su su',
        'Bí đỏ',
        'Đu đủ',
        'Khoai lang',
        'Cà rốt',
        'Su hào',
        'Củ cải trắng',
        'Cà chua mini đỏ',
        'Bắp sú',
        'Bầu',
        'Lá chè xanh',
        'Bí đao',
        'Mướp hương',
        'Gừng',
        'Sả',
        'Đậu Hà Lan',
        'Cần tây',
        'Cà chua mini vàng',
        'Cà chua mini socola',
        'Lá hẹ',
        'Đọt bầu',
        'Rau càng cua',
        'Lá ổi',
        'Rau diếp cá',
        'Đọt khổ qua',
        'Súp lơ trắng',
        'Dưa leo baby',
        'Ớt chuông vàng',
        'Cải thìa tím',
        'Ớt chuông xanh',
        'Súp lơ baby',
        'Bắp cải tím',
        'Đậu cove nhật',
        'Khoai lang Úc',
    ];

    public function run(): void
    {
        foreach (self::CROP_NAMES as $index => $name) {
            $profile = $this->profileFor($name);

            $crop = Crop::updateOrCreate(
                ['name' => $name],
                [
                    'group' => $profile['group'],
                    'sale_unit' => $profile['sale_unit'],
                    'production_unit' => $profile['production_unit'],
                    'can_harvest_multiple' => $profile['multi'],
                    'has_multiple_cycles' => $profile['multi'],
                    'avg_growth_days' => $profile['growth_days'],
                    'harvest_exploitation_days' => $profile['harvest_duration'],
                    'rest_days' => $profile['rest_days'],
                    'sale_price_per_unit' => $profile['price'],
                ],
            );

            $variety = CropVariety::updateOrCreate(
                [
                    'crop_id' => $crop->id,
                    'code' => sprintf('CAT%03d', $index + 1),
                ],
                [
                    'name' => $name . ' tiêu chuẩn',
                    'supplier' => 'AgriOps regional catalog',
                    'description' => 'Giống/catalog mặc định cho ' . $name . ' trong bộ seed nông nghiệp Việt Nam.',
                    'avg_growth_days' => $profile['growth_days'],
                    'avg_yield_per_plant' => $profile['yield'],
                    'planting_density_per_m2' => $profile['density'],
                    'disease_resistance' => $profile['resistance'],
                    'suitable_season' => $profile['season'],
                    'suitable_climate_zone' => $profile['climate'],
                    'care_requirements' => $profile['care'],
                    'status' => 'active',
                    'notes' => $profile['notes'],
                ],
            );

            $stageIds = $this->seedGrowthStages($crop, $variety, $profile);
            $this->seedHarvestModel($crop, $variety, $profile);
            $this->seedIrrigationNorm($crop, $variety, $profile, $stageIds[1] ?? null);
            $this->seedLaborNorm($crop, $variety, $profile);
            $this->seedLossProfile($crop, $variety, $profile);
            $this->seedProductStandard($crop, $variety, $profile);
        }
    }

    private function seedGrowthStages(Crop $crop, CropVariety $variety, array $profile): array
    {
        $durations = $this->stageDurations($profile['growth_days'], $profile['multi']);
        $stageNames = $profile['multi']
            ? ['Ươm/ra rễ', 'Sinh trưởng thân lá', 'Thu hoạch lứa đầu', 'Khai thác lặp lại']
            : ['Ươm/ra rễ', 'Sinh trưởng thân lá', 'Tạo củ/trái/bắp', 'Hoàn thiện thu hoạch'];

        $ids = [];

        foreach ($stageNames as $order => $stageName) {
            $stage = GrowthStage::updateOrCreate(
                [
                    'crop_id' => $crop->id,
                    'variety_id' => $variety->id,
                    'code' => sprintf('CAT-ST%02d', $order + 1),
                ],
                [
                    'name' => $stageName,
                    'order' => $order + 1,
                    'duration_days' => $durations[$order],
                    'description' => 'Giai đoạn ' . mb_strtolower($stageName) . ' cho ' . $crop->name . '.',
                ],
            );

            $ids[] = $stage->id;
        }

        return $ids;
    }

    private function seedHarvestModel(Crop $crop, CropVariety $variety, array $profile): void
    {
        HarvestModel::updateOrCreate(
            [
                'crop_id' => $crop->id,
                'variety_id' => $variety->id,
            ],
            [
                'harvest_type' => $profile['multi'] ? 'multiple' : 'single',
                'avg_yield_per_plant' => $profile['yield'],
                'min_yield_per_plant' => max(0.02, round($profile['yield'] * 0.75, 4)),
                'max_yield_per_plant' => round($profile['yield'] * 1.25, 4),
                'planting_density_per_m2' => $profile['density'],
                'survival_rate' => $profile['survival'],
                'grade_a_percent' => $profile['grade_a'],
                'grade_b_percent' => 100 - $profile['grade_a'] - $profile['reject'],
                'grade_c_percent' => 0,
                'reject_percent' => $profile['reject'],
                'days_to_first_harvest' => $profile['growth_days'],
                'harvest_duration_days' => $profile['harvest_duration'],
            ],
        );
    }

    private function seedIrrigationNorm(Crop $crop, CropVariety $variety, array $profile, ?int $stageId): void
    {
        IrrigationNorm::updateOrCreate(
            [
                'crop_id' => $crop->id,
                'variety_id' => $variety->id,
                'growth_stage_id' => $stageId,
            ],
            [
                'frequency' => $profile['water'] >= 5.5 ? '1-2 lần/ngày' : '1 lần/ngày',
                'water_amount' => $profile['water'],
                'unit' => 'liters_per_m2_per_day',
                'timing' => 'Sáng sớm hoặc chiều mát',
                'requires_actual_log' => true,
                'notes' => 'Định mức theo vùng chính ' . $profile['climate'] . '; Củ Chi cần cộng thêm khoảng 20-30% nước so với cao nguyên.',
            ],
        );
    }

    private function seedLaborNorm(Crop $crop, CropVariety $variety, array $profile): void
    {
        LaborNorm::updateOrCreate(
            [
                'crop_id' => $crop->id,
                'variety_id' => $variety->id,
            ],
            [
                'hours_per_m2' => $profile['labor_hours'],
                'cost_per_m2' => round($profile['labor_hours'] * 30000, 2),
                'notes' => 'Bao gồm làm đất, chăm sóc, thu hoạch và sơ chế cơ bản.',
            ],
        );
    }

    private function seedLossProfile(Crop $crop, CropVariety $variety, array $profile): void
    {
        LossProfile::updateOrCreate(
            [
                'crop_id' => $crop->id,
                'variety_id' => $variety->id,
            ],
            [
                'harvest_loss_percent' => $profile['harvest_loss'],
                'processing_loss_percent' => $profile['processing_loss'],
                'packing_loss_percent' => $profile['packing_loss'],
                'non_grade_a_percent' => 100 - $profile['grade_a'],
                'reject_percent' => $profile['reject'],
                'notes' => 'Tỷ lệ hao hụt ước tính thực hành cho chuỗi cung ứng rau củ tươi.',
            ],
        );
    }

    private function seedProductStandard(Crop $crop, CropVariety $variety, array $profile): void
    {
        ProductStandard::updateOrCreate(
            [
                'crop_id' => $crop->id,
                'variety_id' => $variety->id,
                'code' => 'GRADE-A',
            ],
            [
                'name' => $crop->name . ' loại A',
                'specifications' => 'Tươi, đúng giống, sạch đất, không dập nát, không sâu bệnh nhìn thấy; kích cỡ đồng đều theo lô.',
                'allowed_defect_percent' => 5,
                'packing_spec' => $profile['packing'],
                'grade' => 'A',
            ],
        );
    }

    private function profileFor(string $name): array
    {
        $group = $this->groupFor($name);
        $climate = $this->climateFor($name);
        $multi = $this->isMultiHarvest($name, $group);

        $base = match ($group) {
            'fruit_tree' => [
                'growth_days' => 180,
                'harvest_duration' => 120,
                'density' => 0.08,
                'yield' => 8.0,
                'water' => 6.2,
                'labor_hours' => 0.22,
                'price' => 22000,
                'sale_unit' => 'kg',
                'production_unit' => 'cây',
                'packing' => 'Thùng 10-15 kg',
            ],
            'fruit' => [
                'growth_days' => 65,
                'harvest_duration' => $multi ? 35 : 10,
                'density' => 2.2,
                'yield' => 0.85,
                'water' => 5.4,
                'labor_hours' => 0.18,
                'price' => 24000,
                'sale_unit' => $this->isPieceSale($name) ? 'trái' : 'kg',
                'production_unit' => 'cây',
                'packing' => 'Thùng 5-10 kg',
            ],
            'root' => [
                'growth_days' => 85,
                'harvest_duration' => 7,
                'density' => 8.0,
                'yield' => 0.28,
                'water' => 4.2,
                'labor_hours' => 0.13,
                'price' => 18000,
                'sale_unit' => 'kg',
                'production_unit' => 'm2',
                'packing' => 'Bao/thùng 10 kg',
            ],
            default => [
                'growth_days' => 35,
                'harvest_duration' => $multi ? 28 : 5,
                'density' => 18.0,
                'yield' => 0.08,
                'water' => 4.0,
                'labor_hours' => 0.1,
                'price' => 16000,
                'sale_unit' => $this->isBunchSale($name) ? 'bó' : 'kg',
                'production_unit' => 'm2',
                'packing' => 'Rổ/thùng 5 kg hoặc bó theo đơn hàng',
            ],
        };

        if ($climate === 'tropical') {
            $base['water'] = round($base['water'] * 1.25, 2);
            $base['growth_days'] = max(20, $base['growth_days'] - ($group === 'leafy' ? 5 : 0));
        } elseif (in_array($climate, ['temperate', 'highland'], true)) {
            $base['growth_days'] += $group === 'leafy' ? 5 : 10;
        }

        $overrides = $this->overridesFor($name);
        $profile = array_merge($base, $overrides);
        $profile['group'] = $group;
        $profile['climate'] = $climate;
        $profile['multi'] = $multi || ($overrides['multi'] ?? false);
        $profile['harvest_duration'] = $overrides['harvest_duration'] ?? ($profile['multi'] ? max($base['harvest_duration'], 21) : $base['harvest_duration']);
        $profile['rest_days'] = $profile['multi'] ? 7 : 0;
        $profile['survival'] = $group === 'fruit_tree' ? 92 : 95;
        $profile['grade_a'] = in_array($climate, ['highland', 'temperate'], true) ? 82 : 78;
        $profile['reject'] = $group === 'leafy' ? 7 : 6;
        $profile['harvest_loss'] = $group === 'leafy' ? 5 : 4;
        $profile['processing_loss'] = $group === 'leafy' ? 8 : 5;
        $profile['packing_loss'] = 2;
        $profile['resistance'] = 'Trung bình-khá; cần IPM và vệ sinh đồng ruộng định kỳ';
        $profile['season'] = match ($climate) {
            'temperate', 'highland' => 'Mùa mát/cao nguyên quanh năm',
            'subtropical' => 'Mùa khô mát hoặc vùng cao',
            default => 'Nhiệt đới quanh năm, ưu tiên quản lý nước mùa mưa',
        };
        $profile['care'] = 'Theo dõi ẩm độ, che mưa/nắng khi cần, bón phân cân đối và ghi nhật ký canh tác theo lô.';
        $profile['notes'] = 'Dữ liệu là ước tính nông học nội bộ theo vùng Việt Nam, dùng để demo kế hoạch sản xuất khi chưa có khảo nghiệm từng giống.';

        return $profile;
    }

    private function groupFor(string $name): string
    {
        if (in_array($name, ['Chanh', 'Đu đủ'], true)) {
            return 'fruit_tree';
        }

        foreach (['Bắp sú', 'Bắp cải'] as $needle) {
            if (str_contains($name, $needle)) {
                return 'leafy';
            }
        }

        foreach (['Khoai', 'Củ ', 'Cà rốt', 'Su hào', 'Gừng'] as $needle) {
            if (str_contains($name, $needle)) {
                return 'root';
            }
        }

        foreach (['Cà chua', 'Cà tím', 'Khổ qua', 'Dưa leo', 'Bắp ', 'Ớt ', 'Bí ', 'Bầu', 'Mướp', 'Su su', 'Đậu '] as $needle) {
            if (str_contains($name, $needle)) {
                return 'fruit';
            }
        }

        return 'leafy';
    }

    private function climateFor(string $name): string
    {
        foreach (['Bắp sú', 'Bắp cải tím', 'Cải thảo', 'Súp lơ', 'Cà rốt', 'Su hào', 'Củ cải trắng'] as $needle) {
            if (str_contains($name, $needle)) {
                return 'temperate';
            }
        }

        foreach (['Xà lách', 'Khoai tây', 'Măng tây', 'Cải kale', 'Cải bó xôi', 'Đậu Hà Lan', 'Cần tây', 'Hành paro', 'Cà chua'] as $needle) {
            if (str_contains($name, $needle)) {
                return 'highland';
            }
        }

        foreach (['Hành lá', 'Đậu ve', 'Đậu cove', 'Cà tím', 'Ớt', 'Chanh', 'Cần tây'] as $needle) {
            if (str_contains($name, $needle)) {
                return 'subtropical';
            }
        }

        return 'tropical';
    }

    private function isMultiHarvest(string $name, string $group): bool
    {
        if ($group === 'fruit_tree') {
            return true;
        }

        foreach (['Rau muống', 'Rau lang', 'Mồng tơi', 'Rau dền', 'Măng tây', 'Dưa leo', 'Khổ qua', 'Cà chua', 'Cà tím', 'Ớt', 'Bầu', 'Bí', 'Mướp', 'Đậu', 'Su su', 'Lá ', 'Đọt ', 'Hành lá', 'Lá hẹ', 'Sả'] as $needle) {
            if (str_contains($name, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function isBunchSale(string $name): bool
    {
        foreach (['Hành', 'Sả', 'Lá hẹ', 'Rau muống', 'Rau dền', 'Ngải cứu', 'Rau diếp cá', 'Rau càng cua'] as $needle) {
            if (str_contains($name, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function isPieceSale(string $name): bool
    {
        foreach (['Bí đỏ', 'Bí đao', 'Bầu', 'Đu đủ', 'Chanh'] as $needle) {
            if (str_contains($name, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function overridesFor(string $name): array
    {
        return match ($name) {
            'Rau muống' => ['growth_days' => 25, 'harvest_duration' => 35, 'density' => 25.0, 'yield' => 0.06, 'water' => 5.8, 'multi' => true],
            'Xà lách romain' => ['growth_days' => 45, 'density' => 12.0, 'yield' => 0.22, 'water' => 3.8],
            'Khoai tây vàng' => ['growth_days' => 105, 'density' => 5.5, 'yield' => 0.65, 'water' => 4.4],
            'Măng tây' => ['growth_days' => 180, 'harvest_duration' => 90, 'density' => 4.0, 'yield' => 0.12, 'water' => 4.6, 'multi' => true],
            'Dưa leo baby' => ['growth_days' => 45, 'harvest_duration' => 35, 'density' => 2.5, 'yield' => 0.9, 'water' => 6.2, 'multi' => true],
            'Bắp sú tim', 'Cải thảo' => ['growth_days' => 70, 'density' => 5.0, 'yield' => 0.9, 'water' => 4.1],
            'Khổ qua' => ['growth_days' => 55, 'harvest_duration' => 45, 'density' => 1.8, 'yield' => 1.1, 'water' => 6.0, 'multi' => true],
            'Chanh' => ['growth_days' => 240, 'harvest_duration' => 180, 'density' => 0.06, 'yield' => 12.0, 'water' => 5.8, 'multi' => true],
            'Đu đủ' => ['growth_days' => 210, 'harvest_duration' => 180, 'density' => 0.4, 'yield' => 8.0, 'water' => 6.6, 'multi' => true],
            default => [],
        };
    }

    private function stageDurations(int $growthDays, bool $multi): array
    {
        $ratios = $multi ? [0.18, 0.37, 0.2, 0.25] : [0.2, 0.35, 0.3, 0.15];
        $durations = [];
        $allocated = 0;

        foreach ($ratios as $index => $ratio) {
            if ($index === count($ratios) - 1) {
                $durations[] = max(1, $growthDays - $allocated);
                continue;
            }

            $duration = max(1, (int) round($growthDays * $ratio));
            $durations[] = $duration;
            $allocated += $duration;
        }

        return $durations;
    }
}
