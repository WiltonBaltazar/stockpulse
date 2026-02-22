<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductionBatchResource;
use App\Models\ProductionBatch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductionStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = -19;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->can('manage inventory') ?? false;
    }

    protected function getStats(): array
    {
        $since7Days = now()->subDays(7)->startOfDay();
        $since30Days = now()->subDays(30)->startOfDay();

        $baseQuery = $this->productionBatchesQuery();

        $batches30Days = (int) (clone $baseQuery)
            ->where('produced_at', '>=', $since30Days->toDateString())
            ->count();

        $units30Days = (float) (clone $baseQuery)
            ->where('produced_at', '>=', $since30Days->toDateString())
            ->sum('produced_units');

        $cpvAvg30Days = (float) (clone $baseQuery)
            ->where('produced_at', '>=', $since30Days->toDateString())
            ->avg('cogs_per_unit');

        $marginAvg30Days = (float) (clone $baseQuery)
            ->where('produced_at', '>=', $since30Days->toDateString())
            ->selectRaw('AVG(COALESCE(suggested_unit_price, 0) - cogs_per_unit) as margin_avg')
            ->value('margin_avg');

        $units7Days = (float) (clone $baseQuery)
            ->where('produced_at', '>=', $since7Days->toDateString())
            ->sum('produced_units');

        return [
            Stat::make('Lotes produzidos (30 dias)', (string) $batches30Days)
                ->description('Registros de produção recentes')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info')
                ->url(ProductionBatchResource::getUrl('index')),
            Stat::make('Unidades produzidas (30 dias)', $this->quantity($units30Days))
                ->description('Últimos 30 dias')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success')
                ->url(ProductionBatchResource::getUrl('index')),
            Stat::make('CPV médio / unidade (30 dias)', $this->currency($cpvAvg30Days))
                ->description('Média do custo de produção por unidade')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning')
                ->url(ProductionBatchResource::getUrl('index')),
            Stat::make('Margem média / unidade (30 dias)', $this->currency($marginAvg30Days))
                ->description('Produção nos últimos 7 dias: '.$this->quantity($units7Days).' unid.')
                ->descriptionIcon($marginAvg30Days >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($marginAvg30Days >= 0 ? 'success' : 'danger')
                ->url(ProductionBatchResource::getUrl('index')),
        ];
    }

    private function productionBatchesQuery(): Builder
    {
        $query = ProductionBatch::query();

        $user = Auth::user();
        if ($user && ! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function currency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }

    private function quantity(float $value): string
    {
        return number_format((float) round($value), 0, ',', '.');
    }
}
