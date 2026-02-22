<?php

namespace App\Filament\DashboardWidgets;

use Filament\Widgets\AccountWidget;

class PrimaryAccountWidget extends AccountWidget
{
    protected static ?int $sort = -100;

    protected int|string|array $columnSpan = 'full';
}
