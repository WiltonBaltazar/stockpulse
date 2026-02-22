<?php

namespace App\Support;

use InvalidArgumentException;

class MeasurementUnitConverter
{
    public const UNIT_G = 'g';

    public const UNIT_KG = 'kg';

    public const UNIT_ML = 'ml';

    public const UNIT_L = 'l';

    public const UNIT_TSP = 'tsp';

    public const UNIT_TBSP = 'tbsp';

    public const UNIT_CUP = 'cup';

    public const UNIT_UNIT = 'un';

    private const ML_PER_TSP = 5.0;

    private const ML_PER_TBSP = 15.0;

    private const ML_PER_CUP = 240.0;

    /**
     * @return array<string, string>
     */
    public static function commonUnitOptions(): array
    {
        return [
            self::UNIT_G => 'g',
            self::UNIT_KG => 'kg',
            self::UNIT_ML => 'ml',
            self::UNIT_L => 'L',
            self::UNIT_TSP => 'colher de chá (5 ml)',
            self::UNIT_TBSP => 'colher de sopa (15 ml)',
            self::UNIT_CUP => 'chávena (240 ml)',
            self::UNIT_UNIT => 'unidade (un)',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function massUnits(): array
    {
        return [self::UNIT_G, self::UNIT_KG];
    }

    /**
     * @return array<int, string>
     */
    public static function volumeUnits(): array
    {
        return [self::UNIT_ML, self::UNIT_L, self::UNIT_TSP, self::UNIT_TBSP, self::UNIT_CUP];
    }

    /**
     * @return array<int, string>
     */
    public static function unitUnits(): array
    {
        return [self::UNIT_UNIT];
    }

    public static function normalizeNumber(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            return 0.0;
        }

        // Accept values such as 1.234,56 and 1234.56.
        $normalized = str_replace('.', '', $stringValue);
        $normalized = str_replace(',', '.', $normalized);

        if (! is_numeric($normalized)) {
            return 0.0;
        }

        return (float) $normalized;
    }

    public static function toBase(
        float $value,
        string $unit,
        string $measurementType,
        ?float $densityGPerMl = null,
    ): float {
        $value = max($value, 0.0);
        $density = max((float) ($densityGPerMl ?? 0.0), 0.0);

        return match ($measurementType) {
            'mass' => self::toMassBase($value, $unit, $density),
            'volume' => self::toVolumeBase($value, $unit, $density),
            'unit' => self::toUnitBase($value, $unit),
            default => throw new InvalidArgumentException('Tipo de medição inválido.'),
        };
    }

    public static function fromBase(
        float $baseValue,
        string $unit,
        string $measurementType,
        ?float $densityGPerMl = null,
    ): float {
        $baseValue = max($baseValue, 0.0);
        $density = max((float) ($densityGPerMl ?? 0.0), 0.0);

        return match ($measurementType) {
            'mass' => self::massFromBase($baseValue, $unit, $density),
            'volume' => self::volumeFromBase($baseValue, $unit, $density),
            'unit' => self::unitFromBase($baseValue, $unit),
            default => throw new InvalidArgumentException('Tipo de medição inválido.'),
        };
    }

    public static function unitLabel(string $unit): string
    {
        return self::commonUnitOptions()[$unit] ?? strtoupper($unit);
    }

    public static function shortUnitLabel(string $unit): string
    {
        return match ($unit) {
            self::UNIT_L => 'L',
            self::UNIT_TSP => 'c. chá',
            self::UNIT_TBSP => 'c. sopa',
            self::UNIT_CUP => 'chávena',
            default => $unit,
        };
    }

    private static function toMassBase(float $value, string $unit, float $density): float
    {
        if (in_array($unit, self::massUnits(), true)) {
            return match ($unit) {
                self::UNIT_G => $value,
                self::UNIT_KG => $value * 1000.0,
                default => $value,
            };
        }

        if (in_array($unit, self::volumeUnits(), true)) {
            if ($density <= 0.0) {
                throw new InvalidArgumentException('Defina a densidade do ingrediente para converter volume em massa.');
            }

            return self::volumeToMl($value, $unit) * $density;
        }

        throw new InvalidArgumentException('Unidade incompatível para ingredientes de massa.');
    }

    private static function toVolumeBase(float $value, string $unit, float $density): float
    {
        if (in_array($unit, self::volumeUnits(), true)) {
            return self::volumeToMl($value, $unit);
        }

        if (in_array($unit, self::massUnits(), true)) {
            if ($density <= 0.0) {
                throw new InvalidArgumentException('Defina a densidade do ingrediente para converter massa em volume.');
            }

            $grams = match ($unit) {
                self::UNIT_G => $value,
                self::UNIT_KG => $value * 1000.0,
                default => $value,
            };

            return $grams / $density;
        }

        throw new InvalidArgumentException('Unidade incompatível para ingredientes de volume.');
    }

    private static function toUnitBase(float $value, string $unit): float
    {
        if ($unit !== self::UNIT_UNIT) {
            throw new InvalidArgumentException('Ingredientes por unidade aceitam apenas unidade (un).');
        }

        return $value;
    }

    private static function massFromBase(float $baseValue, string $unit, float $density): float
    {
        if (in_array($unit, self::massUnits(), true)) {
            return match ($unit) {
                self::UNIT_G => $baseValue,
                self::UNIT_KG => $baseValue / 1000.0,
                default => $baseValue,
            };
        }

        if (in_array($unit, self::volumeUnits(), true)) {
            if ($density <= 0.0) {
                throw new InvalidArgumentException('Defina a densidade do ingrediente para converter massa em volume.');
            }

            return self::mlToUnit($baseValue / $density, $unit);
        }

        throw new InvalidArgumentException('Unidade incompatível para ingredientes de massa.');
    }

    private static function volumeFromBase(float $baseValue, string $unit, float $density): float
    {
        if (in_array($unit, self::volumeUnits(), true)) {
            return self::mlToUnit($baseValue, $unit);
        }

        if (in_array($unit, self::massUnits(), true)) {
            if ($density <= 0.0) {
                throw new InvalidArgumentException('Defina a densidade do ingrediente para converter volume em massa.');
            }

            $grams = $baseValue * $density;

            return match ($unit) {
                self::UNIT_G => $grams,
                self::UNIT_KG => $grams / 1000.0,
                default => $grams,
            };
        }

        throw new InvalidArgumentException('Unidade incompatível para ingredientes de volume.');
    }

    private static function unitFromBase(float $baseValue, string $unit): float
    {
        if ($unit !== self::UNIT_UNIT) {
            throw new InvalidArgumentException('Ingredientes por unidade aceitam apenas unidade (un).');
        }

        return $baseValue;
    }

    private static function volumeToMl(float $value, string $unit): float
    {
        return match ($unit) {
            self::UNIT_ML => $value,
            self::UNIT_L => $value * 1000.0,
            self::UNIT_TSP => $value * self::ML_PER_TSP,
            self::UNIT_TBSP => $value * self::ML_PER_TBSP,
            self::UNIT_CUP => $value * self::ML_PER_CUP,
            default => throw new InvalidArgumentException('Unidade de volume inválida.'),
        };
    }

    private static function mlToUnit(float $valueMl, string $unit): float
    {
        return match ($unit) {
            self::UNIT_ML => $valueMl,
            self::UNIT_L => $valueMl / 1000.0,
            self::UNIT_TSP => $valueMl / self::ML_PER_TSP,
            self::UNIT_TBSP => $valueMl / self::ML_PER_TBSP,
            self::UNIT_CUP => $valueMl / self::ML_PER_CUP,
            default => throw new InvalidArgumentException('Unidade de volume inválida.'),
        };
    }
}
