<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeItem extends Model
{
    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'quantity_used_g',
    ];

    protected function casts(): array
    {
        return [
            'quantity_used_g' => 'float',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function getLineCostAttribute(): float
    {
        if (! $this->relationLoaded('ingredient') || ! $this->ingredient) {
            $this->loadMissing('ingredient');
        }

        if (! $this->ingredient) {
            return 0.0;
        }

        return (float) $this->quantity_used_g * $this->ingredient->cost_per_gram;
    }
}
