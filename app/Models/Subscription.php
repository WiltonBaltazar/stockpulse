<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'started_at',
        'ends_at',
        'price',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'price' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Ativa',
            self::STATUS_CANCELLED => 'Cancelada',
            self::STATUS_EXPIRED => 'Expirada',
        ];
    }

    public function getResolvedStatusAttribute(): string
    {
        if ($this->status === self::STATUS_ACTIVE && $this->ends_at && $this->ends_at->isPast()) {
            return self::STATUS_EXPIRED;
        }

        return $this->status;
    }
}
