<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\Crop;
use App\Models\Farm;
use Carbon\Carbon;

class PlanningService
{
    private const CANONICAL_UNITS = ['kg', 'trái', 'bó', 'thùng'];

    public function calculate(array $input): array
    {
        $crop = Crop::with(['lossProfiles', 'harvestModels', 'laborNorms'])->find($input['crop_id']);

        if (!$crop) {
            throw new DomainException("Crop not found with ID: {$input['crop_id']}");
        }

        $quantityNeeded = (float) $input['quantity'];
        $unit = $input['unit'];

        // Validate canonical unit
        if (!in_array($unit, self::CANONICAL_UNITS, true)) {
            throw new DomainException(
                "Unsupported unit '{$unit}'. Canonical units are: " . implode(', ', self::CANONICAL_UNITS),
                'unit',
                ['supported_units' => self::CANONICAL_UNITS]
            );
        }
        $varietyId = $input['variety_id'] ?? null;
        $farmId = $input['farm_id'] ?? null;

        $lossProfile = $this->selectNorm($crop->lossProfiles, $varietyId);
        if (!$lossProfile) {
            throw new DomainException("Missing loss profile for crop '{$crop->name}'. Configure harvest, processing, packing, grade, and reject loss percentages.");
        }

        $harvestModel = $this->selectNorm($crop->harvestModels, $varietyId);
        if (!$harvestModel) {
            throw new DomainException("Missing harvest model for crop '{$crop->name}'. Configure average yield per plant, planting density, and survival rate.");
        }

        $laborNorm = $this->selectNorm($crop->laborNorms, $varietyId);
        if (!$laborNorm) {
            throw new DomainException("Missing labor norm for crop '{$crop->name}'. Configure hours per m2 and cost per m2.");
        }

        $totalLossPercent = $lossProfile->harvest_loss_percent 
            + $lossProfile->processing_loss_percent 
            + $lossProfile->packing_loss_percent 
            + $lossProfile->non_grade_a_percent 
            + $lossProfile->reject_percent;

        if ($totalLossPercent >= 100) {
            throw new DomainException("Invalid loss profile for crop '{$crop->name}'. Total loss percent must be below 100.");
        }

        $grossYield = $quantityNeeded / (1 - ($totalLossPercent / 100));

        $plantsNeeded = $harvestModel->avg_yield_per_plant > 0 
            ? $grossYield / $harvestModel->avg_yield_per_plant 
            : 0;

        $plantsToPlant = $harvestModel->survival_rate > 0 
            ? $plantsNeeded / ($harvestModel->survival_rate / 100) 
            : $plantsNeeded;

        $areaM2 = $harvestModel->planting_density_per_m2 > 0 
            ? $plantsToPlant / $harvestModel->planting_density_per_m2 
            : 0;

        $estimatedWorkers = $laborNorm->hours_per_m2 > 0 
            ? ($areaM2 * $laborNorm->hours_per_m2) / 8 
            : 0;

        $estimatedLaborHours = $areaM2 * $laborNorm->hours_per_m2;
        $estimatedCost = $areaM2 * $laborNorm->cost_per_m2;

        $pricePerUnit = $crop->sale_price_per_unit ?? 0;
        $estimatedRevenue = $quantityNeeded * $pricePerUnit;

        $marginPercent = $estimatedRevenue > 0 
            ? (($estimatedRevenue - $estimatedCost) / $estimatedRevenue) * 100 
            : 0;

        $safetyWarnings = [];

        if ($totalLossPercent > 30) {
            $safetyWarnings[] = "High total loss rate ({$totalLossPercent}%) - consider improving processes";
        }

        if ($areaM2 > 1000) {
            $safetyWarnings[] = "Large area required ({$areaM2} m²) - verify farm capacity";
        }

        if ($estimatedWorkers > 50) {
            $safetyWarnings[] = "High labor requirement ({$estimatedWorkers} công) - plan workforce accordingly";
        }

        if ($marginPercent < 10 && $marginPercent > 0) {
            $safetyWarnings[] = "Low margin ({$marginPercent}%) - review cost structure";
        }

        if ($marginPercent < 0) {
            $safetyWarnings[] = "Negative margin ({$marginPercent}%) - current pricing does not cover costs";
        }

        // Build assumptions object
        $assumptions = [
            'formula_version' => 'planning_v1_flat_loss',
            'loss_model' => 'flat_sum_v1',
            'season' => $input['season'] ?? 'unspecified',
            'climate_zone' => $this->determineClimateZone($crop, $farmId ?? null),
            'season_factor' => 1.0,
            'climate_factor' => 1.0,
            'price_source' => 'crop.sale_price_per_unit',
            'rounding_profile' => 'mvp_v1',
        ];

        // Build fulfillment object
        $fulfillmentWarnings = [];
        if ($assumptions['season'] === 'unspecified') {
            $fulfillmentWarnings[] = 'season_not_specified';
        }
        if ($assumptions['climate_zone'] === 'unspecified') {
            $fulfillmentWarnings[] = 'climate_zone_not_specified';
        }
        if ($marginPercent < 10 && $marginPercent > 0) {
            $fulfillmentWarnings[] = 'low_margin';
        }
        if ($marginPercent < 0) {
            $fulfillmentWarnings[] = 'negative_margin';
        }

        $fulfillment = [
            'status' => empty($fulfillmentWarnings) ? 'feasible' : 'warning',
            'shortages' => [],
            'warnings' => $fulfillmentWarnings,
        ];

        return [
            'input' => [
                'quantity' => $quantityNeeded,
                'unit' => $unit,
                'frequency' => $input['frequency'],
                'crop_id' => $crop->id,
                'crop_name' => $crop->name,
                'variety_id' => $varietyId,
                'farm_id' => $farmId,
                'target_date' => $input['target_date'],
            ],
            'output' => [
                'delivery_quantity' => round($quantityNeeded, 2),
                'raw_harvest_quantity' => round($grossYield, 2),
                'gross_yield' => round($grossYield, 2),
                'plants_needed_estimate' => round($plantsNeeded, 2),
                'plants_needed_execution' => (int) ceil($plantsNeeded),
                'plants_to_plant_estimate' => round($plantsToPlant, 2),
                'plants_to_plant_execution' => (int) ceil($plantsToPlant),
                'area_m2' => round($areaM2, 2),
                'labor_hours' => round($estimatedLaborHours, 2),
                'estimated_workers' => round($estimatedWorkers, 2),
                'estimated_cost' => round($estimatedCost, 2),
                'estimated_revenue' => round($estimatedRevenue, 2),
                'margin_percent' => round($marginPercent, 2),
                'days_to_first_harvest' => (int) $harvestModel->days_to_first_harvest,
                'estimated_planting_date' => Carbon::parse($input['target_date'])
                    ->subDays((int) $harvestModel->days_to_first_harvest)
                    ->toDateString(),
                'estimated_first_harvest_date' => Carbon::parse($input['target_date'])->toDateString(),
            ],
            'fulfillment' => $fulfillment,
            'assumptions' => $assumptions,
            'norms_used' => [
                'loss_profile_id' => $lossProfile->id,
                'harvest_model_id' => $harvestModel->id,
                'labor_norm_id' => $laborNorm->id,
            ],
            'safety_warnings' => $safetyWarnings,
        ];
    }

    private function determineClimateZone($crop, ?int $farmId): string
    {
        // For MVP, return unspecified unless we have farm data with climate zone
        // This can be enhanced in future iterations
        if ($farmId) {
            $farm = Farm::find($farmId);
            if ($farm && isset($farm->climate_zone)) {
                return $farm->climate_zone;
            }
        }
        return 'unspecified';
    }

    private function selectNorm($norms, ?int $varietyId)
    {
        if ($varietyId) {
            $varietySpecific = $norms->firstWhere('variety_id', $varietyId);

            if ($varietySpecific) {
                return $varietySpecific;
            }
        }

        return $norms->first(fn ($norm) => $norm->variety_id === null) ?? $norms->first();
    }
}
