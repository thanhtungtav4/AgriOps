<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\ChemicalProduct;
use App\Models\HarvestLot;
use App\Models\PlantingBatch;
use App\Models\User;
use App\Models\WorkTask;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AlertService
{
    public function trigger(?int $farmId = null): Collection
    {
        return collect()
            ->merge($this->triggerOverdueWorkTaskAlerts($farmId))
            ->merge($this->triggerYieldShortfallAlerts($farmId))
            ->merge($this->triggerChemicalLowStockAlerts($farmId))
            ->merge($this->triggerChemicalExpiryAlerts($farmId))
            ->merge($this->triggerHarvestDueAlerts($farmId))
            ->merge($this->triggerHarvestLotPendingAlerts($farmId))
            ->merge($this->triggerPostSeasonReviewPendingAlerts($farmId));
    }

    // ─── Work Task Overdue Alerts ───────────────────────────────

    public function triggerOverdueWorkTaskAlerts(?int $farmId = null, ?CarbonInterface $asOf = null): Collection
    {
        $asOf ??= now();

        return WorkTask::with(['plantingBatch.crop', 'farm'])
            ->whereNotNull('planned_due_date')
            ->whereDate('planned_due_date', '<=', $asOf->toDateString())
            ->whereIn('status', ['planned', 'assigned', 'in_progress'])
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->get()
            ->map(fn (WorkTask $task) => $this->createOrUpdateAlert([
                'farm_id' => $task->farm_id,
                'recipient_role' => User::ROLE_FARM_MANAGER,
                'alert_type' => 'work_task_overdue',
                'severity' => $task->priority === 'urgent' ? 'critical' : 'warning',
                'title' => "Công việc quá hạn: {$task->title}",
                'message' => "Công việc '{$task->title}' đã quá hạn cho farm {$task->farm?->code}.",
                'source_type' => WorkTask::class,
                'source_id' => $task->id,
                'context' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'task_status' => $task->status,
                    'due_date' => $task->planned_due_date?->toDateString(),
                    'crop_id' => $task->plantingBatch?->crop_id,
                    'crop_name' => $task->plantingBatch?->crop?->name,
                    'batch_id' => $task->planting_batch_id,
                    'priority' => $task->priority,
                    'recipient_role' => User::ROLE_FARM_MANAGER,
                ],
            ]));
    }

    // ─── Yield Shortfall Alerts ────────────────────────────────

    public function triggerYieldShortfallAlerts(?int $farmId = null): Collection
    {
        return PlantingBatch::with(['crop', 'farm'])
            ->whereNotNull('planned_quantity')
            ->whereNotNull('actual_quantity')
            ->whereColumn('actual_quantity', '<', 'planned_quantity')
            ->whereIn('status', ['growing', 'harvesting', 'harvested', 'completed'])
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->get()
            ->map(function (PlantingBatch $batch) {
                $gapQuantity = (float) $batch->planned_quantity - (float) $batch->actual_quantity;
                $shortfallPercent = ((float) $batch->planned_quantity > 0)
                    ? round(($gapQuantity / (float) $batch->planned_quantity) * 100, 1)
                    : 0;

                return $this->createOrUpdateAlert([
                    'farm_id' => $batch->farm_id,
                    'recipient_role' => User::ROLE_FARM_MANAGER,
                    'alert_type' => 'yield_shortfall',
                    'severity' => $shortfallPercent >= 25 ? 'critical' : 'warning',
                    'title' => "Sản lượng thấp: {$batch->code}",
                    'message' => "Lô {$batch->code} thiếu {$gapQuantity} {$batch->planned_unit} so với kế hoạch (-{$shortfallPercent}%).",
                    'source_type' => PlantingBatch::class,
                    'source_id' => $batch->id,
                    'context' => [
                        'batch_id' => $batch->id,
                        'batch_code' => $batch->code,
                        'crop_id' => $batch->crop_id,
                        'crop_name' => $batch->crop?->name,
                        'planned_quantity' => (float) $batch->planned_quantity,
                        'actual_quantity' => (float) $batch->actual_quantity,
                        'gap_quantity' => round($gapQuantity, 3),
                        'shortfall_percent' => $shortfallPercent,
                        'unit' => $batch->planned_unit,
                        'recipient_role' => User::ROLE_FARM_MANAGER,
                    ],
                ]);
            });
    }

    // ─── Chemical Low Stock Alerts ─────────────────────────────

    public function triggerChemicalLowStockAlerts(?int $farmId = null): Collection
    {
        return ChemicalProduct::with(['farm'])
            ->active()
            ->lowStock()
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->get()
            ->map(function (ChemicalProduct $product) {
                $deficit = (float) $product->min_stock_level - (float) $product->stock_quantity;

                return $this->createOrUpdateAlert([
                    'farm_id' => $product->farm_id,
                    'recipient_role' => User::ROLE_WAREHOUSE,
                    'alert_type' => 'chemical_low_stock',
                    'severity' => 'warning',
                    'title' => "Tồn kho thấp: {$product->name}",
                    'message' => "Sản phẩm hóa chất {$product->name} đang ở mức tồn kho thấp. Cần nhập thêm {$deficit} {$product->unit}.",
                    'source_type' => ChemicalProduct::class,
                    'source_id' => $product->id,
                    'context' => [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_type' => $product->type,
                        'current_stock' => (float) $product->stock_quantity,
                        'min_stock_level' => (float) $product->min_stock_level,
                        'deficit' => round($deficit, 3),
                        'unit' => $product->unit,
                        'supplier' => $product->supplier,
                        'recipient_role' => User::ROLE_WAREHOUSE,
                    ],
                ]);
            });
    }

    // ─── Chemical Expiry Alerts ───────────────────────────────

    public function triggerChemicalExpiryAlerts(?int $farmId = null, int $warningDays = 30): Collection
    {
        $warningDate = now()->addDays($warningDays);

        return ChemicalProduct::with(['farm'])
            ->active()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $warningDate)
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->get()
            ->map(function (ChemicalProduct $product) {
                $daysUntilExpiry = now()->diffInDays($product->expiry_date, false);
                $isExpired = $daysUntilExpiry < 0;
                $severity = $isExpired ? 'critical' : ($daysUntilExpiry <= 7 ? 'warning' : 'info');

                return $this->createOrUpdateAlert([
                    'farm_id' => $product->farm_id,
                    'recipient_role' => User::ROLE_WAREHOUSE,
                    'alert_type' => 'chemical_expiry',
                    'severity' => $severity,
                    'title' => $isExpired ? "Hết hạn: {$product->name}" : "Sắp hết hạn: {$product->name}",
                    'message' => $isExpired
                        ? "Sản phẩm {$product->name} đã hết hạn (hết hạn: {$product->expiry_date->format('d/m/Y')}). Cần xử lý."
                        : "Sản phẩm {$product->name} sẽ hết hạn trong {$daysUntilExpiry} ngày.",
                    'source_type' => ChemicalProduct::class,
                    'source_id' => $product->id,
                    'context' => [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_type' => $product->type,
                        'expiry_date' => $product->expiry_date->toDateString(),
                        'days_until_expiry' => (int) $daysUntilExpiry,
                        'is_expired' => $isExpired,
                        'current_stock' => (float) $product->stock_quantity,
                        'unit' => $product->unit,
                        'recipient_role' => User::ROLE_WAREHOUSE,
                    ],
                ]);
            });
    }

    // ─── Harvest Due Alerts ────────────────────────────────────

    public function triggerHarvestDueAlerts(?int $farmId = null, int $warningDays = 3): Collection
    {
        $warningDate = now()->addDays($warningDays);

        return PlantingBatch::with(['crop', 'farm'])
            ->whereIn('status', ['growing', 'ready_to_harvest'])
            ->whereNotNull('planned_harvest_date')
            ->whereDate('planned_harvest_date', '<=', $warningDate)
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->get()
            ->map(function (PlantingBatch $batch) {
                $daysUntilHarvest = now()->diffInDays($batch->planned_harvest_date, false);
                $isOverdue = $daysUntilHarvest < 0;

                return $this->createOrUpdateAlert([
                    'farm_id' => $batch->farm_id,
                    'recipient_role' => User::ROLE_FARM_MANAGER,
                    'alert_type' => 'harvest_due',
                    'severity' => $isOverdue ? 'critical' : 'warning',
                    'title' => $isOverdue ? "Quá hạn thu hoạch: {$batch->code}" : "Sắp đến hạn thu hoạch: {$batch->code}",
                    'message' => $isOverdue
                        ? "Lô {$batch->code} đã quá hạn thu hoạch ({$batch->planned_harvest_date->format('d/m/Y')})."
                        : "Lô {$batch->code} cần thu hoạch trong {$daysUntilHarvest} ngày.",
                    'source_type' => PlantingBatch::class,
                    'source_id' => $batch->id,
                    'context' => [
                        'batch_id' => $batch->id,
                        'batch_code' => $batch->code,
                        'crop_id' => $batch->crop_id,
                        'crop_name' => $batch->crop?->name,
                        'planned_harvest_date' => $batch->planned_harvest_date->toDateString(),
                        'days_until_harvest' => (int) $daysUntilHarvest,
                        'is_overdue' => $isOverdue,
                        'planned_quantity' => (float) $batch->planned_quantity,
                        'actual_quantity' => $batch->actual_quantity ? (float) $batch->actual_quantity : null,
                        'unit' => $batch->planned_unit,
                        'recipient_role' => User::ROLE_FARM_MANAGER,
                    ],
                ]);
            });
    }

    // ─── Harvest Lot Pending Alerts ──────────────────────────

    public function triggerHarvestLotPendingAlerts(?int $farmId = null, int $pendingDays = 2): Collection
    {
        $cutoffDate = now()->subDays($pendingDays);

        return HarvestLot::with(['plantingBatch.crop', 'farm'])
            ->where('status', 'available')
            ->where('harvest_date', '<=', $cutoffDate)
            ->whereDoesntHave('packingSources')
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->get()
            ->map(function (HarvestLot $lot) {
                $pendingDaysCount = now()->diffInDays($lot->harvest_date);

                return $this->createOrUpdateAlert([
                    'farm_id' => $lot->farm_id,
                    'recipient_role' => User::ROLE_WAREHOUSE,
                    'alert_type' => 'harvest_lot_pending',
                    'severity' => $pendingDaysCount >= 3 ? 'warning' : 'info',
                    'title' => "Lô thu hoạch chưa đóng gói: {$lot->code}",
                    'message' => "Lô {$lot->code} đã thu hoạch {$pendingDaysCount} ngày nhưng chưa được đóng gói.",
                    'source_type' => HarvestLot::class,
                    'source_id' => $lot->id,
                    'context' => [
                        'lot_id' => $lot->id,
                        'lot_code' => $lot->code,
                        'harvest_date' => $lot->harvest_date->toDateString(),
                        'pending_days' => $pendingDaysCount,
                        'raw_quantity' => (float) $lot->raw_quantity,
                        'grade_a_quantity' => (float) $lot->grade_a_quantity,
                        'grade_b_quantity' => (float) $lot->grade_b_quantity,
                        'grade_c_quantity' => (float) $lot->grade_c_quantity,
                        'unit' => $lot->unit,
                        'crop_id' => $lot->plantingBatch?->crop_id,
                        'crop_name' => $lot->plantingBatch?->crop?->name,
                        'batch_id' => $lot->planting_batch_id,
                        'recipient_role' => User::ROLE_WAREHOUSE,
                    ],
                ]);
            });
    }

    // ─── Post-Season Review Pending Alerts ────────────────────

    public function triggerPostSeasonReviewPendingAlerts(?int $farmId = null): Collection
    {
        // Get completed plans that don't have a review yet
        return \App\Models\ProductionPlan::with(['farm', 'postSeasonReview'])
            ->where('status', 'completed')
            ->whereDoesntHave('postSeasonReview')
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->get()
            ->map(function (\App\Models\ProductionPlan $plan) {
                return $this->createOrUpdateAlert([
                    'farm_id' => $plan->farm_id,
                    'recipient_role' => User::ROLE_FARM_MANAGER,
                    'alert_type' => 'post_season_review_pending',
                    'severity' => 'info',
                    'title' => "Cần tạo đánh giá sau vụ: {$plan->code}",
                    'message' => "Kế hoạch sản xuất {$plan->code} đã hoàn thành nhưng chưa có đánh giá sau vụ.",
                    'source_type' => \App\Models\ProductionPlan::class,
                    'source_id' => $plan->id,
                    'context' => [
                        'plan_id' => $plan->id,
                        'plan_code' => $plan->code,
                        'farm_id' => $plan->farm_id,
                        'farm_name' => $plan->farm?->name,
                        'end_date' => $plan->actual_end_date?->toDateString(),
                        'days_since_completion' => $plan->actual_end_date
                            ? now()->diffInDays($plan->actual_end_date)
                            : null,
                        'recipient_role' => User::ROLE_FARM_MANAGER,
                    ],
                ]);
            });
    }

    // ─── Batch Status Alerts ───────────────────────────────────

    public function triggerBatchStatusAlerts(?int $farmId = null): Collection
    {
        // Alert when batch has been in a status too long
        return PlantingBatch::with(['crop', 'farm'])
            ->where('status', 'in_transit')
            ->whereDate('actual_start_date', '<', now()->subDays(7))
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->get()
            ->map(function (PlantingBatch $batch) {
                $daysInTransit = now()->diffInDays($batch->actual_start_date);

                return $this->createOrUpdateAlert([
                    'farm_id' => $batch->farm_id,
                    'recipient_role' => User::ROLE_FARM_MANAGER,
                    'alert_type' => 'batch_in_transit_long',
                    'severity' => 'info',
                    'title' => "Lô đang vận chuyển lâu: {$batch->code}",
                    'message' => "Lô {$batch->code} đang trong trạng thái vận chuyển {$daysInTransit} ngày.",
                    'source_type' => PlantingBatch::class,
                    'source_id' => $batch->id,
                    'context' => [
                        'batch_id' => $batch->id,
                        'batch_code' => $batch->code,
                        'status' => $batch->status,
                        'days_in_transit' => $daysInTransit,
                        'crop_id' => $batch->crop_id,
                        'crop_name' => $batch->crop?->name,
                        'recipient_role' => User::ROLE_FARM_MANAGER,
                    ],
                ]);
            });
    }

    // ─── Helper Methods ────────────────────────────────────────

    private function createOrUpdateAlert(array $data): Alert
    {
        $payload = $this->buildNotificationPayload($data);

        return Alert::updateOrCreate(
            [
                'alert_type' => $data['alert_type'],
                'source_type' => $data['source_type'],
                'source_id' => $data['source_id'],
                'recipient_role' => $data['recipient_role'] ?? null,
            ],
            [
                ...$data,
                'notification_payload' => $payload,
                'status' => 'unread',
                'read_at' => null,
            ],
        );
    }

    private function buildNotificationPayload(array $data): array
    {
        return [
            'channel' => 'in_app_push_ready',
            'title' => $data['title'],
            'body' => $data['message'],
            'severity' => $data['severity'],
            'type' => $data['alert_type'],
            'farm_id' => $data['farm_id'] ?? null,
            'recipient' => [
                'role' => $data['recipient_role'] ?? null,
                'user_id' => $data['recipient_user_id'] ?? null,
            ],
            'data' => $data['context'] ?? [],
        ];
    }
}