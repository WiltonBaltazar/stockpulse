<?php

namespace App\Filament\Widgets;

use App\Models\Feature;
use Illuminate\Support\Facades\Auth;

class CommercialSectionHeadingWidget extends DashboardSectionHeadingWidget
{
    protected static ?int $sort = -90;

    protected static string $title = 'Comercial e Clientes';

    protected static ?string $description = 'Clientes, orçamentos e pedidos';

    public static function canView(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (! ($user->can('manage sales') || $user->can('manage clients'))) {
            return false;
        }

        return $user->hasAnyFeature([
            Feature::CLIENTS,
            Feature::QUOTES,
            Feature::ORDERS,
            Feature::SALES,
        ]);
    }
}
