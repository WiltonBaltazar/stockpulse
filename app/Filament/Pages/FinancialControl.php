<?php

namespace App\Filament\Pages;

use App\Models\FinancialTransaction;
use App\Models\Sale;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FinancialControl extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationGroup = 'Vendas & Financeiro';

    protected static ?string $navigationLabel = 'Controlo financeiro';

    protected static ?string $title = 'Controlo Financeiro';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.financial-control';

    public int $quickRangeDays = 30;

    public ?string $startDate = null;

    public ?string $endDate = null;

    public string $status = 'all';

    public string $source = 'all';

    public string $search = '';

    public function mount(): void
    {
        $this->setQuickRange(30);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage finances') ?? false;
    }

    public function setQuickRange(int $days): void
    {
        $days = max($days, 1);
        $this->quickRangeDays = $days;
        $this->endDate = now()->toDateString();
        $this->startDate = now()->subDays($days - 1)->toDateString();
    }

    public function applyFilters(): void
    {
        if ($this->startDate || $this->endDate) {
            $this->quickRangeDays = 0;
        }
    }

    public function refreshData(): void
    {
        // Intentionally empty: this action triggers a Livewire re-render.
    }

    public function getStatusOptionsProperty(): array
    {
        return ['all' => 'Todos estados'] + FinancialTransaction::statusOptions();
    }

    public function getSourceOptionsProperty(): array
    {
        return ['all' => 'Todas fontes'] + FinancialTransaction::sourceOptions();
    }

    public function getStatsProperty(): array
    {
        $transactionsQuery = $this->filteredTransactionsQuery();

        $completedTransactions = (clone $transactionsQuery)
            ->where('status', FinancialTransaction::STATUS_COMPLETED);

        $incomeQuery = (clone $completedTransactions)
            ->where('type', FinancialTransaction::TYPE_INCOME);

        $expenseQuery = (clone $completedTransactions)
            ->where('type', FinancialTransaction::TYPE_EXPENSE);

        $grossRevenue = (float) (clone $incomeQuery)->sum('amount');
        $cogsExpense = (float) (clone $expenseQuery)
            ->where('source', FinancialTransaction::SOURCE_COGS)
            ->sum('amount');
        $cashExpenses = (float) (clone $expenseQuery)
            ->where('source', '!=', FinancialTransaction::SOURCE_COGS)
            ->sum('amount');
        $netMovement = $grossRevenue - $cashExpenses;

        $purchases = (float) (clone $expenseQuery)
            ->where('source', FinancialTransaction::SOURCE_PURCHASE)
            ->sum('amount');

        $losses = (float) (clone $expenseQuery)
            ->where('source', FinancialTransaction::SOURCE_LOSS)
            ->sum('amount');

        $allTransactions = (int) (clone $transactionsQuery)->count();
        $pendingAmount = (float) (clone $transactionsQuery)
            ->where('status', FinancialTransaction::STATUS_PENDING)
            ->sum('amount');

        $salesQuery = (clone $this->filteredSalesQuery())
            ->where('status', Sale::STATUS_COMPLETED);

        $salesRevenue = (float) (clone $salesQuery)->sum('total_amount');
        $salesCost = (float) (clone $salesQuery)->sum('estimated_total_cost');
        $salesProfit = (float) (clone $salesQuery)->sum('estimated_profit');
        $salesCount = (int) (clone $salesQuery)->count();
        $soldQuantity = (float) (clone $salesQuery)->sum('quantity');

        $avgSaleTicket = $salesCount > 0
            ? $salesRevenue / $salesCount
            : 0.0;

        return [
            [
                'label' => 'Vendas (receita)',
                'value' => $this->currency($salesRevenue),
                'description' => 'Total vendido em vendas concluídas',
                'tone' => 'success',
            ],
            [
                'label' => 'Custo do que foi vendido (vendas)',
                'value' => $this->currency($salesCost),
                'description' => 'Custo do que foi vendido por lote (FIFO)',
                'tone' => 'warning',
            ],
            [
                'label' => 'Ganho de vendas',
                'value' => $this->currency($salesProfit),
                'description' => 'Receita - custo do que foi vendido',
                'tone' => $salesProfit >= 0 ? 'primary' : 'danger',
            ],
            [
                'label' => 'Compras de insumos',
                'value' => $this->currency($purchases),
                'description' => 'Despesas com ingredientes e materiais',
                'tone' => 'danger',
            ],
            [
                'label' => 'Perdas e quebras',
                'value' => $this->currency($losses),
                'description' => 'Despesas de perdas operacionais',
                'tone' => 'danger',
            ],
            [
                'label' => 'Custo do que foi vendido (lançado)',
                'value' => $this->currency($cogsExpense),
                'description' => 'Despesa técnica gerada automaticamente nas vendas',
                'tone' => 'warning',
            ],
            [
                'label' => 'Movimento líquido',
                'value' => $this->currency($netMovement),
                'description' => 'Receitas concluídas - despesas de caixa (sem custo do que foi vendido)',
                'tone' => $netMovement >= 0 ? 'info' : 'danger',
            ],
            [
                'label' => 'Quantidade vendida',
                'value' => $this->quantity($soldQuantity),
                'description' => 'Itens/unidades vendidos',
                'tone' => 'gray',
            ],
            [
                'label' => 'Ticket médio da venda',
                'value' => $this->currency($avgSaleTicket),
                'description' => 'Média por venda concluída',
                'tone' => 'success',
            ],
            [
                'label' => 'Transações registadas',
                'value' => (string) $allTransactions,
                'description' => 'Movimentos financeiros no período',
                'tone' => 'info',
            ],
            [
                'label' => 'Pendente',
                'value' => $this->currency($pendingAmount),
                'description' => 'Movimentos ainda em aberto',
                'tone' => 'warning',
            ],
        ];
    }

    public function getRevenueOriginsProperty(): Collection
    {
        return (clone $this->filteredTransactionsQuery())
            ->where('status', FinancialTransaction::STATUS_COMPLETED)
            ->where('type', FinancialTransaction::TYPE_INCOME)
            ->selectRaw('source, COUNT(*) as transactions_count, SUM(amount) as amount_sum')
            ->groupBy('source')
            ->orderByDesc('amount_sum')
            ->get()
            ->map(function (FinancialTransaction $transaction): array {
                $source = (string) $transaction->source;

                return [
                    'source' => FinancialTransaction::sourceOptions()[$source] ?? ucfirst($source),
                    'transactions' => (int) $transaction->transactions_count,
                    'amount' => (float) $transaction->amount_sum,
                ];
            })
            ->values();
    }

    public function getSalesByChannelProperty(): Collection
    {
        return (clone $this->filteredSalesQuery())
            ->where('status', Sale::STATUS_COMPLETED)
            ->selectRaw('channel, COUNT(*) as sales_count, SUM(quantity) as quantity_sum, SUM(total_amount) as amount_sum, SUM(estimated_profit) as profit_sum')
            ->groupBy('channel')
            ->orderByDesc('amount_sum')
            ->get()
            ->map(function (Sale $sale): array {
                $channel = (string) $sale->channel;

                return [
                    'channel' => Sale::channelOptions()[$channel] ?? ucfirst($channel),
                    'sales' => (int) $sale->sales_count,
                    'quantity' => (float) $sale->quantity_sum,
                    'amount' => (float) $sale->amount_sum,
                    'profit' => (float) $sale->profit_sum,
                ];
            })
            ->values();
    }

    public function getTransactionsProperty(): Collection
    {
        return (clone $this->filteredTransactionsQuery())
            ->with('user')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->map(function (FinancialTransaction $transaction): array {
                return [
                    'date' => $transaction->transaction_date?->format('d/m/Y') ?? '-',
                    'user' => $transaction->counterparty ?: ($transaction->user?->name ?? '-'),
                    'source' => FinancialTransaction::sourceOptions()[$transaction->source] ?? $transaction->source,
                    'type' => FinancialTransaction::typeOptions()[$transaction->type] ?? $transaction->type,
                    'status' => FinancialTransaction::statusOptions()[$transaction->status] ?? $transaction->status,
                    'status_tone' => $this->statusTone($transaction->status),
                    'amount' => $this->currency((float) $transaction->amount),
                    'amount_tone' => $transaction->type === FinancialTransaction::TYPE_EXPENSE ? 'danger' : 'success',
                    'reason' => trim((string) ($transaction->reason ?? '')) !== '' ? (string) $transaction->reason : ((string) ($transaction->notes ?? '') ?: '-'),
                    'reference' => $transaction->reference ?: '-',
                ];
            });
    }

    private function filteredTransactionsQuery(): Builder
    {
        $query = FinancialTransaction::query();

        $user = Auth::user();
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($this->startDate) {
            $query->whereDate('transaction_date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('transaction_date', '<=', $this->endDate);
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->source !== 'all') {
            $query->where('source', $this->source);
        }

        if ($this->search !== '') {
            $needle = trim($this->search);

            if ($needle !== '') {
                $query->where(function (Builder $builder) use ($needle): void {
                    $builder
                        ->where('counterparty', 'like', "%{$needle}%")
                        ->orWhere('reference', 'like', "%{$needle}%")
                        ->orWhere('reason', 'like', "%{$needle}%")
                        ->orWhere('notes', 'like', "%{$needle}%")
                        ->orWhere('package_name', 'like', "%{$needle}%");
                });
            }
        }

        return $query;
    }

    private function filteredSalesQuery(): Builder
    {
        $query = Sale::query();

        $user = Auth::user();
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($this->startDate) {
            $query->whereDate('sold_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('sold_at', '<=', $this->endDate);
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->source !== 'all' && $this->source !== FinancialTransaction::SOURCE_SALES) {
            $query->whereRaw('1 = 0');
        }

        if ($this->search !== '') {
            $needle = trim($this->search);

            if ($needle !== '') {
                $query->where(function (Builder $builder) use ($needle): void {
                    $builder
                        ->where('item_name', 'like', "%{$needle}%")
                        ->orWhere('customer_name', 'like', "%{$needle}%")
                        ->orWhere('reference', 'like', "%{$needle}%")
                        ->orWhere('notes', 'like', "%{$needle}%");
                });
            }
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

    private function statusTone(string $status): string
    {
        return match ($status) {
            FinancialTransaction::STATUS_COMPLETED => 'success',
            FinancialTransaction::STATUS_PENDING => 'warning',
            FinancialTransaction::STATUS_CANCELLED => 'gray',
            default => 'gray',
        };
    }
}
