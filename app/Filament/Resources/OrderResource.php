<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Client;
use App\Models\Feature;
use App\Models\Order;
use App\Models\Recipe;
use App\Services\SaleService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Vendas & Financeiro';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $modelLabel = 'pedido';

    protected static ?string $pluralModelLabel = 'pedidos';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return (Auth::user()?->can('manage sales') ?? false)
            && (Auth::user()?->hasFeature(Feature::ORDERS) ?? false);
    }

    public static function canCreate(): bool
    {
        return (Auth::user()?->can('manage sales') ?? false)
            && (Auth::user()?->hasFeature(Feature::ORDERS) ?? false);
    }

    public static function canEdit($record): bool
    {
        return (Auth::user()?->can('manage sales') ?? false)
            && (Auth::user()?->hasFeature(Feature::ORDERS) ?? false);
    }

    public static function canDelete($record): bool
    {
        return (Auth::user()?->can('manage sales') ?? false)
            && (Auth::user()?->hasFeature(Feature::ORDERS) ?? false);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['client', 'quote'])->withCount('items');

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
                Section::make('Dados do pedido')
                    ->schema([
                        DateTimePicker::make('order_date')
                            ->label('Data do pedido')
                            ->required()
                            ->default(now())
                            ->seconds(false),
                        DateTimePicker::make('delivery_date')
                            ->label('Data/hora de entrega')
                            ->seconds(false),
                        Select::make('status')
                            ->label('Estado pedido')
                            ->options(Order::statusOptions())
                            ->required()
                            ->default(Order::STATUS_PENDING)
                            ->native(false),
                        Select::make('payment_status')
                            ->label('Estado pagamento')
                            ->options(Order::paymentStatusOptions())
                            ->required()
                            ->default(Order::PAYMENT_OPEN)
                            ->native(false),
                        Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'name', fn (Builder $query): Builder => $query
                                ->when(! (Auth::user()?->isAdmin() ?? false), fn (Builder $inner): Builder => $inner->where('user_id', Auth::id()))
                                ->orderBy('name')
                            )
                            ->searchable(['name', 'contact_number', 'email'])
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('contact_number')
                                    ->label('Contacto')
                                    ->maxLength(40),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('address')
                                    ->label('Endereço')
                                    ->maxLength(255),
                                Textarea::make('notes')
                                    ->label('Notas')
                                    ->maxLength(1000)
                                    ->columnSpanFull(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $data['user_id'] = Auth::id();
                                $data['is_active'] = true;

                                return Client::query()->create($data)->id;
                            }),
                        TextInput::make('reference')
                            ->label('Referência (automática)')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Gerada automaticamente ao guardar'),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->columnSpanFull()
                            ->maxLength(1000),
                    ])
                    ->columns(3),
                Section::make('Itens do pedido')
                    ->schema([
                        Repeater::make('items')
                            ->label('Itens')
                            ->defaultItems(1)
                            ->minItems(1)
                            ->reorderable(false)
                            ->schema([
                                Select::make('recipe_id')
                                    ->label('Receita/produto')
                                    ->options(function (): array {
                                        $query = Recipe::query()
                                            ->when(Recipe::supportsActiveState(), fn (Builder $inner): Builder => $inner->where('is_active', true))
                                            ->when(! (Auth::user()?->isAdmin() ?? false), fn (Builder $inner): Builder => $inner->where('user_id', Auth::id()))
                                            ->orderBy('name');

                                        return $query->pluck('name', 'id')->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set): void {
                                        if (! $state) {
                                            return;
                                        }

                                        $recipe = Recipe::query()->find((int) $state);
                                        if (! $recipe) {
                                            return;
                                        }

                                        $set('item_name', $recipe->name);

                                        $user = Auth::user();
                                        if (! $user) {
                                            return;
                                        }

                                        $unitPrice = app(SaleService::class)->suggestedUnitPriceForRecipe($user, (int) $recipe->id);
                                        if ($unitPrice > 0) {
                                            $set('unit_price', $unitPrice);
                                        }
                                    }),
                                TextInput::make('item_name')
                                    ->label('Item')
                                    ->requiredWithout('recipe_id')
                                    ->maxLength(255),
                                TextInput::make('quantity')
                                    ->label('Quantidade')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->step(1),
                                TextInput::make('unit_price')
                                    ->label('Valor unitário')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->suffix('MT'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order_date', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('N.º pedido')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_date')
                    ->label('Data pedido')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('Data entrega')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('total_amount')
                    ->label('Valor')
                    ->formatStateUsing(fn (float $state): string => self::currency($state))
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado pedido')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_DELIVERED => 'success',
                        Order::STATUS_READY => 'info',
                        Order::STATUS_PREPARING => 'warning',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('payment_status')
                    ->label('Estado pagamento')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::paymentStatusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Order::PAYMENT_PAID => 'success',
                        Order::PAYMENT_PARTIAL => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('items_count')
                    ->label('Itens')
                    ->numeric(decimalPlaces: 0),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado pedido')
                    ->options(Order::statusOptions()),
                SelectFilter::make('payment_status')
                    ->label('Estado pagamento')
                    ->options(Order::paymentStatusOptions()),
                SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name', fn (Builder $query): Builder => $query
                        ->when(! (Auth::user()?->isAdmin() ?? false), fn (Builder $inner): Builder => $inner->where('user_id', Auth::id()))
                        ->orderBy('name')
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('download_slip')
                    ->label('Comprovativo PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (Order $record): string => route('documents.orders.slip', ['order' => $record]))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    private static function currency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }
}
