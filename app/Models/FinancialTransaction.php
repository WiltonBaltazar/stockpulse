<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    public const TYPE_INCOME = 'income';

    public const TYPE_EXPENSE = 'expense';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CANCELLED = 'cancelled';

    public const SOURCE_SALES = 'sales';

    public const SOURCE_SUBSCRIPTION = 'subscription';

    public const SOURCE_CREDITS = 'credits';

    public const SOURCE_PURCHASE = 'purchase';

    public const SOURCE_LOSS = 'loss';

    public const SOURCE_COGS = 'cogs';

    public const SOURCE_OTHER = 'other';

    protected $fillable = [
        'user_id',
        'transaction_date',
        'type',
        'status',
        'source',
        'package_name',
        'counterparty',
        'reference',
        'amount',
        'credits',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'float',
            'credits' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_INCOME => 'Receita',
            self::TYPE_EXPENSE => 'Despesa',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_COMPLETED => 'Concluída',
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_CANCELLED => 'Cancelada',
        ];
    }

    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_SALES => 'Vendas',
            self::SOURCE_SUBSCRIPTION => 'Subscrições',
            self::SOURCE_CREDITS => 'Créditos',
            self::SOURCE_PURCHASE => 'Compras de insumos',
            self::SOURCE_LOSS => 'Perdas e quebras',
            self::SOURCE_COGS => 'CPV realizado',
            self::SOURCE_OTHER => 'Outras entradas/saídas',
        ];
    }
}
