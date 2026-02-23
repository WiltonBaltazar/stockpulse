<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_DELIVERY = 'delivery';

    public const TYPE_PICKUP = 'pickup';

    protected $fillable = [
        'user_id',
        'client_id',
        'reference',
        'status',
        'type',
        'quote_date',
        'delivery_date',
        'delivery_time',
        'additional_fee',
        'discount',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quote_date' => 'date',
            'delivery_date' => 'date',
            'additional_fee' => 'float',
            'discount' => 'float',
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

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Rascunho',
            self::STATUS_SENT => 'Enviado',
            self::STATUS_APPROVED => 'Aprovado',
            self::STATUS_REJECTED => 'Rejeitado',
            self::STATUS_CONVERTED => 'Convertido em pedido',
            self::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_DELIVERY => 'Entrega',
            self::TYPE_PICKUP => 'Levantamento',
        ];
    }
}
