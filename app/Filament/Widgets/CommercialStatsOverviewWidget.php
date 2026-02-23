<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ClientResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\QuoteResource;
use App\Models\Client;
use App\Models\Order;
use App\Models\Quote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CommercialStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = -89;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        return ($user?->can('manage sales') ?? false) || ($user?->can('manage clients') ?? false);
    }

    protected function getStats(): array
    {
        $since30Days = now()->subDays(30)->startOfDay();
        $now = now();
        $next7Days = now()->addDays(7)->endOfDay();

        $clientsQuery = $this->clientsQuery();
        $quotesQuery = $this->quotesQuery();
        $ordersQuery = $this->ordersQuery();

        $activeClients = (int) (clone $clientsQuery)
            ->where('is_active', true)
            ->count();

        $openQuotes = (int) (clone $quotesQuery)
            ->whereIn('status', [Quote::STATUS_DRAFT, Quote::STATUS_SENT])
            ->count();

        $quotesLast30 = (int) (clone $quotesQuery)
            ->whereDate('quote_date', '>=', $since30Days->toDateString())
            ->count();

        $convertedQuotesLast30 = (int) (clone $quotesQuery)
            ->whereDate('quote_date', '>=', $since30Days->toDateString())
            ->where('status', Quote::STATUS_CONVERTED)
            ->count();

        $conversionRate = $quotesLast30 > 0
            ? ($convertedQuotesLast30 / $quotesLast30) * 100
            : 0.0;

        $ordersInProgress = (int) (clone $ordersQuery)
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PREPARING, Order::STATUS_READY])
            ->count();

        $upcomingDeliveries = (int) (clone $ordersQuery)
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PREPARING, Order::STATUS_READY])
            ->whereNotNull('delivery_date')
            ->whereBetween('delivery_date', [$now, $next7Days])
            ->count();

        $ordersRevenue30 = (float) (clone $ordersQuery)
            ->whereDate('order_date', '>=', $since30Days->toDateString())
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->sum('total_amount');

        return [
            Stat::make('Clientes ativos', number_format($activeClients, 0, ',', '.'))
                ->description('Base de clientes com cadastro ativo')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(ClientResource::getUrl('index')),
            Stat::make('Orçamentos em aberto', number_format($openQuotes, 0, ',', '.'))
                ->description('Rascunhos e enviados aguardando decisão')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($openQuotes > 0 ? 'warning' : 'gray')
                ->url(QuoteResource::getUrl('index')),
            Stat::make('Conversão de orçamentos (30 dias)', $this->percent($conversionRate))
                ->description(number_format($convertedQuotesLast30, 0, ',', '.').' de '.number_format($quotesLast30, 0, ',', '.').' convertidos')
                ->descriptionIcon('heroicon-m-arrow-path-rounded-square')
                ->color($conversionRate >= 40 ? 'success' : 'warning')
                ->url(QuoteResource::getUrl('index')),
            Stat::make('Pedidos em andamento', number_format($ordersInProgress, 0, ',', '.'))
                ->description('Pendentes, em preparação e prontos')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color($ordersInProgress > 0 ? 'info' : 'gray')
                ->url(OrderResource::getUrl('index')),
            Stat::make('Entregas nos próximos 7 dias', number_format($upcomingDeliveries, 0, ',', '.'))
                ->description('Pedidos com entrega agendada')
                ->descriptionIcon('heroicon-m-truck')
                ->color($upcomingDeliveries > 0 ? 'primary' : 'gray')
                ->url(OrderResource::getUrl('index')),
            Stat::make('Volume de pedidos (30 dias)', $this->currency($ordersRevenue30))
                ->description('Valor total dos pedidos não cancelados')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->url(OrderResource::getUrl('index')),
        ];
    }

    private function clientsQuery(): Builder
    {
        $query = Client::query();

        $user = Auth::user();
        if ($user && ! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function quotesQuery(): Builder
    {
        $query = Quote::query();

        $user = Auth::user();
        if ($user && ! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function ordersQuery(): Builder
    {
        $query = Order::query();

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

    private function percent(float $value): string
    {
        return number_format($value, 1, ',', '.').'%';
    }
}
