<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Models\Feature;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Support\MeasurementUnitConverter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';

    protected static ?string $navigationGroup = 'Produção & Stock';

    protected static ?string $modelLabel = 'receita';

    protected static ?string $pluralModelLabel = 'receitas';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public static function canViewAny(): bool
    {
        return (Auth::user()?->can('manage recipes') ?? false)
            && (Auth::user()?->hasFeature(Feature::RECIPES) ?? false);
    }

    public static function canCreate(): bool
    {
        return (Auth::user()?->can('manage recipes') ?? false)
            && (Auth::user()?->hasFeature(Feature::RECIPES) ?? false);
    }

    public static function canEdit($record): bool
    {
        return (Auth::user()?->can('manage recipes') ?? false)
            && (Auth::user()?->hasFeature(Feature::RECIPES) ?? false);
    }

    public static function canDelete($record): bool
    {
        return (Auth::user()?->can('manage recipes') ?? false)
            && (Auth::user()?->hasFeature(Feature::RECIPES) ?? false);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Como usar esta calculadora')
                    ->schema([
                        Placeholder::make('guide_name')
                            ->label('Nome')
                            ->content('Identifica a receita (ex.: Bolo de Chocolate).'),
                        Placeholder::make('guide_priced_at')
                            ->label('Precificado em')
                            ->content('Data de referência do cálculo, útil para revisão futura de preços.'),
                        Placeholder::make('guide_yield_units')
                            ->label('Rendimento (unidades)')
                            ->content('Quantidade de unidades que a receita produz.'),
                        Placeholder::make('guide_packaging')
                            ->label('Custo da Embalagem / Unidade')
                            ->content('Valor da embalagem por unidade vendida.'),
                        Placeholder::make('guide_overhead')
                            ->label('Custos Indiretos %')
                            ->content('Percentual para cobrir energia, gás, água e perdas operacionais.'),
                        Placeholder::make('guide_markup')
                            ->label('Multiplicador de Lucro')
                            ->content('Fator aplicado após custos para definir margem/lucro.'),
                        Placeholder::make('guide_items')
                            ->label('Itens da Receita')
                            ->content('Selecione cada ingrediente, informe quantidade e unidade (kg, g, L, ml, colher, chávena ou un).'),
                        Placeholder::make('guide_formulas')
                            ->label('Cálculo automático')
                            ->content('1) Soma ingredientes, 2) aplica custos indiretos, 3) aplica multiplicador, 4) divide pelo rendimento e 5) soma embalagem por unidade.'),
                    ])
                    ->columns(2),
                Section::make('Receita')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('priced_at')
                            ->label('Precificado em')
                            ->default(now())
                            ->native(false),
                        TextInput::make('yield_units')
                            ->label('Rendimento (unidades)')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->dehydrateStateUsing(fn ($state): int => max((int) ($state ?? 0), 1))
                            ->minValue(1)
                            ->step(1)
                            ->default(1),
                        TextInput::make('packaging_cost_per_unit')
                            ->label('Custo da Embalagem / Unidade')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('MT')
                            ->default(0),
                        TextInput::make('overhead_percent')
                            ->label('Custos Indiretos %')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('%')
                            ->default(25),
                        TextInput::make('markup_multiplier')
                            ->label('Multiplicador de Lucro')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.001)
                            ->default(3),
                    ])
                    ->columns(2),
                Section::make('Ingredientes')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->label('Itens da Receita')
                            ->reorderable(false)
                            ->schema([
                                Select::make('ingredient_id')
                                    ->label('Ingrediente')
                                    ->relationship('ingredient', 'name', function ($query) {
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
                                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                                        if (! $state) {
                                            $set('quantity_unit', MeasurementUnitConverter::UNIT_G);

                                            return;
                                        }

                                        $ingredient = Ingredient::query()->find((int) $state);
                                        $set('quantity_unit', $ingredient?->baseUnit() ?? MeasurementUnitConverter::UNIT_G);
                                    })
                                    ->required(),
                                TextInput::make('quantity_used_g')
                                    ->label('Quantidade usada')
                                    ->numeric()
                                    ->dehydrateStateUsing(function ($state, Get $get): int {
                                        $ingredientId = (int) ($get('ingredient_id') ?? 0);
                                        $unit = (string) ($get('quantity_unit') ?? MeasurementUnitConverter::UNIT_G);

                                        if ($ingredientId <= 0) {
                                            return max((int) round(MeasurementUnitConverter::normalizeNumber($state)), 1);
                                        }

                                        $ingredient = Ingredient::query()->find($ingredientId);
                                        if (! $ingredient) {
                                            return max((int) round(MeasurementUnitConverter::normalizeNumber($state)), 1);
                                        }

                                        try {
                                            $baseQuantity = MeasurementUnitConverter::toBase(
                                                value: MeasurementUnitConverter::normalizeNumber($state),
                                                unit: $unit,
                                                measurementType: $ingredient->measurement_type,
                                                densityGPerMl: $ingredient->density_g_per_ml,
                                            );
                                        } catch (InvalidArgumentException $exception) {
                                            throw ValidationException::withMessages([
                                                'items' => $exception->getMessage(),
                                            ]);
                                        }

                                        return max((int) round($baseQuantity), 1);
                                    })
                                    ->minValue(0.0001)
                                    ->step(0.001)
                                    ->suffix(fn (Get $get): string => MeasurementUnitConverter::shortUnitLabel((string) ($get('quantity_unit') ?? MeasurementUnitConverter::UNIT_G)))
                                    ->required(),
                                Select::make('quantity_unit')
                                    ->label('Unidade')
                                    ->options(function (Get $get): array {
                                        $ingredientId = (int) ($get('ingredient_id') ?? 0);
                                        if ($ingredientId <= 0) {
                                            return [
                                                MeasurementUnitConverter::UNIT_G => 'g',
                                                MeasurementUnitConverter::UNIT_KG => 'kg',
                                                MeasurementUnitConverter::UNIT_ML => 'ml',
                                                MeasurementUnitConverter::UNIT_L => 'L',
                                                MeasurementUnitConverter::UNIT_UNIT => 'un',
                                            ];
                                        }

                                        $ingredient = Ingredient::query()->find($ingredientId);
                                        if (! $ingredient) {
                                            return Ingredient::inputUnitsForType(Ingredient::MEASUREMENT_MASS);
                                        }

                                        return Ingredient::inputUnitsForType($ingredient->measurement_type);
                                    })
                                    ->default(MeasurementUnitConverter::UNIT_G)
                                    ->afterStateHydrated(function (Set $set, Get $get, $state): void {
                                        if (filled($state)) {
                                            return;
                                        }

                                        $ingredientId = (int) ($get('ingredient_id') ?? 0);
                                        if ($ingredientId <= 0) {
                                            $set('quantity_unit', MeasurementUnitConverter::UNIT_G);

                                            return;
                                        }

                                        $ingredient = Ingredient::query()->find($ingredientId);
                                        $set('quantity_unit', $ingredient?->baseUnit() ?? MeasurementUnitConverter::UNIT_G);
                                    })
                                    ->dehydrated(false)
                                    ->required()
                                    ->native(false),
                            ])
                            ->defaultItems(1)
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Resumo da Precificação')
                    ->schema([
                        Placeholder::make('ingredients_cost')
                            ->label('Custo Total dos Ingredientes')
                            ->content(fn (Get $get): string => self::currency(self::ingredientsCostFromState($get('items')))),
                        Placeholder::make('cost_with_overhead')
                            ->label('Custo + Custos Indiretos')
                            ->content(function (Get $get): string {
                                $ingredientsCost = self::ingredientsCostFromState($get('items'));
                                $overheadPercent = (float) ($get('overhead_percent') ?: 0);
                                $costWithOverhead = $ingredientsCost * (1 + ($overheadPercent / 100));

                                return self::currency($costWithOverhead);
                            }),
                        Placeholder::make('target_revenue')
                            ->label('Meta de Receita')
                            ->content(function (Get $get): string {
                                $ingredientsCost = self::ingredientsCostFromState($get('items'));
                                $overheadPercent = (float) ($get('overhead_percent') ?: 0);
                                $markupMultiplier = (float) ($get('markup_multiplier') ?: 0);
                                $costWithOverhead = $ingredientsCost * (1 + ($overheadPercent / 100));

                                return self::currency($costWithOverhead * $markupMultiplier);
                            }),
                        Placeholder::make('final_unit_price')
                            ->label('Preço Final / Unidade')
                            ->content(function (Get $get): string {
                                $ingredientsCost = self::ingredientsCostFromState($get('items'));
                                $overheadPercent = (float) ($get('overhead_percent') ?: 0);
                                $markupMultiplier = (float) ($get('markup_multiplier') ?: 0);
                                $yieldUnits = max((float) ($get('yield_units') ?: 1), 0.000001);
                                $packagingCostPerUnit = (float) ($get('packaging_cost_per_unit') ?: 0);
                                $costWithOverhead = $ingredientsCost * (1 + ($overheadPercent / 100));
                                $targetRevenue = $costWithOverhead * $markupMultiplier;
                                $unitPrice = $targetRevenue / $yieldUnits;

                                return self::currency($unitPrice + $packagingCostPerUnit);
                            }),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ativa' : 'Arquivada')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->visible(fn (): bool => Recipe::supportsActiveState()),
                TextColumn::make('priced_at')
                    ->label('Precificado em')
                    ->date()
                    ->sortable(),
                TextColumn::make('yield_units')
                    ->label('Rendimento')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('ingredients_cost')
                    ->label('Ingredientes')
                    ->getStateUsing(fn (Recipe $record): string => self::currency($record->ingredients_cost)),
                TextColumn::make('final_unit_price')
                    ->label('Final / Unidade')
                    ->getStateUsing(fn (Recipe $record): string => self::currency($record->final_unit_price)),
                TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Mostrar receitas ativas')
                    ->placeholder('Todas')
                    ->trueLabel('Ativas')
                    ->falseLabel('Arquivadas')
                    ->default(true)
                    ->visible(fn (): bool => Recipe::supportsActiveState()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('archive')
                    ->label('Arquivar')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->visible(fn (Recipe $record): bool => Recipe::supportsActiveState() && (bool) $record->is_active)
                    ->requiresConfirmation()
                    ->action(fn (Recipe $record): bool => Recipe::supportsActiveState() ? $record->update(['is_active' => false]) : false),
                Tables\Actions\Action::make('restore')
                    ->label('Reativar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (Recipe $record): bool => Recipe::supportsActiveState() && ! $record->is_active)
                    ->requiresConfirmation()
                    ->action(fn (Recipe $record): bool => Recipe::supportsActiveState() ? $record->update(['is_active' => true]) : false),
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }

    private static function ingredientsCostFromState(?array $items): float
    {
        if (! $items) {
            return 0.0;
        }

        $ingredientIds = collect($items)
            ->pluck('ingredient_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($ingredientIds === []) {
            return 0.0;
        }

        $user = Auth::user();
        $ingredientsQuery = Ingredient::query()->whereIn('id', $ingredientIds);

        if ($user && ! $user->isAdmin()) {
            $ingredientsQuery->where('user_id', $user->id);
        }

        $ingredients = $ingredientsQuery
            ->get(['id', 'package_cost', 'package_quantity_g'])
            ->keyBy('id');

        return (float) collect($items)->sum(function (array $item) use ($ingredients): float {
            $ingredientId = $item['ingredient_id'] ?? null;
            $quantityUsed = (float) ($item['quantity_used_g'] ?? 0);

            if (! $ingredientId || $quantityUsed <= 0) {
                return 0.0;
            }

            $ingredient = $ingredients->get((int) $ingredientId);
            if (! $ingredient || (float) $ingredient->package_quantity_g <= 0.0) {
                return 0.0;
            }

            $costPerGram = (float) $ingredient->package_cost / (float) $ingredient->package_quantity_g;

            return $costPerGram * $quantityUsed;
        });
    }

    private static function currency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }
}
