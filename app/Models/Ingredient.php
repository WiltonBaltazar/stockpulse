<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'package_quantity_g',
        'package_cost',
        'stock_quantity_g',
        'reorder_level_g',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'package_quantity_g' => 'float',
            'package_cost' => 'float',
            'stock_quantity_g' => 'float',
            'reorder_level_g' => 'float',
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

    public function getInventoryValueAttribute(): float
    {
        return max((float) $this->stock_quantity_g, 0.0) * $this->cost_per_gram;
    }

    public function getIsLowStockAttribute(): bool
    {
        return (float) $this->reorder_level_g > 0
            && (float) $this->stock_quantity_g <= (float) $this->reorder_level_g;
    }
}
