<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\FinancialControl;
use App\Models\FinancialTransaction;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FinancialStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = -60;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->can('manage finances') ?? false;
    }

    protected function getStats(): array
    {
        $since30Days = now()->subDays(30)->startOfDay();

        $transactions = $this->transactionsQuery()
            ->whereDate('transaction_date', '>=', $since30Days->toDateString());

        $completed = (clone $transactions)->where('status', FinancialTransaction::STATUS_COMPLETED);

        $income = (float) (clone $completed)
            ->where('type', FinancialTransaction::TYPE_INCOME)
            ->sum('amount');

        $expense = (float) (clone $completed)
            ->where('type', FinancialTransaction::TYPE_EXPENSE)
            ->sum('amount');

        $pending = (float) (clone $transactions)
            ->where('status', FinancialTransaction::STATUS_PENDING)
            ->sum('amount');

        $net = $income - $expense;

        $sales = $this->salesQuery()
            ->whereDate('sold_at', '>=', $since30Days->toDateString())
            ->where('status', Sale::STATUS_COMPLETED);

        $salesCount = (int) (clone $sales)->count();
        $salesTotal = (float) (clone $sales)->sum('total_amount');
        $avgTicket = $salesCount > 0 ? $salesTotal / $salesCount : 0.0;

        return [
            Stat::make('Receitas (30 dias)', $this->currency($income))
                ->description('Entradas concluídas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->url(FinancialControl::getUrl()),
            Stat::make('Despesas (30 dias)', $this->currency($expense))
                ->description('Saídas concluídas')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->url(FinancialControl::getUrl()),
            Stat::make('Resultado líquido (30 dias)', $this->currency($net))
                ->description('Receitas menos despesas')
                ->descriptionIcon($net >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($net >= 0 ? 'primary' : 'danger')
                ->url(FinancialControl::getUrl()),
            Stat::make('Ticket médio (30 dias)', $this->currency($avgTicket))
                ->description('Média por venda concluída')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info')
                ->url(FinancialControl::getUrl()),
            Stat::make('Vendas concluídas (30 dias)', number_format($salesCount, 0, ',', '.'))
                ->description('Total de vendas no período')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary')
                ->url(FinancialControl::getUrl()),
            Stat::make('Pendentes (30 dias)', $this->currency($pending))
                ->description('Movimentos ainda em aberto')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(FinancialControl::getUrl()),
        ];
    }

    private function transactionsQuery(): Builder
    {
        $query = FinancialTransaction::query();

        $user = Auth::user();
        if ($user && ! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function salesQuery(): Builder
    {
        $query = Sale::query();

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
}
