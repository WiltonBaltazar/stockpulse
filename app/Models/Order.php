<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_READY = 'ready';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_OPEN = 'open';

    public const PAYMENT_PARTIAL = 'partial';

    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'user_id',
        'client_id',
        'quote_id',
        'reference',
        'status',
        'payment_status',
        'order_date',
        'delivery_date',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'delivery_date' => 'datetime',
            'total_amount' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_PREPARING => 'Em preparação',
            self::STATUS_READY => 'Pronto',
            self::STATUS_DELIVERED => 'Entregue',
            self::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    public static function paymentStatusOptions(): array
    {
        return [
            self::PAYMENT_OPEN => 'Aberto',
            self::PAYMENT_PARTIAL => 'Parcial',
            self::PAYMENT_PAID => 'Pago',
        ];
    }
}
