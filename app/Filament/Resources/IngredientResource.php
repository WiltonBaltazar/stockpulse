<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Services\InventoryMovementService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
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
                TextInput::make('package_quantity_g')
                    ->label('Quantidade da Embalagem (g)')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->dehydrateStateUsing(fn ($state): int => max((int) ($state ?? 0), 1))
                    ->minValue(1)
                    ->step(1),
                TextInput::make('package_cost')
                    ->label('Custo da Embalagem')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01)
                    ->suffix('MT'),
                TextInput::make('stock_quantity_g')
                    ->label('Estoque Atual (g)')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->dehydrateStateUsing(fn ($state): int => max((int) ($state ?? 0), 0))
                    ->minValue(0)
                    ->step(1)
                    ->default(0),
                TextInput::make('reorder_level_g')
                    ->label('Ponto de Reposição (g)')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->dehydrateStateUsing(fn ($state): int => max((int) ($state ?? 0), 0))
                    ->minValue(0)
                    ->step(1)
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true)
                    ->required(),
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
                TextColumn::make('package_quantity_g')
                    ->label('Qtd. (g)')
                    ->formatStateUsing(fn (float $state): string => self::formatQuantity($state))
                    ->sortable(),
                TextColumn::make('package_cost')
                    ->label('Custo')
                    ->formatStateUsing(fn (float $state): string => self::formatCurrency($state))
                    ->sortable(),
                TextColumn::make('cost_per_gram')
                    ->label('Custo / g')
                    ->formatStateUsing(fn (float $state): string => self::formatCurrency($state))
                    ->sortable(),
                TextColumn::make('stock_quantity_g')
                    ->label('Estoque (g)')
                    ->formatStateUsing(fn (float $state): string => self::formatQuantity($state))
                    ->sortable(),
                TextColumn::make('reorder_level_g')
                    ->label('Reposição (g)')
                    ->formatStateUsing(fn (float $state): string => self::formatQuantity($state))
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
                        TextInput::make('quantity_g')
                            ->label('Quantidade a adicionar (g)')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->dehydrateStateUsing(fn ($state): int => max((int) ($state ?? 0), 1))
                            ->minValue(1)
                            ->step(1),
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

                        app(InventoryMovementService::class)->record(
                            ingredient: $record,
                            user: $user,
                            type: InventoryMovement::TYPE_PURCHASE,
                            quantityG: (int) ($data['quantity_g'] ?? 0),
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
                        TextInput::make('quantity_g')
                            ->label('Quantidade a retirar (g)')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->dehydrateStateUsing(fn ($state): int => max((int) ($state ?? 0), 1))
                            ->minValue(1)
                            ->step(1),
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

                        app(InventoryMovementService::class)->record(
                            ingredient: $record,
                            user: $user,
                            type: InventoryMovement::TYPE_MANUAL_OUT,
                            quantityG: -1 * (int) ($data['quantity_g'] ?? 0),
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
                        TextInput::make('new_stock_quantity_g')
                            ->label('Novo estoque contado (g)')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->dehydrateStateUsing(fn ($state): int => max((int) ($state ?? 0), 0))
                            ->minValue(0)
                            ->step(1)
                            ->default(fn (Ingredient $record): int => (int) round((float) $record->stock_quantity_g)),
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
                        $newStock = max((int) ($data['new_stock_quantity_g'] ?? 0), 0);
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

    private static function formatQuantity(float $value): string
    {
        return number_format((float) round($value), 0, ',', '.');
    }

    private static function formatCurrency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }
}
