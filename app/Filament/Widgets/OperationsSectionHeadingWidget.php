<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;

class OperationsSectionHeadingWidget extends DashboardSectionHeadingWidget
{
    protected static ?int $sort = -30;

    protected static string $title = 'Produção e Stock';

    protected static ?string $description = 'Produção, inventário e alertas';

    public static function canView(): bool
    {
        return Auth::user()?->can('manage inventory') ?? false;
    }
}
