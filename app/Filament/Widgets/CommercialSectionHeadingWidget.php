<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;

class CommercialSectionHeadingWidget extends DashboardSectionHeadingWidget
{
    protected static ?int $sort = -90;

    protected static string $title = 'Comercial e Clientes';

    protected static ?string $description = 'Clientes, orçamentos e pedidos';

    public static function canView(): bool
    {
        $user = Auth::user();

        return ($user?->can('manage sales') ?? false) || ($user?->can('manage clients') ?? false);
    }
}
