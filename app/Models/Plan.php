<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    public const CODE_BASIC = 'basic';

    protected $fillable = [
        'code',
        'name',
        'description',
        'price',
        'currency',
        'duration_months',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'duration_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'feature_plan')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
