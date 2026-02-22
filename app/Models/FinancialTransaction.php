<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
        'reason',
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

    protected static function booted(): void
    {
        static::creating(function (self $transaction): void {
            if (filled($transaction->reference)) {
                return;
            }

            $seed = trim((string) ($transaction->reason ?: $transaction->counterparty ?: $transaction->source ?: ''));
            $transaction->reference = self::generateReference((int) ($transaction->user_id ?? 0), $seed);
        });
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

    public static function generateReference(int $userId, string $seedText = ''): string
    {
        $prefix = self::buildReferencePrefix($seedText);

        do {
            $reference = $prefix.self::randomUpperAlphaNumeric(8);

            $query = self::query()->where('reference', $reference);
            if ($userId > 0) {
                $query->where('user_id', $userId);
            }
        } while ($query->exists());

        return $reference;
    }

    private static function buildReferencePrefix(string $seedText): string
    {
        $lettersOnly = strtoupper((string) Str::of(Str::ascii($seedText))
            ->replaceMatches('/[^A-Za-z]/', ''));

        if ($lettersOnly === '') {
            return 'FIN';
        }

        return str_pad(substr($lettersOnly, 0, 3), 3, 'X');
    }

    private static function randomUpperAlphaNumeric(int $length): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxIndex = strlen($alphabet) - 1;
        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $alphabet[random_int(0, $maxIndex)];
        }

        return $token;
    }
}
