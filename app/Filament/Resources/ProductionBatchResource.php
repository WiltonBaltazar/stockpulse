<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionBatchResource\Pages;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use App\Services\ProductionBatchService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ProductionBatchResource extends Resource
{
    protected static ?string $model = ProductionBatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Produção & Stock';

    protected static ?string $modelLabel = 'lote de produção';

    protected static ?string $pluralModelLabel = 'lotes de produção';

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('manage inventory') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('manage inventory') ?? false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['recipe', 'items']);
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Produção')
                    ->schema([
                        Select::make('recipe_id')
                            ->label('Receita')
                            ->relationship('recipe', 'name', function (Builder $query): void {
                                $query->where('is_active', true);

                                $user = Auth::user();

                                if (! $user) {
                                    $query->whereRaw('1 = 0');

                                    return;
                                }

                                if (! $user->isAdmin()) {
                                    $query->where('user_id', $user->id);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        TextInput::make('produced_units')
                            ->label('Quantidade produzida (unidades)')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->dehydrateStateUsing(fn ($state): int => max((int) ($state ?? 0), 1))
                            ->minValue(1)
                            ->step(1)
                            ->default(1)
                            ->live(),
                        DatePicker::make('produced_at')
                            ->label('Data de produção')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Guia rápido de termos')
                    ->schema([
                        Placeholder::make('guide_cpv')
                            ->label('CPV (Custo dos Produtos Vendidos)')
                            ->content('É o custo total para produzir o lote: ingredientes + custos indiretos + embalagem.'),
                        Placeholder::make('guide_packaging')
                            ->label('Custo de embalagem')
                            ->content('Quanto custa embalar cada unidade produzida (caixa, saco, etiqueta, etc.).'),
                        Placeholder::make('guide_overhead')
                            ->label('Custos indiretos')
                            ->content('Percentual para cobrir energia, gás, água e outras despesas operacionais.'),
                        Placeholder::make('guide_margin')
                            ->label('Margem por unidade')
                            ->content('Diferença entre o preço sugerido e o CPV por unidade.'),
                    ])
                    ->columns(2),
                Section::make('Pré-visualização de CPV')
                    ->schema([
                        Placeholder::make('stock_status_indicator')
                            ->label('Estado do estoque')
                            ->content(function (Get $get): HtmlString {
                                $preview = self::previewFromState($get);
                                $hasRecipe = (bool) $get('recipe_id');
                                $hasUnits = (float) ($get('produced_units') ?? 0) > 0;

                                if (! $hasRecipe || ! $hasUnits) {
                                    return new HtmlString(
                                        '<div style="border:1px solid #d1d5db;background:#f9fafb;color:#374151;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;">Selecione a receita e a quantidade para validar o estoque.</div>'
                                    );
                                }

                                $shortages = $preview['shortages'] ?? [];

                                if ($shortages === []) {
                                    return new HtmlString(
                                        '<div style="border:1px solid #10b981;background:#ecfdf5;color:#065f46;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;font-weight:600;">Estoque suficiente para esta produção.</div>'
                                    );
                                }

                                return new HtmlString(
                                    '<div style="border:1px solid #ef4444;background:#fef2f2;color:#991b1b;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;font-weight:600;">Estoque insuficiente para esta produção.</div>'
                                );
                            })
                            ->columnSpanFull(),
                        Placeholder::make('ingredients_cost_preview')
                            ->label('Custo de ingredientes')
                            ->content(fn (Get $get): string => self::currency(self::previewFromState($get)['ingredients_cost'] ?? 0.0)),
                        Placeholder::make('packaging_cost_preview')
                            ->label('Custo de embalagem')
                            ->content(fn (Get $get): string => self::currency(self::previewFromState($get)['packaging_cost'] ?? 0.0)),
                        Placeholder::make('overhead_cost_preview')
                            ->label('Custos indiretos')
                            ->content(fn (Get $get): string => self::currency(self::previewFromState($get)['overhead_cost'] ?? 0.0)),
                        Placeholder::make('total_cogs_preview')
                            ->label('CPV total')
                            ->content(fn (Get $get): string => self::currency(self::previewFromState($get)['total_cogs'] ?? 0.0)),
                        Placeholder::make('cogs_per_unit_preview')
                            ->label('CPV por unidade')
                            ->content(fn (Get $get): string => self::currency(self::previewFromState($get)['cogs_per_unit'] ?? 0.0)),
                        Placeholder::make('suggested_price_preview')
                            ->label('Preço sugerido por unidade')
                            ->content(fn (Get $get): string => self::currency(self::previewFromState($get)['suggested_unit_price'] ?? 0.0)),
                        Placeholder::make('margin_per_unit_preview')
                            ->label('Margem por unidade')
                            ->content(fn (Get $get): string => self::currency(self::previewFromState($get)['margin_per_unit'] ?? 0.0)),
                        Placeholder::make('stock_shortages')
                            ->label('Ingredientes em falta')
                            ->content(function (Get $get): HtmlString {
                                $preview = self::previewFromState($get);
                                $shortages = $preview['shortages'] ?? [];

                                if ((bool) $get('recipe_id') === false || (int) ($get('produced_units') ?? 0) <= 0) {
                                    return new HtmlString('<span style="color:#4b5563;">Selecione a receita e a quantidade para validar faltas no estoque.</span>');
                                }

                                if ($shortages === []) {
                                    return new HtmlString('<div style="border:1px solid #10b981;background:#ecfdf5;color:#065f46;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;font-weight:600;">Sem ingredientes em falta para esta produção.</div>');
                                }

                                $rows = collect($shortages)
                                    ->map(fn (array $item): string => sprintf(
                                        '<li><strong>%s</strong>: faltam %s g</li>',
                                        e((string) ($item['ingredient_name'] ?? '-')),
                                        number_format((float) round((float) ($item['shortage_g'] ?? 0)), 0, ',', '.')
                                    ))
                                    ->implode('');

                                return new HtmlString(
                                    '<div style="border:1px solid #ef4444;background:#fef2f2;color:#991b1b;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;font-weight:600;"><p style="margin:0 0 0.25rem 0;">Ingredientes insuficientes:</p><ul style="margin:0;padding-left:1.25rem;list-style:disc;">'.$rows.'</ul></div>'
                                );
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('produced_at', 'desc')
            ->columns([
                TextColumn::make('produced_at')
                    ->label('Data')
                    ->date()
                    ->sortable(),
                TextColumn::make('recipe.name')
                    ->label('Receita')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('produced_units')
                    ->label('Qtd. produzida')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('sold_units')
                    ->label('Qtd. vendida')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('available_units')
                    ->label('Qtd. disponível')
                    ->numeric(decimalPlaces: 0)
                    ->state(fn (ProductionBatch $record): int => $record->available_units),
                TextColumn::make('total_cogs')
                    ->label('CPV total')
                    ->formatStateUsing(fn (float $state): string => self::currency($state))
                    ->sortable(),
                TextColumn::make('cogs_per_unit')
                    ->label('CPV/unidade')
                    ->formatStateUsing(fn (float $state): string => self::currency($state))
                    ->sortable(),
                TextColumn::make('suggested_unit_price')
                    ->label('Preço sugerido')
                    ->formatStateUsing(fn (?float $state): string => $state === null ? '-' : self::currency($state))
                    ->sortable(),
                TextColumn::make('margin_per_unit')
                    ->label('Margem/unidade')
                    ->formatStateUsing(fn (?float $state): string => $state === null ? '-' : self::currency($state))
                    ->color(fn (?float $state): string => $state === null ? 'gray' : ($state >= 0 ? 'success' : 'danger')),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Resumo do lote')
                    ->schema([
                        TextEntry::make('recipe.name')
                            ->label('Receita'),
                        TextEntry::make('produced_at')
                            ->label('Data')
                            ->date(),
                        TextEntry::make('produced_units')
                            ->label('Qtd. produzida')
                            ->formatStateUsing(fn (float $state): string => number_format((float) round($state), 0, ',', '.')),
                        TextEntry::make('sold_units')
                            ->label('Qtd. vendida')
                            ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.')),
                        TextEntry::make('available_units')
                            ->label('Qtd. disponível')
                            ->state(fn (ProductionBatch $record): int => $record->available_units)
                            ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.')),
                        TextEntry::make('ingredients_cost')
                            ->label('Custo de ingredientes')
                            ->formatStateUsing(fn (float $state): string => self::currency($state)),
                        TextEntry::make('packaging_cost')
                            ->label('Custo de embalagem')
                            ->formatStateUsing(fn (float $state): string => self::currency($state)),
                        TextEntry::make('overhead_cost')
                            ->label('Custos indiretos')
                            ->formatStateUsing(fn (float $state): string => self::currency($state)),
                        TextEntry::make('total_cogs')
                            ->label('CPV total')
                            ->formatStateUsing(fn (float $state): string => self::currency($state)),
                        TextEntry::make('cogs_per_unit')
                            ->label('CPV/unidade')
                            ->formatStateUsing(fn (float $state): string => self::currency($state)),
                        TextEntry::make('suggested_unit_price')
                            ->label('Preço sugerido')
                            ->formatStateUsing(fn (?float $state): string => $state === null ? '-' : self::currency($state)),
                        TextEntry::make('margin_per_unit')
                            ->label('Margem/unidade')
                            ->formatStateUsing(fn (?float $state): string => $state === null ? '-' : self::currency($state))
                            ->color(fn (?float $state): string => $state === null ? 'gray' : ($state >= 0 ? 'success' : 'danger')),
                        TextEntry::make('notes')
                            ->label('Notas')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                InfolistSection::make('Ingredientes consumidos')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('ingredient_name')
                                    ->label('Ingrediente'),
                                TextEntry::make('quantity_used_g')
                                    ->label('Quantidade (g)')
                                    ->formatStateUsing(fn (float $state): string => number_format((float) round($state), 0, ',', '.')),
                                TextEntry::make('unit_cost')
                                    ->label('Custo/g')
                                    ->formatStateUsing(fn (float $state): string => self::currency($state)),
                                TextEntry::make('line_cost')
                                    ->label('Custo linha')
                                    ->formatStateUsing(fn (float $state): string => self::currency($state)),
                            ])
                            ->columns(4)
                            ->contained(false),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionBatches::route('/'),
            'create' => Pages\CreateProductionBatch::route('/create'),
            'view' => Pages\ViewProductionBatch::route('/{record}'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function previewFromState(Get $get): array
    {
        $recipeId = $get('recipe_id');
        $producedUnits = (int) ($get('produced_units') ?? 0);

        if (! $recipeId || $producedUnits <= 0) {
            return [];
        }

        $recipe = self::resolveRecipe((int) $recipeId);
        if (! $recipe) {
            return [];
        }

        return app(ProductionBatchService::class)->previewForRecipe($recipe, $producedUnits);
    }

    public static function resolveRecipe(int $recipeId): ?Recipe
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $query = Recipe::query()
            ->with(['items.ingredient'])
            ->whereKey($recipeId)
            ->where('is_active', true);

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query->first();
    }

    private static function currency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }
}
