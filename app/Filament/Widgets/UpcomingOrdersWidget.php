<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UpcomingOrdersWidget extends BaseWidget
{
    protected static ?int $sort = -88;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->can('manage sales') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Próximas entregas')
            ->query($this->query())
            ->defaultSort('delivery_date')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Pedido')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Entrega')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_READY => 'info',
                        Order::STATUS_PREPARING => 'warning',
                        Order::STATUS_PENDING => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Valor')
                    ->formatStateUsing(fn (float $state): string => number_format($state, 2, ',', '.').' MT')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('open_order')
                    ->label('Abrir')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Nenhuma entrega próxima encontrada.');
    }

    private function query(): Builder
    {
        $query = Order::query()
            ->with('client')
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PREPARING, Order::STATUS_READY])
            ->whereNotNull('delivery_date')
            ->whereBetween('delivery_date', [now()->startOfDay(), now()->addDays(14)->endOfDay()]);

        $user = Auth::user();
        if ($user && ! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }
}
