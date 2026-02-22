<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    public const TYPE_PURCHASE = 'purchase';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_MANUAL_OUT = 'manual_out';

    public const TYPE_PRODUCTION = 'production';

    protected $fillable = [
        'ingredient_id',
        'user_id',
        'type',
        'quantity_g',
        'unit_cost',
        'total_cost',
        'moved_at',
        'notes',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity_g' => 'float',
            'unit_cost' => 'float',
            'total_cost' => 'float',
            'moved_at' => 'datetime',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
