<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\CostRecord;
use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\DeliveryAcceptanceRecord;
use App\Models\DeliveryNote;
use App\Models\Farm;
use App\Models\FarmingLog;
use App\Models\FertilizerNorm;
use App\Models\GrowthStage;
use App\Models\HarvestLot;
use App\Models\HarvestModel;
use App\Models\Incident;
use App\Models\IrrigationNorm;
use App\Models\LaborNorm;
use App\Models\LossProfile;
use App\Models\PackingLot;
use App\Models\PackingLotSource;
use App\Models\Plot;
use App\Models\PreHarvestInspection;
use App\Models\PriceTable;
use App\Models\ProductStandard;
use App\Models\ProductionPlan;
use App\Models\SupplyContract;
use App\Models\SupplyDemand;
use App\Models\User;
use App\Models\WorkTask;
use App\Models\PlantingBatch;
use App\Models\PlantingBatchAllocation;
use App\Models\ChemicalUsage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoWorkflowSeeder extends Seeder
{
    /**
     * Tạo demo workflow đầy đủ: Contract → Demand → Plan → Batch → Tasks → Harvest → Processing → Packing → Delivery → Return
     * 
     * Demo này mô phỏng một chuỗi hoàn chỉnh:
     * 1. Hợp đồng cung ứng với siêu thị
     * 2. Tính kế hoạch sản xuất
     * 3. Tạo lứa trồng và phân bổ
     * 4. Sinh công việc theo giai đoạn
     * 5. Ghi nhận nhật ký canh tác
     * 6. Nghiệm thu trước thu hoạch
     * 7. Thu hoạch với phân loại A/B/C
     * 8. Sơ chế và đóng gói
     * 9. Giao hàng cho siêu thị
     * 10. Xử lý trả hàng (nếu có)
     */
    public function run(): void
    {
        $this->seedUsers();
        $this->seedFarmStructure();
        $this->seedCropData();
        $this->seedSupplyChain();
        $this->seedProductionPlan();
        $this->seedPlantingBatch();
        $this->seedWorkTasks();
        $this->seedFarmingLogs();
        $this->seedIncidentAndChemicalUsage();
        $this->seedPreHarvestInspection();
        $this->seedHarvestLot();
        $this->seedProcessingRecord();
        $this->seedPackingLot();
        $this->seedDeliveryAndReturns();
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

        // Lô A - đang trồng
        $plotA = Plot::firstOrCreate(
            ['farm_id' => $farm->id, 'code' => 'PLOT-A'],
            [
                'name' => 'Lô A - Nhà kính',
                'area_m2' => 1500.00,
                'status' => 'planting',
            ]
        );

        // Lô B - trống
        $plotB = Plot::firstOrCreate(
            ['farm_id' => $farm->id, 'code' => 'PLOT-B'],
            [
                'name' => 'Lô B - Ngoài trời',
                'area_m2' => 1000.00,
                'status' => 'available',
            ]
        );

        // Lô C - nghỉ đất
        $plotC = Plot::firstOrCreate(
            ['farm_id' => $farm->id, 'code' => 'PLOT-C'],
            [
                'name' => 'Lô C - Nghỉ đất',
                'area_m2' => 800.00,
                'status' => 'rest',
            ]
        );

        // Tạo luống cho Lô A
        $bedsA = [
            ['code' => 'A-01', 'length_m' => 50.00, 'width_m' => 3.00],
            ['code' => 'A-02', 'length_m' => 50.00, 'width_m' => 3.00],
            ['code' => 'A-03', 'length_m' => 50.00, 'width_m' => 3.00],
        ];

        foreach ($bedsA as $b) {
            Bed::firstOrCreate(
                ['plot_id' => $plotA->id, 'code' => $b['code']],
                [
                    'length_m' => $b['length_m'],
                    'width_m' => $b['width_m'],
                    'area_m2' => $b['length_m'] * $b['width_m'],
                    'expected_plants' => (int) ($b['length_m'] * $b['width_m'] * 20),
                    'status' => 'available',
                ]
            );
        }

        // Tạo luống cho Lô B
        $bedsB = [
            ['code' => 'B-01', 'length_m' => 40.00, 'width_m' => 2.50],
            ['code' => 'B-02', 'length_m' => 40.00, 'width_m' => 2.50],
        ];

        foreach ($bedsB as $b) {
            Bed::firstOrCreate(
                ['plot_id' => $plotB->id, 'code' => $b['code']],
                [
                    'length_m' => $b['length_m'],
                    'width_m' => $b['width_m'],
                    'area_m2' => $b['length_m'] * $b['width_m'],
                    'expected_plants' => (int) ($b['length_m'] * $b['width_m'] * 20),
                    'status' => 'available',
                ]
            );
        }
    }

    private function seedCropData(): void
    {
        // Rau muống - cây trồng chính
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

        // Variety
        $variety = CropVariety::firstOrCreate(
            ['crop_id' => $crop->id, 'code' => 'VM001'],
            [
                'name' => 'Muống Nhật (Japanese Water Spinach)',
                'avg_growth_days' => 25,
                'avg_yield_per_plant' => 0.15,
                'planting_density_per_m2' => 20,
                'disease_resistance' => 'Medium',
                'suitable_season' => 'All seasons',
                'suitable_climate_zone' => 'tropical',
                'status' => 'active',
            ]
        );

        // Growth Stages
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

        // Harvest Model
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

        // Loss Profile
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

        // Product Standard
        ProductStandard::firstOrCreate(
            ['crop_id' => $crop->id, 'code' => 'PS001'],
            [
                'name' => 'Rau muống loại 1',
                'allowed_defect_percent' => 5.00,
                'grade' => 'A',
            ]
        );

        // Labor Norm
        LaborNorm::firstOrCreate(
            ['crop_id' => $crop->id],
            [
                'variety_id' => $variety->id,
                'hours_per_m2' => 0.5,
                'cost_per_m2' => 15000.00,
            ]
        );

        // Irrigation Norm
        IrrigationNorm::firstOrCreate(
            ['crop_id' => $crop->id],
            [
                'variety_id' => $variety->id,
                'frequency' => 'daily',
                'water_amount' => 5.0,
                'unit' => 'liters_per_m2_per_day',
                'requires_actual_log' => false,
            ]
        );

        // Fertilizer Norm
        FertilizerNorm::firstOrCreate(
            ['crop_id' => $crop->id],
            [
                'variety_id' => $variety->id,
                'fertilizer_name' => 'NPK 16-16-8',
                'amount' => 0.5,
                'unit' => 'kg',
                'application_day_range' => '7-14',
            ]
        );

        // Price Table
        PriceTable::firstOrCreate(
            ['crop_id' => $crop->id],
            [
                'variety_id' => $variety->id,
                'unit' => 'kg',
                'grade_a_price' => 15000.00,
                'grade_b_price' => 12000.00,
                'grade_c_price' => 8000.00,
                'side_channel_price' => 10000.00,
                'effective_from' => now()->subMonth(),
                'effective_until' => now()->addMonths(3),
                'status' => 'active',
            ]
        );
    }

    private function seedSupplyChain(): void
    {
        $farm = Farm::where('code', 'FARM001')->first();
        $crop = Crop::where('name', 'Rau muống')->first();

        if (!$farm || !$crop) return;

        // Hợp đồng cung ứng - MM Mega Market
        $contract = SupplyContract::firstOrCreate(
            ['customer_name' => 'MM Mega Market Quận 7'],
            [
                'customer_type' => 'retail',
                'crop_id' => $crop->id,
                'quantity' => 10.00,
                'unit' => 'kg',
                'frequency' => 'daily',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addMonths(2),
                'status' => 'active',
                'notes' => 'Hợp đồng cung ứng rau muống hàng ngày cho siêu thị',
            ]
        );

        // Tạo nhu cầu hàng ngày (7 ngày gần nhất)
        for ($i = 0; $i < 7; $i++) {
            SupplyDemand::firstOrCreate(
                [
                    'crop_id' => $crop->id,
                    'quantity' => 10.00,
                    'unit' => 'kg',
                    'target_date' => now()->subDays($i)->format('Y-m-d'),
                ],
                [
                    'frequency' => 'once',
                    'status' => 'pending',
                    'supply_contract_id' => $contract->id,
                ]
            );
        }
    }

    private function seedProductionPlan(): void
    {
        $farm = Farm::where('code', 'FARM001')->first();
        $crop = Crop::where('name', 'Rau muống')->first();
        $variety = CropVariety::where('crop_id', $crop->id)->first();
        $contract = SupplyContract::first()->first();
        $demand = SupplyDemand::where('supply_contract_id', $contract->id)->first();

        if (!$farm || !$crop || !$demand) return;

        // Tính kế hoạch sản xuất
        // 10kg/ngày, hao hụt ~20%, cần thu thô ~12.5kg/ngày
        // Mật độ 20 cây/m2, yield 0.15kg/cây => 3kg/m2
        // Cần ~4.2m2/ngày, cho 1 tuần => ~30m2

        ProductionPlan::firstOrCreate(
            [
                'supply_demand_id' => $demand->id,
                'crop_id' => $crop->id,
            ],
            [
                'supply_contract_id' => $contract->id ?? null,
                'variety_id' => $variety->id,
                'farm_id' => $farm->id,
                'quantity' => 70.00, // 10kg x 7 ngày
                'unit' => 'kg',
                'target_delivery_date' => now()->addDays(7),
                'estimated_cost' => 450000.00, // 30m2 x 15000/m2 labor
                'estimated_revenue' => 1050000.00, // 70kg x 15000
                'estimated_margin' => 600000.00,
                'margin_percent' => 57.14,
                'status' => 'approved',
                'notes' => 'Kế hoạch sản xuất rau muống 1 tuần cho MM Mega Market',
            ]
        );
    }

    private function seedPlantingBatch(): void
    {
        $farm = Farm::where('code', 'FARM001')->first();
        $crop = Crop::where('name', 'Rau muống')->first();
        $variety = CropVariety::where('crop_id', $crop->id)->first();
        $plan = ProductionPlan::first();
        $plotA = Plot::where('code', 'PLOT-A')->first();

        if (!$farm || !$crop || !$plan) return;

        // Tính: 70kg / 0.15kg/cây = ~467 cây
        // Mật độ 20 cây/m2 => ~23.5m2
        // Lấy tròn 25m2 để an toàn

        $batch = PlantingBatch::firstOrCreate(
            ['code' => 'LUA-2026-001'],
            [
                'farm_id' => $farm->id,
                'crop_id' => $crop->id,
                'variety_id' => $variety->id ?? null,
                'production_plan_id' => $plan->id,
                'planned_quantity' => 70.00,
                'planned_unit' => 'kg',
                'planned_area_m2' => 25.00,
                'planned_start_date' => now()->subDays(25),
                'planned_harvest_date' => now()->addDays(7),
                'actual_quantity' => 65.00,
                'actual_area_m2' => 24.00,
                'actual_start_date' => now()->subDays(25),
                'status' => 'harvesting',
                'notes' => 'Lứa trồng rau muống cho MM Mega Market',
            ]
        );

        // Phân bổ lứa vào lô
        if ($plotA) {
            PlantingBatchAllocation::firstOrCreate(
                [
                    'planting_batch_id' => $batch->id,
                    'plot_id' => $plotA->id,
                ],
                [
                    'allocated_area_m2' => 24.00,
                    'status' => 'active',
                ]
            );
        }
    }

    private function seedWorkTasks(): void
    {
        $batch = PlantingBatch::first();
        $plotA = Plot::where('code', 'PLOT-A')->first();
        $farm = Farm::where('code', 'FARM001')->first();
        $worker = User::where('role', User::ROLE_WORKER)->first();
        $tech = User::where('role', User::ROLE_TECHNICIAN)->first();
        $manager = User::where('role', User::ROLE_FARM_MANAGER)->first();

        if (!$batch || !$plotA || !$farm) return;

        $stages = GrowthStage::where('crop_id', $batch->crop_id)->orderBy('order')->get();

        // Tính ngày bắt đầu dựa trên planned_start_date của batch
        $batchStart = $batch->planned_start_date ?? now()->subDays(25);
        $dayCounter = 0;

        foreach ($stages as $stage) {
            $taskStart = (clone $batchStart)->addDays($dayCounter);
            $taskEnd = (clone $taskStart)->addDays($stage->duration_days - 1);
            $dayCounter += $stage->duration_days;

            // Xác định người phụ trách và trạng thái
            $assignee = $stage->order <= 2 ? $worker : ($stage->order == 3 ? $tech : $manager);
            $status = $stage->order < 3 ? 'done' : ($stage->order == 3 ? 'in_progress' : 'assigned');

            WorkTask::firstOrCreate(
                [
                    'planting_batch_id' => $batch->id,
                    'growth_stage_id' => $stage->id,
                    'farm_id' => $farm->id,
                    'plot_id' => $plotA->id,
                ],
                [
                    'title' => $stage->name,
                    'task_type' => 'stage_task',
                    'assigned_user_id' => $assignee?->id,
                    'status' => $status,
                    'priority' => 'normal',
                    'planned_start_date' => $taskStart,
                    'planned_due_date' => $taskEnd,
                    'started_at' => $status != 'assigned' ? $taskStart : null,
                    'completed_at' => $status == 'done' ? $taskEnd : null,
                    'instructions' => "Thực hiện {$stage->name} cho lứa LUA-2026-001. Thời gian dự kiến: {$stage->duration_days} ngày.",
                    'completion_note' => $status == 'done' ? "Đã hoàn thành {$stage->name} đúng tiến độ" : null,
                    'metadata' => [
                        'stage_order' => $stage->order,
                        'duration_days' => $stage->duration_days,
                    ],
                ]
            );
        }
    }

    private function seedFarmingLogs(): void
    {
        $batch = PlantingBatch::first();
        $farm = Farm::where('code', 'FARM001')->first();
        $worker = User::where('role', User::ROLE_WORKER)->first();
        $tech = User::where('role', User::ROLE_TECHNICIAN)->first();

        if (!$batch) return;

        // Lấy các task đã hoàn thành
        $tasks = WorkTask::where('planting_batch_id', $batch->id)
            ->whereIn('status', ['done', 'in_progress'])
            ->get();

        foreach ($tasks as $task) {
            $actor = $task->assigned_user_id == $worker->id ? $worker : $tech;
            $loggedAt = $task->completed_at ?? $task->planned_due_date ?? now();

            FarmingLog::firstOrCreate(
                [
                    'work_task_id' => $task->id,
                    'farm_id' => $farm->id,
                    'logged_at' => $loggedAt,
                ],
                [
                    'reported_by_user_id' => $actor?->id,
                    'planting_batch_id' => $batch->id,
                    'plot_id' => $task->plot_id,
                    'notes' => "Da hoan thanh: {$task->title}",
                    'actual_start_at' => $task->started_at ?? $task->planned_start_date,
                    'actual_end_at' => $task->completed_at ?? $task->planned_due_date,
                    'metadata' => [
                        'weather' => 'nang',
                        'temperature' => '28-32C',
                    ],
                ]
            );
        }
    }

    private function seedIncidentAndChemicalUsage(): void
    {
        $batch = PlantingBatch::first();
        $farm = Farm::where('code', 'FARM001')->first();
        $tech = User::where('role', User::ROLE_TECHNICIAN)->first();

        if (!$batch || !$farm) return;

        // Ghi nhận sâu bệnh
        $incident = Incident::firstOrCreate(
            [
                'farm_id' => $farm->id,
                'planting_batch_id' => $batch->id,
                'reported_by_user_id' => $tech?->id,
            ],
            [
                'incident_type' => 'pest',
                'description' => 'Phat hien rep sap tren la rau muong, mat do thap',
                'severity' => 'medium',
                'detected_at' => now()->subDays(10),
                'status' => 'resolved',
            ]
        );

        // Ghi nhận sử dụng thuốc sinh học
        ChemicalUsage::firstOrCreate(
            [
                'incident_id' => $incident->id,
                'planting_batch_id' => $batch->id,
            ],
            [
                'farm_id' => $farm->id,
                'product_name' => 'Confidor',
                'product_type' => 'biological',
                'active_ingredient' => 'Imidacloprid',
                'dosage_value' => 5.00,
                'dosage_unit' => 'g/L',
                'quantity_value' => 50.00,
                'quantity_unit' => 'gram',
                'cost_amount' => 150000.00,
                'applied_by_user_id' => $tech?->id,
                'applied_at' => now()->subDays(10),
                'isolation_days' => 7,
                'isolation_ends_at' => now()->subDays(3),
                'notes' => 'Phun xu ly rep sap, dam bao cach ly 7 ngay truoc thu hoach',
            ]
        );
    }

    private function seedPreHarvestInspection(): void
    {
        $batch = PlantingBatch::first();
        $farm = Farm::where('code', 'FARM001')->first();
        $tech = User::where('role', User::ROLE_TECHNICIAN)->first();
        $manager = User::where('role', User::ROLE_FARM_MANAGER)->first();

        if (!$batch || !$farm) return;

        // Nghiệm thu trước thu hoạch - ĐẠT
        PreHarvestInspection::firstOrCreate(
            [
                'planting_batch_id' => $batch->id,
                'farm_id' => $farm->id,
            ],
            [
                'inspector_user_id' => $tech?->id,
                'inspected_at' => now()->subDays(2),
                'status' => 'approved',
                'approved_at' => now()->subDays(2),
                'approved_by_user_id' => $manager?->id,
                'checklist' => [
                    ['key' => 'size_ok', 'label' => 'Kich thuoc dat chuan', 'passed' => true],
                    ['key' => 'color_ok', 'label' => 'Mau sac dat chuan', 'passed' => true],
                    ['key' => 'no_pest', 'label' => 'Khong sau benh', 'passed' => true],
                    ['key' => 'no_chemical', 'label' => 'Da het thoi gian cach ly', 'passed' => true],
                    ['key' => 'expected_yield_ok', 'label' => 'San luong du kien dat', 'passed' => true],
                ],
                'notes' => 'Nghiem thu dat, san sang thu hoach',
            ]
        );
    }

    private function seedHarvestLot(): void
    {
        $batch = PlantingBatch::first();
        $farm = Farm::where('code', 'FARM001')->first();
        $plotA = Plot::where('code', 'PLOT-A')->first();
        $inspection = PreHarvestInspection::where('planting_batch_id', $batch->id)->first();
        $worker = User::where('role', User::ROLE_WORKER)->first();

        if (!$batch || !$farm) return;

        // Thu hoạch lần 1 - đạt
        HarvestLot::firstOrCreate(
            ['code' => 'TH-2026-001'],
            [
                'farm_id' => $farm->id,
                'planting_batch_id' => $batch->id,
                'pre_harvest_inspection_id' => $inspection?->id,
                'plot_id' => $plotA->id ?? null,
                'harvested_by_user_id' => $worker?->id,
                'harvest_date' => now()->subDays(1),
                'raw_quantity' => 25.00,
                'unit' => 'kg',
                'grade_a_quantity' => 21.00,
                'grade_b_quantity' => 2.50,
                'grade_c_quantity' => 0.75,
                'reject_quantity' => 0.75,
                'reject_reasons' => ['R05' => 0.50, 'R12' => 0.25], // Dập nát, không đạt cảm quan
                'status' => 'available',
                'notes' => 'Thu hoạch lần 1, chất lượng tốt',
            ]
        );

        // Thu hoạch lần 2 - tiếp tục
        HarvestLot::firstOrCreate(
            ['code' => 'TH-2026-002'],
            [
                'farm_id' => $farm->id,
                'planting_batch_id' => $batch->id,
                'pre_harvest_inspection_id' => $inspection?->id,
                'plot_id' => $plotA->id ?? null,
                'harvested_by_user_id' => $worker?->id,
                'harvest_date' => now(),
                'raw_quantity' => 20.00,
                'unit' => 'kg',
                'grade_a_quantity' => 17.00,
                'grade_b_quantity' => 2.00,
                'grade_c_quantity' => 0.60,
                'reject_quantity' => 0.40,
                'reject_reasons' => ['R04' => 0.40], // Sâu bệnh
                'status' => 'available',
                'notes' => 'Thu hoạch lần 2, có 1 ít sâu bệnh',
            ]
        );
    }

    private function seedProcessingRecord(): void
    {
        $harvest = HarvestLot::first();
        $farm = Farm::where('code', 'FARM001')->first();
        $worker = User::where('role', User::ROLE_WORKER)->first();

        if (!$harvest || !$farm) return;

        // Sơ chế - rửa, cắt gốc, loại lá hư
        $processingRecord = \App\Models\ProcessingRecord::firstOrCreate(
            [
                'harvest_lot_id' => $harvest->id,
                'farm_id' => $farm->id,
            ],
            [
                'processed_by_user_id' => $worker?->id,
                'processed_at' => now()->subHours(12),
                'input_quantity' => $harvest->raw_quantity,
                'output_quantity' => $harvest->raw_quantity * 0.98,
                'loss_quantity' => $harvest->raw_quantity * 0.02,
                'loss_rate' => 0.02,
                'unit' => 'kg',
                'status' => 'completed',
                'notes' => 'Cat goc, loai la hu, rua sach',
            ]
        );
    }

    private function seedPackingLot(): void
    {
        $farm = Farm::where('code', 'FARM001')->first();
        $worker = User::where('role', User::ROLE_WORKER)->first();
        $harvests = HarvestLot::where('status', 'available')->get();

        if (!$farm || $harvests->isEmpty()) return;

        // Tạo lô đóng gói
        $packing = PackingLot::firstOrCreate(
            ['code' => 'DG-2026-001'],
            [
                'farm_id' => $farm->id,
                'created_by_user_id' => $worker?->id,
                'packed_at' => now(),
                'status' => 'packed',
                'total_input_quantity' => $harvests->sum('raw_quantity'),
                'total_output_quantity' => $harvests->sum('raw_quantity') * 0.95, // Hao hụt đóng gói
                'unit' => 'kg',
                'qr_code' => 'QR-' . uniqid(),
                'notes' => 'Lô đóng gói rau muống cho MM Mega Market',
            ]
        );

        // Gắn nguồn harvest vào packing lot
        foreach ($harvests as $harvest) {
            PackingLotSource::firstOrCreate(
                [
                    'packing_lot_id' => $packing->id,
                    'harvest_lot_id' => $harvest->id,
                ],
                [
                    'farm_id' => $farm->id,
                    'quantity' => $harvest->raw_quantity,
                    'unit' => 'kg',
                ]
            );

            // Cập nhật trạng thái harvest lot
            $harvest->update(['status' => 'packed']);
        }
    }

    private function seedDeliveryAndReturns(): void
    {
        $packing = PackingLot::first();
        $farm = Farm::where('code', 'FARM001')->first();
        $contract = SupplyContract::first();
        $delivery = User::where('role', User::ROLE_DELIVERY)->first();

        if (!$packing || !$farm || !$contract) return;

        // Tạo phiếu giao hàng
        $deliveryNote = DeliveryNote::firstOrCreate(
            ['code' => 'GH-2026-001'],
            [
                'farm_id' => $farm->id,
                'packing_lot_id' => $packing->id,
                'supply_contract_id' => $contract->id,
                'delivered_by_user_id' => $delivery?->id,
                'customer_name' => 'MM Mega Market Quận 7',
                'customer_type' => 'supermarket',
                'delivered_at' => now()->subHours(6),
                'planned_quantity' => 40.00,
                'accepted_quantity' => 38.00,
                'returned_quantity' => 2.00,
                'net_quantity' => 38.00,
                'unit' => 'kg',
                'unit_price' => 15000.00,
                'gross_revenue' => 570000.00, // 38 x 15000
                'return_deduction' => 30000.00, // 2 x 15000
                'side_channel_revenue' => 0,
                'net_revenue' => 540000.00,
                'status' => 'accepted',
                'notes' => 'Giao hàng đạt yêu cầu, có 2kg trả do hư nhẹ',
            ]
        );

        // Biên bản giao nhận
        DeliveryAcceptanceRecord::firstOrCreate(
            ['delivery_note_id' => $deliveryNote->id],
            [
                'accepted_by_name' => 'Nguyen Thi X',
                'accepted_at' => now()->subHours(5),
                'accepted_quantity' => 38.00,
                'rejected_quantity' => 2.00,
                'notes' => 'Hang dat chuan, co 2kg tra do dap nhat trong van chuyen',
            ]
        );

        // Ghi nhận trả hàng
        \App\Models\ReturnRecord::firstOrCreate(
            [
                'delivery_note_id' => $deliveryNote->id,
                'packing_lot_id' => $packing->id,
                'farm_id' => $farm->id,
            ],
            [
                'recorded_by_user_id' => $delivery?->id,
                'returned_at' => now()->subHours(4),
                'quantity' => 2.00,
                'unit' => 'kg',
                'reason' => 'bruised_wilted',
                'handling_action' => 'discard',
                'revenue_deduction' => 30000.00,
                'notes' => 'Hang bi dap nhat trong van chuyen, khong dat ban',
            ]
        );

        // Cập nhật trạng thái packing lot
        $packing->update(['status' => 'published']);
    }
}