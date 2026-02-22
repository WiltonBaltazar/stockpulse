<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleBatchAllocation extends Model
{
    protected $fillable = [
        'sale_id',
        'production_batch_id',
        'quantity_units',
        'unit_cogs',
        'total_cogs',
    ];

    protected function casts(): array
    {
        return [
            'quantity_units' => 'integer',
            'unit_cogs' => 'float',
            'total_cogs' => 'float',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function productionBatch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class);
    }
}
