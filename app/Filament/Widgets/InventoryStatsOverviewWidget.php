<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\IngredientResource;
use App\Filament\Resources\InventoryMovementResource;
use App\Models\Feature;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InventoryStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = -20;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        return ($user?->can('manage inventory') ?? false)
            && ($user?->hasFeature(Feature::INGREDIENTS) ?? false)
            && ($user?->hasFeature(Feature::INVENTORY) ?? false);
    }

    protected function getStats(): array
    {
        $now = now();
        $since30Days = $now->copy()->subDays(30)->startOfDay();

        $ingredientsQuery = $this->ingredientsQuery();

        $inventoryValue = (float) (clone $ingredientsQuery)
            ->selectRaw('SUM((CASE WHEN stock_quantity_g > 0 THEN stock_quantity_g ELSE 0 END) * (CASE WHEN package_quantity_g > 0 THEN package_cost / package_quantity_g ELSE 0 END)) as inventory_value')
            ->value('inventory_value');

        $lowStockCount = (int) (clone $ingredientsQuery)
            ->where('is_active', true)
            ->where('reorder_level_g', '>', 0)
            ->whereColumn('stock_quantity_g', '<=', 'reorder_level_g')
            ->count();

        $movementsQuery = $this->movementsQuery();

        $entries30Days = (float) (clone $movementsQuery)
            ->where('moved_at', '>=', $since30Days)
            ->where('quantity_g', '>', 0)
            ->sum('quantity_g');

        $manualOut30Days = (float) (clone $movementsQuery)
            ->where('moved_at', '>=', $since30Days)
            ->where('type', InventoryMovement::TYPE_MANUAL_OUT)
            ->sum('quantity_g');

        return [
            Stat::make('Valor total em estoque', $this->currency($inventoryValue))
                ->description('Custo atual dos ingredientes em estoque')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info')
                ->url(IngredientResource::getUrl('index')),
            Stat::make('Ingredientes em baixo estoque', (string) $lowStockCount)
                ->description($lowStockCount > 0 ? 'Repor itens críticos' : 'Nenhum alerta no momento')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockCount > 0 ? 'danger' : 'success')
                ->url(IngredientResource::getUrl('index')),
            Stat::make('Entradas (30 dias)', $this->quantity($entries30Days).' unid. base')
                ->description('Compras e ajustes positivos')
                ->descriptionIcon('heroicon-m-arrow-down-circle')
                ->color('success')
                ->url(InventoryMovementResource::getUrl('index')),
            Stat::make('Saídas manuais (30 dias)', $this->quantity(abs($manualOut30Days)).' unid. base')
                ->description('Perdas, descartes e uso fora de receita')
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->color(abs($manualOut30Days) > 0 ? 'warning' : 'gray')
                ->url(InventoryMovementResource::getUrl('index')),
        ];
    }

    private function ingredientsQuery(): Builder
    {
        $query = Ingredient::query();

        $user = Auth::user();
        if ($user && ! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function movementsQuery(): Builder
    {
        $query = InventoryMovement::query();

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
