<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class LowStockIngredientsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->can('manage inventory') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Alertas de estoque baixo')
            ->query($this->getLowStockQuery())
            ->defaultSort('stock_quantity_g')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Ingrediente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity_g')
                    ->label('Estoque (g)')
                    ->formatStateUsing(fn (float $state): HtmlString => new HtmlString(
                        '<span style="color:#991b1b;font-weight:600;">'.e(self::formatQuantity($state)).'</span>'
                    ))
                    ->sortable()
                    ->html(),
                Tables\Columns\TextColumn::make('reorder_level_g')
                    ->label('Reposição (g)')
                    ->formatStateUsing(fn (float $state): string => self::formatQuantity($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('shortage_g')
                    ->label('Falta para repor (g)')
                    ->getStateUsing(fn (Ingredient $record): float => max((float) $record->reorder_level_g - (float) $record->stock_quantity_g, 0.0))
                    ->formatStateUsing(fn (float $state): HtmlString => new HtmlString(
                        '<span style="color:#991b1b;font-weight:600;">'.e(self::formatQuantity($state)).'</span>'
                    ))
                    ->html(),
            ])
            ->actions([
                Tables\Actions\Action::make('open_ingredient')
                    ->label('Abrir')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Ingredient $record): string => route('filament.admin.resources.ingredients.edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Nenhum ingrediente com estoque baixo.');
    }

    private function getLowStockQuery(): Builder
    {
        $query = Ingredient::query()
            ->where('is_active', true)
            ->where('reorder_level_g', '>', 0)
            ->whereColumn('stock_quantity_g', '<=', 'reorder_level_g');

        $user = Auth::user();

        if ($user && ! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private static function formatQuantity(float $value): string
    {
        return number_format((float) round($value), 0, ',', '.');
    }
}
