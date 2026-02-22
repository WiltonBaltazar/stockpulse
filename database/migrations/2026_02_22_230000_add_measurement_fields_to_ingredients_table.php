<?php

use App\Models\Ingredient;
use App\Support\MeasurementUnitConverter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('measurement_type', 20)
                ->default(Ingredient::MEASUREMENT_MASS)
                ->after('reorder_level_g');
            $table->string('preferred_unit', 20)
                ->default(MeasurementUnitConverter::UNIT_G)
                ->after('measurement_type');
            $table->decimal('density_g_per_ml', 8, 4)
                ->nullable()
                ->after('preferred_unit');
        });

        DB::table('ingredients')
            ->whereRaw('LOWER(name) like ?', ['%ovos%'])
            ->orWhereRaw('LOWER(name) like ?', ['%embalagem%'])
            ->update([
                'measurement_type' => Ingredient::MEASUREMENT_UNIT,
                'preferred_unit' => MeasurementUnitConverter::UNIT_UNIT,
                'density_g_per_ml' => null,
            ]);

        DB::table('ingredients')
            ->whereRaw('LOWER(name) like ?', ['%leite%'])
            ->orWhereRaw('LOWER(name) like ?', ['%baunilha%'])
            ->update([
                'measurement_type' => Ingredient::MEASUREMENT_VOLUME,
                'preferred_unit' => MeasurementUnitConverter::UNIT_ML,
                'density_g_per_ml' => 1.0,
            ]);

        DB::table('ingredients')
            ->whereRaw('LOWER(name) like ?', ['%óleo%'])
            ->orWhereRaw('LOWER(name) like ?', ['%oleo%'])
            ->update([
                'measurement_type' => Ingredient::MEASUREMENT_VOLUME,
                'preferred_unit' => MeasurementUnitConverter::UNIT_ML,
                'density_g_per_ml' => 0.92,
            ]);

        DB::table('ingredients')
            ->whereRaw('LOWER(name) like ?', ['%mel%'])
            ->update([
                'measurement_type' => Ingredient::MEASUREMENT_VOLUME,
                'preferred_unit' => MeasurementUnitConverter::UNIT_ML,
                'density_g_per_ml' => 1.42,
            ]);
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn(['measurement_type', 'preferred_unit', 'density_g_per_ml']);
        });
    }
};
