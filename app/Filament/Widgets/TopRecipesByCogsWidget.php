<?php

namespace App\Filament\Widgets;

use App\Models\Recipe;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TopRecipesByCogsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->can('manage inventory') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top receitas por custo/unidade')
            ->query($this->getTopRecipesQuery())
            ->defaultSort('avg_cogs_per_unit', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Receita')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('production_batches_count')
                    ->label('Lotes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('produced_units_sum')
                    ->label('Unid. produzidas')
                    ->formatStateUsing(fn (?float $state): string => number_format((float) round((float) $state), 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('avg_cogs_per_unit')
                    ->label('Custo médio/unid.')
                    ->formatStateUsing(fn (?float $state): string => self::currency((float) $state))
                    ->sortable()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('last_produced_at')
                    ->label('Última produção')
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('open_recipe')
                    ->label('Abrir')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Recipe $record): string => route('filament.admin.resources.recipes.edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Ainda não há lotes de produção registrados.');
    }

    private function getTopRecipesQuery(): Builder
    {
        $query = Recipe::query()
            ->whereHas('productionBatches')
            ->withCount('productionBatches')
            ->withSum('productionBatches as produced_units_sum', 'produced_units')
            ->withAvg('productionBatches as avg_cogs_per_unit', 'cogs_per_unit')
            ->withMax('productionBatches as last_produced_at', 'produced_at');

        $user = Auth::user();

        if ($user && ! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private static function currency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }
}
