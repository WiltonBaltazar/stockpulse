<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'is_active',
        'priced_at',
        'yield_units',
        'packaging_cost_per_unit',
        'overhead_percent',
        'markup_multiplier',
    ];

    protected function casts(): array
    {
        return [
            'priced_at' => 'date',
            'is_active' => 'boolean',
            'yield_units' => 'float',
            'packaging_cost_per_unit' => 'float',
            'overhead_percent' => 'float',
            'markup_multiplier' => 'float',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productionBatches(): HasMany
    {
        return $this->hasMany(ProductionBatch::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getIngredientsCostAttribute(): float
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->with('ingredient')->get();

        return (float) $items->sum(fn (RecipeItem $item) => $item->line_cost);
    }

    public function getCostWithOverheadAttribute(): float
    {
        $factor = 1 + ((float) $this->overhead_percent / 100);

        return $this->ingredients_cost * $factor;
    }

    public function getTargetRevenueAttribute(): float
    {
        return $this->cost_with_overhead * (float) $this->markup_multiplier;
    }

    public function getUnitBasePriceAttribute(): float
    {
        $yield = max((float) $this->yield_units, 0.000001);

        return $this->target_revenue / $yield;
    }

    public function getFinalUnitPriceAttribute(): float
    {
        return $this->unit_base_price + (float) $this->packaging_cost_per_unit;
    }
}
