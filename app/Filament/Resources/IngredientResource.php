<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Services\InventoryMovementService;
use App\Support\MeasurementUnitConverter;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Produção & Stock';

    protected static ?string $modelLabel = 'ingrediente';

    protected static ?string $pluralModelLabel = 'ingredientes';

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
        return Auth::user()?->can('manage ingredients') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                Select::make('measurement_type')
                    ->label('Tipo de medição')
                    ->options(Ingredient::measurementTypeOptions())
                    ->required()
                    ->default(Ingredient::MEASUREMENT_MASS)
                    ->native(false)
                    ->live(),
                Select::make('preferred_unit')
                    ->label('Unidade preferida para exibição')
                    ->options(fn (Get $get): array => Ingredient::preferredUnitOptionsForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS)))
                    ->required()
                    ->default(fn (Get $get): string => Ingredient::baseUnitForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS)))
                    ->native(false)
                    ->helperText('Esta unidade será usada para mostrar quantidades no sistema.'),
                TextInput::make('density_g_per_ml')
                    ->label('Densidade (g/ml)')
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.0001)
                    ->default(1)
                    ->visible(fn (Get $get): bool => (string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS) !== Ingredient::MEASUREMENT_UNIT)
                    ->helperText('Use 1 para água/leite, 0,92 para óleo, 1,42 para mel.'),
                TextInput::make('package_quantity_g')
                    ->label('Quantidade da Embalagem')
                    ->numeric()
                    ->required()
                    ->minValue(0.0001)
                    ->step(0.001)
                    ->suffix(fn (Get $get): string => self::shortUnit((string) ($get('package_input_unit') ?: Ingredient::baseUnitForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS)))))
                    ->dehydrateStateUsing(fn ($state, Get $get): int => self::toBaseQuantityOrFail(
                        value: $state,
                        measurementType: (string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS),
                        unit: (string) ($get('package_input_unit') ?: Ingredient::baseUnitForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS))),
                        density: (float) ($get('density_g_per_ml') ?: 0),
                        field: 'package_quantity_g',
                        minBase: 1,
                    )),
                Select::make('package_input_unit')
                    ->label('Unidade da embalagem')
                    ->options(fn (Get $get): array => Ingredient::inputUnitsForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS)))
                    ->default(fn (Get $get): string => Ingredient::baseUnitForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS)))
                    ->dehydrated(false)
                    ->required()
                    ->native(false),
                TextInput::make('package_cost')
                    ->label('Custo da Embalagem')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01)
                    ->suffix('MT'),
                TextInput::make('stock_quantity_g')
                    ->label('Estoque Atual')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.001)
                    ->default(0)
                    ->suffix(fn (Get $get): string => self::shortUnit((string) ($get('stock_input_unit') ?: Ingredient::baseUnitForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS)))))
                    ->dehydrateStateUsing(fn ($state, Get $get): int => self::toBaseQuantityOrFail(
                        value: $state,
                        measurementType: (string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS),
                        unit: (string) ($get('stock_input_unit') ?: Ingredient::baseUnitForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS))),
                        density: (float) ($get('density_g_per_ml') ?: 0),
                        field: 'stock_quantity_g',
                        minBase: 0,
                    )),
                Select::make('stock_input_unit')
                    ->label('Unidade do estoque')
                    ->options(fn (Get $get): array => Ingredient::inputUnitsForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS)))
                    ->default(fn (Get $get): string => Ingredient::baseUnitForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS)))
                    ->dehydrated(false)
                    ->required()
                    ->native(false),
                TextInput::make('reorder_level_g')
                    ->label('Ponto de Reposição')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.001)
                    ->default(0)
                    ->suffix(fn (Get $get): string => self::shortUnit((string) ($get('reorder_input_unit') ?: Ingredient::baseUnitForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS)))))
                    ->dehydrateStateUsing(fn ($state, Get $get): int => self::toBaseQuantityOrFail(
                        value: $state,
                        measurementType: (string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS),
                        unit: (string) ($get('reorder_input_unit') ?: Ingredient::baseUnitForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS))),
                        density: (float) ($get('density_g_per_ml') ?: 0),
                        field: 'reorder_level_g',
                        minBase: 0,
                    )),
                Select::make('reorder_input_unit')
                    ->label('Unidade da reposição')
                    ->options(fn (Get $get): array => Ingredient::inputUnitsForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS)))
                    ->default(fn (Get $get): string => Ingredient::baseUnitForType((string) ($get('measurement_type') ?: Ingredient::MEASUREMENT_MASS)))
                    ->dehydrated(false)
                    ->required()
                    ->native(false),
                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true)
                    ->required(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('measurement_type')
                    ->label('Medição')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Ingredient::measurementTypeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Ingredient::MEASUREMENT_MASS => 'info',
                        Ingredient::MEASUREMENT_VOLUME => 'warning',
                        Ingredient::MEASUREMENT_UNIT => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('package_quantity_g')
                    ->label('Qtd. embalagem')
                    ->formatStateUsing(fn (float $state, Ingredient $record): string => self::formatIngredientQuantity($record, $state))
                    ->sortable(),
                TextColumn::make('package_cost')
                    ->label('Custo')
                    ->formatStateUsing(fn (float $state): string => self::formatCurrency($state))
                    ->sortable(),
                TextColumn::make('cost_per_gram')
                    ->label('Custo / unidade')
                    ->getStateUsing(fn (Ingredient $record): string => self::formatCurrency($record->cost_per_base_unit).' / '.$record->baseUnit()),
                TextColumn::make('stock_quantity_g')
                    ->label('Estoque')
                    ->formatStateUsing(fn (float $state, Ingredient $record): string => self::formatIngredientQuantity($record, $state))
                    ->sortable(),
                TextColumn::make('reorder_level_g')
                    ->label('Reposição')
                    ->formatStateUsing(fn (float $state, Ingredient $record): string => self::formatIngredientQuantity($record, $state))
                    ->sortable(),
                TextColumn::make('stock_status')
                    ->label('Estado do estoque')
                    ->getStateUsing(fn (Ingredient $record): bool => (bool) $record->is_low_stock)
                    ->formatStateUsing(function (bool $state): HtmlString {
                        $arrow = $state ? '↓' : '↑';
                        $label = $state ? 'Estoque baixo' : 'Estoque ok';
                        $border = $state ? '#ef4444' : '#10b981';
                        $background = $state ? '#fef2f2' : '#ecfdf5';
                        $color = $state ? '#991b1b' : '#065f46';

                        return new HtmlString(sprintf(
                            '<span style="display:inline-flex;align-items:center;gap:0.35rem;border:1px solid %s;background:%s;color:%s;border-radius:9999px;padding:0.125rem 0.5rem;font-size:0.75rem;font-weight:600;line-height:1rem;"><span aria-hidden="true">%s</span><span>%s</span></span>',
                            $border,
                            $background,
                            $color,
                            $arrow,
                            $label,
                        ));
                    })
                    ->html(),
                TextColumn::make('inventory_value')
                    ->label('Valor em estoque')
                    ->getStateUsing(fn (Ingredient $record): string => self::formatCurrency($record->inventory_value)),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Ativo'),
            ])
            ->actions([
                Action::make('stock_in')
                    ->label('Entrada')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('quantity_input')
                            ->label('Quantidade a adicionar')
                            ->numeric()
                            ->required()
                            ->minValue(0.0001)
                            ->step(0.001),
                        Select::make('quantity_unit')
                            ->label('Unidade')
                            ->options(fn (Ingredient $record): array => Ingredient::inputUnitsForType($record->measurement_type))
                            ->default(fn (Ingredient $record): string => $record->preferred_unit)
                            ->required()
                            ->native(false),
                        TextInput::make('total_cost')
                            ->label('Custo total da compra (opcional)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('MT'),
                        DateTimePicker::make('moved_at')
                            ->label('Data/hora')
                            ->default(now())
                            ->seconds(false),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->maxLength(1000),
                    ])
                    ->action(function (Ingredient $record, array $data): void {
                        $user = Auth::user();
                        if (! $user) {
                            return;
                        }

                        $quantityBase = self::toBaseQuantityOrFail(
                            value: $data['quantity_input'] ?? 0,
                            measurementType: $record->measurement_type,
                            unit: (string) ($data['quantity_unit'] ?? $record->preferred_unit),
                            density: (float) ($record->density_g_per_ml ?? 0),
                            field: 'quantity_input',
                            minBase: 1,
                        );

                        app(InventoryMovementService::class)->record(
                            ingredient: $record,
                            user: $user,
                            type: InventoryMovement::TYPE_PURCHASE,
                            quantityG: $quantityBase,
                            movedAt: $data['moved_at'] ?? now(),
                            notes: $data['notes'] ?? null,
                            totalCost: isset($data['total_cost']) ? (float) $data['total_cost'] : null,
                        );

                        Notification::make()
                            ->title('Entrada de estoque registrada.')
                            ->success()
                            ->send();
                    }),
                Action::make('stock_out')
                    ->label('Saída')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('danger')
                    ->form([
                        TextInput::make('quantity_input')
                            ->label('Quantidade a retirar')
                            ->numeric()
                            ->required()
                            ->minValue(0.0001)
                            ->step(0.001),
                        Select::make('quantity_unit')
                            ->label('Unidade')
                            ->options(fn (Ingredient $record): array => Ingredient::inputUnitsForType($record->measurement_type))
                            ->default(fn (Ingredient $record): string => $record->preferred_unit)
                            ->required()
                            ->native(false),
                        DateTimePicker::make('moved_at')
                            ->label('Data/hora')
                            ->default(now())
                            ->seconds(false),
                        Textarea::make('notes')
                            ->label('Motivo/Notas')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->action(function (Ingredient $record, array $data): void {
                        $user = Auth::user();
                        if (! $user) {
                            return;
                        }

                        $quantityBase = self::toBaseQuantityOrFail(
                            value: $data['quantity_input'] ?? 0,
                            measurementType: $record->measurement_type,
                            unit: (string) ($data['quantity_unit'] ?? $record->preferred_unit),
                            density: (float) ($record->density_g_per_ml ?? 0),
                            field: 'quantity_input',
                            minBase: 1,
                        );

                        app(InventoryMovementService::class)->record(
                            ingredient: $record,
                            user: $user,
                            type: InventoryMovement::TYPE_MANUAL_OUT,
                            quantityG: -1 * $quantityBase,
                            movedAt: $data['moved_at'] ?? now(),
                            notes: $data['notes'] ?? null,
                        );

                        Notification::make()
                            ->title('Saída de estoque registrada.')
                            ->success()
                            ->send();
                    }),
                Action::make('stock_adjust')
                    ->label('Ajuste')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        TextInput::make('new_stock_quantity_input')
                            ->label('Novo estoque contado')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.001)
                            ->default(function (Ingredient $record): float {
                                return MeasurementUnitConverter::fromBase(
                                    baseValue: (float) $record->stock_quantity_g,
                                    unit: $record->preferred_unit,
                                    measurementType: $record->measurement_type,
                                    densityGPerMl: $record->density_g_per_ml,
                                );
                            }),
                        Select::make('new_stock_unit')
                            ->label('Unidade')
                            ->options(fn (Ingredient $record): array => Ingredient::inputUnitsForType($record->measurement_type))
                            ->default(fn (Ingredient $record): string => $record->preferred_unit)
                            ->required()
                            ->native(false),
                        DateTimePicker::make('moved_at')
                            ->label('Data/hora')
                            ->default(now())
                            ->seconds(false),
                        Textarea::make('notes')
                            ->label('Motivo do ajuste')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->action(function (Ingredient $record, array $data): void {
                        $user = Auth::user();
                        if (! $user) {
                            return;
                        }

                        $record->refresh();

                        $newStock = self::toBaseQuantityOrFail(
                            value: $data['new_stock_quantity_input'] ?? 0,
                            measurementType: $record->measurement_type,
                            unit: (string) ($data['new_stock_unit'] ?? $record->preferred_unit),
                            density: (float) ($record->density_g_per_ml ?? 0),
                            field: 'new_stock_quantity_input',
                            minBase: 0,
                        );

                        $currentStock = (int) round((float) $record->stock_quantity_g);
                        $delta = $newStock - $currentStock;

                        if ($delta === 0) {
                            Notification::make()
                                ->title('Sem alteração')
                                ->body('O estoque informado é igual ao estoque atual.')
                                ->warning()
                                ->send();

                            return;
                        }

                        app(InventoryMovementService::class)->record(
                            ingredient: $record,
                            user: $user,
                            type: InventoryMovement::TYPE_ADJUSTMENT,
                            quantityG: $delta,
                            movedAt: $data['moved_at'] ?? now(),
                            notes: $data['notes'] ?? null,
                        );

                        Notification::make()
                            ->title('Ajuste de estoque registrado.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }

    private static function toBaseQuantityOrFail(
        mixed $value,
        string $measurementType,
        string $unit,
        float $density,
        string $field,
        int $minBase,
    ): int {
        $numericValue = MeasurementUnitConverter::normalizeNumber($value);

        try {
            $base = MeasurementUnitConverter::toBase(
                value: $numericValue,
                unit: $unit,
                measurementType: $measurementType,
                densityGPerMl: $density,
            );
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                $field => $exception->getMessage(),
            ]);
        }

        $baseRounded = (int) round($base);

        if ($baseRounded < $minBase) {
            throw ValidationException::withMessages([
                $field => 'Informe uma quantidade válida maior que zero.',
            ]);
        }

        return $baseRounded;
    }

    private static function formatIngredientQuantity(Ingredient $ingredient, float $baseValue): string
    {
        return $ingredient->formatBaseQuantity($baseValue);
    }

    private static function shortUnit(string $unit): string
    {
        return MeasurementUnitConverter::shortUnitLabel($unit);
    }

    private static function formatCurrency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }
}
