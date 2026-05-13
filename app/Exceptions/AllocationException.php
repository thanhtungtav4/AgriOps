<?php

namespace App\Exceptions;

class AllocationException extends \DomainException
{
    public const CODE_FARM_MISMATCH = 'farm_mismatch';
    public const CODE_PLOT_UNAVAILABLE = 'plot_unavailable';
    public const CODE_AREA_EXCEEDS_PLOT = 'area_exceeds_plot';
    public const CODE_DUPLICATE_ALLOCATION = 'duplicate_allocation';

    public function __construct(
        string $message = '',
        public readonly ?string $errorCode = null,
        public readonly array $context = []
    ) {
        parent::__construct($message);
    }

    public static function farmMismatch(int $batchFarmId, int $plotFarmId): self
    {
        return new self(
            "Cannot allocate: plot belongs to farm {$plotFarmId} but batch belongs to farm {$batchFarmId}",
            self::CODE_FARM_MISMATCH,
            [
                'batch_farm_id' => $batchFarmId,
                'plot_farm_id' => $plotFarmId,
            ]
        );
    }

    public static function plotUnavailable(string $status): self
    {
        return new self(
            "Cannot allocate: plot status '{$status}' is not available",
            self::CODE_PLOT_UNAVAILABLE,
            [
                'plot_status' => $status,
                'allowed_statuses' => ['available'],
            ]
        );
    }

    public static function areaExceedsPlot(float $allocatedArea, float $plotArea): self
    {
        return new self(
            "Cannot allocate: allocated area ({$allocatedArea} m²) exceeds plot area ({$plotArea} m²)",
            self::CODE_AREA_EXCEEDS_PLOT,
            [
                'allocated_area_m2' => $allocatedArea,
                'plot_area_m2' => $plotArea,
            ]
        );
    }

    public static function duplicateAllocation(int $batchId, int $plotId, ?int $bedId = null): self
    {
        $bedContext = $bedId !== null ? " and bed {$bedId}" : '';
        return new self(
            "Cannot allocate: batch {$batchId} already has allocation to plot {$plotId}{$bedContext}",
            self::CODE_DUPLICATE_ALLOCATION,
            [
                'planting_batch_id' => $batchId,
                'plot_id' => $plotId,
                'bed_id' => $bedId,
            ]
        );
    }

    public function toArray(): array
    {
        $result = [
            'error' => 'allocation_error',
            'message' => $this->getMessage(),
        ];

        if ($this->errorCode !== null) {
            $result['code'] = $this->errorCode;
        }

        if (!empty($this->context)) {
            $result['context'] = $this->context;
        }

        return $result;
    }
}