<?php

namespace Tests\Unit;

use App\Support\MeasurementUnitConverter;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MeasurementUnitConverterTest extends TestCase
{
    #[Test]
    public function it_converts_mass_units(): void
    {
        $this->assertSame(1000.0, MeasurementUnitConverter::toBase(1.0, 'kg', 'mass', 1.0));
        $this->assertSame(1.0, MeasurementUnitConverter::fromBase(1000.0, 'kg', 'mass', 1.0));
    }

    #[Test]
    public function it_converts_volume_units(): void
    {
        $this->assertSame(1000.0, MeasurementUnitConverter::toBase(1.0, 'l', 'volume', 1.0));
        $this->assertSame(1.0, MeasurementUnitConverter::fromBase(1000.0, 'l', 'volume', 1.0));
        $this->assertSame(15.0, MeasurementUnitConverter::toBase(1.0, 'tbsp', 'volume', 1.0));
    }

    #[Test]
    public function it_converts_between_volume_and_mass_with_density(): void
    {
        $this->assertSame(460.0, MeasurementUnitConverter::toBase(500.0, 'ml', 'mass', 0.92));
        $this->assertEqualsWithDelta(500.0, MeasurementUnitConverter::fromBase(460.0, 'ml', 'mass', 0.92), 0.0001);
    }

    #[Test]
    public function it_converts_unit_type_as_count(): void
    {
        $this->assertSame(12.0, MeasurementUnitConverter::toBase(12.0, 'un', 'unit', null));
    }

    #[Test]
    public function it_requires_density_for_cross_conversion(): void
    {
        $this->expectException(InvalidArgumentException::class);

        MeasurementUnitConverter::toBase(100.0, 'ml', 'mass', null);
    }
}
