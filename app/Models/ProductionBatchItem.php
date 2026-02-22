<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionBatchItem extends Model
{
    protected $fillable = [
        'production_batch_id',
        'ingredient_id',
        'ingredient_name',
        'quantity_used_g',
        'unit_cost',
        'line_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity_used_g' => 'float',
            'unit_cost' => 'float',
            'line_cost' => 'float',
        ];
    }

    public function productionBatch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
