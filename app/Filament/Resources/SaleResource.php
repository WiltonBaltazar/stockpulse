<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\ProductionBatch;
use App\Models\Sale;
use App\Services\SaleService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Vendas & Financeiro';

    protected static ?string $navigationLabel = 'Vendas';

    protected static ?string $modelLabel = 'venda';

    protected static ?string $pluralModelLabel = 'vendas';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('manage sales') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('manage sales') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->can('manage sales') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->can('manage sales') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['recipe', 'user']);

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
                Select::make('recipe_id')
                    ->label('Receita (opcional)')
                    ->relationship('recipe', 'name', fn (Builder $query): Builder => $query
                        ->where('is_active', true)
                        ->when(! (Auth::user()?->isAdmin() ?? false), fn (Builder $inner): Builder => $inner->where('user_id', Auth::id()))
                        ->orderBy('name')
                    )
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        if (! $state) {
                            return;
                        }

                        $user = Auth::user();
                        if (! $user) {
                            return;
                        }

                        $unitPrice = app(SaleService::class)->suggestedUnitPriceForRecipe($user, (int) $state);
                        if ($unitPrice > 0) {
                            $set('unit_price', $unitPrice);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->helperText('Se selecionar receita, preço unitário e custo do que foi vendido são calculados automaticamente (FIFO) quando a venda estiver concluída.'),
                TextInput::make('item_name')
                    ->label('Item vendido')
                    ->requiredWithout('recipe_id')
                    ->disabled(fn (Get $get): bool => filled($get('recipe_id')))
                    ->maxLength(255)
                    ->helperText('Use para vendas avulsas (salgados, bebidas, extras).'),
                DateTimePicker::make('sold_at')
                    ->label('Data/hora da venda')
                    ->required()
                    ->default(now())
                    ->seconds(false),
                Select::make('channel')
                    ->label('Canal')
                    ->options(Sale::channelOptions())
                    ->required()
                    ->default(Sale::CHANNEL_OFFLINE)
                    ->native(false),
                Select::make('payment_method')
                    ->label('Pagamento')
                    ->options(Sale::paymentOptions())
                    ->required()
                    ->default(Sale::PAYMENT_CASH)
                    ->native(false),
                Select::make('status')
                    ->label('Estado')
                    ->options(Sale::statusOptions())
                    ->required()
                    ->default(Sale::STATUS_COMPLETED)
                    ->native(false),
                TextInput::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->dehydrateStateUsing(fn ($state): int => max((int) ($state ?? 0), 1))
                    ->default(1)
                    ->minValue(1)
                    ->step(1),
                Placeholder::make('production_stock_indicator')
                    ->label('Disponibilidade para venda')
                    ->content(fn (Get $get): HtmlString => self::productionAvailabilityIndicator($get))
                    ->columnSpanFull(),
                TextInput::make('unit_price')
                    ->label('Preço unitário')
                    ->numeric()
                    ->required(fn (Get $get): bool => blank($get('recipe_id')))
                    ->disabled(fn (Get $get): bool => filled($get('recipe_id')))
                    ->formatStateUsing(fn ($state): mixed => filled($state) ? number_format((float) $state, 2, '.', '') : $state)
                    ->dehydrateStateUsing(fn ($state): float => round((float) $state, 2))
                    ->minValue(0.01)
                    ->step(0.01)
                    ->helperText('Em vendas por receita, este valor é preenchido automaticamente.')
                    ->suffix('MT'),
                TextInput::make('customer_name')
                    ->label('Cliente')
                    ->maxLength(255),
                Textarea::make('notes')
                    ->label('Notas')
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sold_at', 'desc')
            ->columns([
                TextColumn::make('sold_at')
                    ->label('Data')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('resolved_item_name')
                    ->label('Item')
                    ->searchable(['item_name'])
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('item_name', $direction)),
                TextColumn::make('channel')
                    ->label('Canal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Sale::channelOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === Sale::CHANNEL_OFFLINE ? 'warning' : 'info'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Sale::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Sale::STATUS_COMPLETED => 'success',
                        Sale::STATUS_PENDING => 'warning',
                        Sale::STATUS_CANCELLED => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('quantity')
                    ->label('Qtd.')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total vendido')
                    ->formatStateUsing(fn (float $state): string => self::currency($state))
                    ->sortable(),
                TextColumn::make('estimated_total_cost')
                    ->label('Custo do que foi vendido')
                    ->formatStateUsing(fn (?float $state): string => $state === null ? '-' : self::currency($state))
                    ->toggleable(),
                TextColumn::make('estimated_profit')
                    ->label('Ganho realizado')
                    ->formatStateUsing(fn (?float $state): string => $state === null ? '-' : self::currency($state))
                    ->color(fn (?float $state): string => $state === null ? 'gray' : ($state >= 0 ? 'success' : 'danger'))
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->formatStateUsing(fn (string $state): string => Sale::paymentOptions()[$state] ?? $state)
                    ->toggleable(),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Registado por')
                    ->toggleable()
                    ->visible(fn (): bool => Auth::user()?->isAdmin() ?? false),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label('Canal')
                    ->options(Sale::channelOptions()),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Sale::statusOptions()),
                SelectFilter::make('payment_method')
                    ->label('Pagamento')
                    ->options(Sale::paymentOptions()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }

    private static function currency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }

    private static function productionAvailabilityIndicator(Get $get): HtmlString
    {
        $recipeId = (int) ($get('recipe_id') ?? 0);
        if ($recipeId <= 0) {
            return new HtmlString(
                '<div style="border:1px solid #d1d5db;background:#f9fafb;color:#374151;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;">Selecione uma receita para validar disponibilidade de produção.</div>'
            );
        }

        $status = (string) ($get('status') ?? Sale::STATUS_COMPLETED);
        if ($status !== Sale::STATUS_COMPLETED) {
            return new HtmlString(
                '<div style="border:1px solid #f59e0b;background:#fffbeb;color:#92400e;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;font-weight:600;">Validação de lotes aplica-se apenas para vendas com estado Concluída.</div>'
            );
        }

        $required = max((int) round((float) ($get('quantity') ?? 0)), 1);
        $available = self::availableUnitsForRecipe($recipeId);

        if ($available >= $required) {
            return new HtmlString(sprintf(
                '<div style="border:1px solid #10b981;background:#ecfdf5;color:#065f46;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;font-weight:600;">Estoque de produção suficiente: disponível %s un | venda %s un.</div>',
                number_format((float) $available, 0, ',', '.'),
                number_format((float) $required, 0, ',', '.')
            ));
        }

        $shortage = max($required - $available, 0);

        return new HtmlString(sprintf(
            '<div style="border:1px solid #ef4444;background:#fef2f2;color:#991b1b;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;font-weight:600;">Estoque de produção insuficiente: disponível %s un | venda %s un | faltam %s un.</div>',
            number_format((float) $available, 0, ',', '.'),
            number_format((float) $required, 0, ',', '.'),
            number_format((float) $shortage, 0, ',', '.')
        ));
    }

    private static function availableUnitsForRecipe(int $recipeId): int
    {
        $user = Auth::user();
        if (! $user) {
            return 0;
        }

        $query = ProductionBatch::query()
            ->where('recipe_id', $recipeId)
            ->where('user_id', $user->id);

        $produced = (int) round((float) (clone $query)->sum('produced_units'));
        $sold = (int) round((float) (clone $query)->sum('sold_units'));

        $available = max($produced - $sold, 0);

        // When editing, include this sale's currently allocated quantity to avoid false negatives.
        $record = request()->route('record');
        $recordId = $record instanceof Sale ? (int) $record->id : (int) $record;
        if ($recordId > 0) {
            $sale = Sale::query()->find($recordId);
            if (
                $sale
                && (int) ($sale->recipe_id ?? 0) === $recipeId
                && $sale->status === Sale::STATUS_COMPLETED
                && $sale->user_id === $user->id
            ) {
                $available += max((int) round((float) $sale->quantity), 0);
            }
        }

        return $available;
    }
}
