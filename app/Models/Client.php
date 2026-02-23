<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'contact_number',
        'email',
        'document_number',
        'address',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public static function normalizeMozambicanContact(?string $value): ?string
    {
        return User::normalizeMozambicanContact($value);
    }

    public static function isValidMozambicanContact(?string $value): bool
    {
        return self::normalizeMozambicanContact($value) !== null;
    }

    protected function setContactNumberAttribute(?string $value): void
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            $this->attributes['contact_number'] = null;

            return;
        }

        $normalized = self::normalizeMozambicanContact($trimmed);
        $this->attributes['contact_number'] = $normalized ?? $trimmed;
    }
}
