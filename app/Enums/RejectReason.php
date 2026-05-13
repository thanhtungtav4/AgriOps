<?php

namespace App\Enums;

/**
 * Canonical reject reason taxonomy for harvest quality classification.
 *
 * Mapped from BRD quality requirements:
 * - Grade A/B/C/reject must map with product standard and reject reason canonical
 * - Reject reasons must be normalized for consistent reporting across harvest lots
 * - "Other" category requires mandatory note for audit trail
 */
enum RejectReason: string
{
    public const VALUES = [
        'disease_pest_damage',
        'physical_damage',
        'size_weight_out_of_spec',
        'color_maturity_out_of_spec',
        'contamination',
        'overripe',
        'underripe',
        'deformed',
        'other',
    ];

    case DISEASE_PEST_DAMAGE = 'disease_pest_damage';
    case PHYSICAL_DAMAGE = 'physical_damage';
    case SIZE_WEIGHT_OUT_OF_SPEC = 'size_weight_out_of_spec';
    case COLOR_MATURITY_OUT_OF_SPEC = 'color_maturity_out_of_spec';
    case CONTAMINATION = 'contamination';
    case OVERRIPE = 'overripe';
    case UNDERRIPE = 'underripe';
    case DEFORMED = 'deformed';
    case OTHER = 'other';

    /**
     * Get all valid reject reason values for validation.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return self::VALUES;
    }

    /**
     * Check if a given value is a valid reject reason.
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    /**
     * Get human-readable label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::DISEASE_PEST_DAMAGE => 'Disease/Pest Damage',
            self::PHYSICAL_DAMAGE => 'Physical Damage (bruising, cutting)',
            self::SIZE_WEIGHT_OUT_OF_SPEC => 'Size/Weight Out of Spec',
            self::COLOR_MATURITY_OUT_OF_SPEC => 'Color/Maturity Out of Spec',
            self::CONTAMINATION => 'Contamination',
            self::OVERRIPE => 'Overripe',
            self::UNDERRIPE => 'Underripe',
            self::DEFORMED => 'Deformed',
            self::OTHER => 'Other (see notes)',
        };
    }

    /**
     * Check if this reason requires a note (for OTHER category).
     */
    public function requiresNote(): bool
    {
        return $this === self::OTHER;
    }
}
