<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionBatch extends Model
{
    protected $fillable = [
        'recipe_id',
        'user_id',
        'produced_at',
        'produced_units',
        'sold_units',
        'ingredients_cost',
        'packaging_cost',
        'overhead_cost',
        'total_cogs',
        'cogs_per_unit',
        'suggested_unit_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'produced_at' => 'date',
            'produced_units' => 'float',
            'sold_units' => 'integer',
            'ingredients_cost' => 'float',
            'packaging_cost' => 'float',
            'overhead_cost' => 'float',
            'total_cogs' => 'float',
            'cogs_per_unit' => 'float',
            'suggested_unit_price' => 'float',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductionBatchItem::class);
    }

    public function saleAllocations(): HasMany
    {
        return $this->hasMany(SaleBatchAllocation::class);
    }

    public function getMarginPerUnitAttribute(): ?float
    {
        if ($this->suggested_unit_price === null) {
            return null;
        }

        return (float) $this->suggested_unit_price - (float) $this->cogs_per_unit;
    }

    public function getAvailableUnitsAttribute(): int
    {
        $produced = max((int) round((float) $this->produced_units), 0);
        $sold = max((int) ($this->sold_units ?? 0), 0);

        return max($produced - $sold, 0);
    }
}
