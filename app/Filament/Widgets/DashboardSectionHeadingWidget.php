<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

abstract class DashboardSectionHeadingWidget extends Widget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.dashboard-section-heading';

    protected static string $title = '';

    protected static ?string $description = null;

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'title' => static::$title,
            'description' => static::$description,
        ];
    }
}
