<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CANCELLED = 'cancelled';

    public const CHANNEL_OFFLINE = 'offline';

    public const CHANNEL_ONLINE = 'online';

    public const PAYMENT_CASH = 'cash';

    public const PAYMENT_TRANSFER = 'transfer';

    public const PAYMENT_MOBILE = 'mobile_money';

    public const PAYMENT_OTHER = 'other';

    protected $fillable = [
        'user_id',
        'recipe_id',
        'item_name',
        'customer_name',
        'reference',
        'status',
        'channel',
        'payment_method',
        'sold_at',
        'quantity',
        'unit_price',
        'total_amount',
        'estimated_unit_cost',
        'estimated_total_cost',
        'estimated_profit',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sold_at' => 'datetime',
            'quantity' => 'float',
            'unit_price' => 'float',
            'total_amount' => 'float',
            'estimated_unit_cost' => 'float',
            'estimated_total_cost' => 'float',
            'estimated_profit' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function batchAllocations(): HasMany
    {
        return $this->hasMany(SaleBatchAllocation::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_COMPLETED => 'Concluída',
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_CANCELLED => 'Cancelada',
        ];
    }

    public static function channelOptions(): array
    {
        return [
            self::CHANNEL_OFFLINE => 'Offline',
            self::CHANNEL_ONLINE => 'Online',
        ];
    }

    public static function paymentOptions(): array
    {
        return [
            self::PAYMENT_CASH => 'Dinheiro',
            self::PAYMENT_TRANSFER => 'Transferência',
            self::PAYMENT_MOBILE => 'M-Pesa / E-Mola',
            self::PAYMENT_OTHER => 'Outro',
        ];
    }

    public function getResolvedItemNameAttribute(): string
    {
        if ($this->recipe) {
            return $this->recipe->name;
        }

        return $this->item_name ?: 'Venda avulsa';
    }
}
