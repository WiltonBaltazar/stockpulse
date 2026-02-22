<?php

namespace App\Models;

use App\Support\MeasurementUnitConverter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    public const MEASUREMENT_MASS = 'mass';

    public const MEASUREMENT_VOLUME = 'volume';

    public const MEASUREMENT_UNIT = 'unit';

    protected $fillable = [
        'user_id',
        'name',
        'package_quantity_g',
        'package_cost',
        'stock_quantity_g',
        'reorder_level_g',
        'measurement_type',
        'preferred_unit',
        'density_g_per_ml',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'package_quantity_g' => 'float',
            'package_cost' => 'float',
            'stock_quantity_g' => 'float',
            'reorder_level_g' => 'float',
            'density_g_per_ml' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function recipeItems(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function getCostPerGramAttribute(): float
    {
        if ((float) $this->package_quantity_g <= 0.0) {
            return 0.0;
        }

        return (float) $this->package_cost / (float) $this->package_quantity_g;
    }

    public function getCostPerBaseUnitAttribute(): float
    {
        return $this->cost_per_gram;
    }

    public function getInventoryValueAttribute(): float
    {
        return max((float) $this->stock_quantity_g, 0.0) * $this->cost_per_gram;
    }

    public function getIsLowStockAttribute(): bool
    {
        return (float) $this->reorder_level_g > 0
            && (float) $this->stock_quantity_g <= (float) $this->reorder_level_g;
    }

    public function getMeasurementTypeAttribute(?string $value): string
    {
        $resolved = (string) ($value ?: self::MEASUREMENT_MASS);

        if (! in_array($resolved, [self::MEASUREMENT_MASS, self::MEASUREMENT_VOLUME, self::MEASUREMENT_UNIT], true)) {
            return self::MEASUREMENT_MASS;
        }

        return $resolved;
    }

    public function getPreferredUnitAttribute(?string $value): string
    {
        $resolved = (string) ($value ?: self::baseUnitForType($this->measurement_type));

        if (! in_array($resolved, self::supportedUnitsForType($this->measurement_type), true)) {
            return self::baseUnitForType($this->measurement_type);
        }

        return $resolved;
    }

    public function getDensityGPerMlAttribute(?float $value): ?float
    {
        if ($this->measurement_type === self::MEASUREMENT_UNIT) {
            return null;
        }

        $resolved = (float) ($value ?? 0);

        return $resolved > 0 ? $resolved : 1.0;
    }

    public function baseUnit(): string
    {
        return self::baseUnitForType($this->measurement_type);
    }

    /**
     * @return array<string, string>
     */
    public static function measurementTypeOptions(): array
    {
        return [
            self::MEASUREMENT_MASS => 'Massa (kg/g)',
            self::MEASUREMENT_VOLUME => 'Volume (L/ml)',
            self::MEASUREMENT_UNIT => 'Unidade (un)',
        ];
    }

    public static function baseUnitForType(?string $measurementType): string
    {
        return match ($measurementType) {
            self::MEASUREMENT_VOLUME => MeasurementUnitConverter::UNIT_ML,
            self::MEASUREMENT_UNIT => MeasurementUnitConverter::UNIT_UNIT,
            default => MeasurementUnitConverter::UNIT_G,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function supportedUnitsForType(?string $measurementType): array
    {
        return match ($measurementType) {
            self::MEASUREMENT_VOLUME => MeasurementUnitConverter::volumeUnits(),
            self::MEASUREMENT_UNIT => MeasurementUnitConverter::unitUnits(),
            default => MeasurementUnitConverter::massUnits(),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function inputUnitsForType(?string $measurementType): array
    {
        $units = match ($measurementType) {
            self::MEASUREMENT_VOLUME => array_merge(MeasurementUnitConverter::volumeUnits(), MeasurementUnitConverter::massUnits()),
            self::MEASUREMENT_UNIT => MeasurementUnitConverter::unitUnits(),
            default => array_merge(MeasurementUnitConverter::massUnits(), MeasurementUnitConverter::volumeUnits()),
        };

        $all = MeasurementUnitConverter::commonUnitOptions();

        return collect($units)
            ->mapWithKeys(fn (string $unit): array => [$unit => $all[$unit] ?? strtoupper($unit)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function preferredUnitOptionsForType(?string $measurementType): array
    {
        $units = self::supportedUnitsForType($measurementType);
        $all = MeasurementUnitConverter::commonUnitOptions();

        return collect($units)
            ->mapWithKeys(fn (string $unit): array => [$unit => $all[$unit] ?? strtoupper($unit)])
            ->all();
    }

    public static function baseUnitLabel(?string $measurementType): string
    {
        return MeasurementUnitConverter::shortUnitLabel(self::baseUnitForType($measurementType));
    }

    public function formatBaseQuantity(float $baseValue): string
    {
        $displayUnit = $this->preferred_unit;
        $displayValue = MeasurementUnitConverter::fromBase(
            baseValue: $baseValue,
            unit: $displayUnit,
            measurementType: $this->measurement_type,
            densityGPerMl: $this->density_g_per_ml,
        );

        return self::formatQuantityNumber($displayValue).' '.MeasurementUnitConverter::shortUnitLabel($displayUnit);
    }

    private static function formatQuantityNumber(float $value): string
    {
        $rounded = round($value, 3);
        $formatted = number_format($rounded, 3, ',', '.');

        return rtrim(rtrim($formatted, '0'), ',');
    }
}
