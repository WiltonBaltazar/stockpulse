<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;

class FinancialSectionHeadingWidget extends DashboardSectionHeadingWidget
{
    protected static ?int $sort = -70;

    protected static string $title = 'Finanças';

    protected static ?string $description = 'Resultados e indicadores financeiros';

    public static function canView(): bool
    {
        return Auth::user()?->can('manage finances') ?? false;
    }
}
