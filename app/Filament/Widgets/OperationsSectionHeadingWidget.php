<?php

namespace App\Filament\Widgets;

use App\Models\Feature;
use Illuminate\Support\Facades\Auth;

class OperationsSectionHeadingWidget extends DashboardSectionHeadingWidget
{
    protected static ?int $sort = -30;

    protected static string $title = 'Produção e Stock';

    protected static ?string $description = 'Produção, inventário e alertas';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->can('manage inventory') && $user->hasAnyFeature([
            Feature::INGREDIENTS,
            Feature::RECIPES,
            Feature::INVENTORY,
            Feature::PRODUCTION_BATCHES,
        ]);
    }
}
