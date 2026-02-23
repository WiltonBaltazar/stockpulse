<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Client;
use App\Models\Quote;
use App\Models\Recipe;
use App\Services\OrderService;
use App\Services\SaleService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Throwable;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Vendas & Financeiro';

    protected static ?string $navigationLabel = 'Orçamentos';

    protected static ?string $modelLabel = 'orçamento';

    protected static ?string $pluralModelLabel = 'orçamentos';

    protected static ?int $navigationSort = 2;

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
        $query = parent::getEloquentQuery()->with(['client', 'order'])->withCount('items');

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
                Section::make('Dados do orçamento')
                    ->schema([
                        DatePicker::make('quote_date')
                            ->label('Data orçamento')
                            ->required()
                            ->default(now())
                            ->native(false),
                        DatePicker::make('delivery_date')
                            ->label('Data entrega')
                            ->native(false),
                        TimePicker::make('delivery_time')
                            ->label('Hora entrega')
                            ->seconds(false),
                        Select::make('type')
                            ->label('Tipo')
                            ->options(Quote::typeOptions())
                            ->required()
                            ->default(Quote::TYPE_PICKUP)
                            ->native(false),
                        Select::make('status')
                            ->label('Estado')
                            ->options(Quote::statusOptions())
                            ->required()
                            ->default(Quote::STATUS_DRAFT)
                            ->native(false),
                        Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'name', fn (Builder $query): Builder => $query
                                ->when(! (Auth::user()?->isAdmin() ?? false), fn (Builder $inner): Builder => $inner->where('user_id', Auth::id()))
                                ->orderBy('name')
                            )
                            ->getOptionLabelFromRecordUsing(fn (Client $record): string => $record->contact_number ? $record->name.' ('.$record->contact_number.')' : $record->name)
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
                        TextInput::make('additional_fee')
                            ->label('Taxa adicional')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->suffix('MT'),
                        TextInput::make('discount')
                            ->label('Desconto')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->suffix('MT'),
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
                Section::make('Produtos do orçamento')
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
            ->defaultSort('quote_date', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Referência')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quote_date')
                    ->label('Data')
                    ->date()
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('Entrega')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('items_count')
                    ->label('Itens')
                    ->numeric(decimalPlaces: 0),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Quote::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Quote::STATUS_APPROVED => 'success',
                        Quote::STATUS_SENT => 'info',
                        Quote::STATUS_DRAFT => 'gray',
                        Quote::STATUS_CONVERTED => 'primary',
                        Quote::STATUS_REJECTED, Quote::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('total_amount')
                    ->label('Valor total')
                    ->formatStateUsing(fn (float $state): string => self::currency($state))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Quote::statusOptions()),
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
                Action::make('convert_to_order')
                    ->label('Gerar pedido')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Quote $record): bool => $record->status !== Quote::STATUS_CONVERTED)
                    ->action(function (Quote $record): void {
                        try {
                            $order = app(OrderService::class)->createFromQuote($record);

                            Notification::make()
                                ->title('Pedido criado com sucesso.')
                                ->body('Referência: '.$order->reference)
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->title('Não foi possível gerar o pedido.')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }

    private static function currency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }
}
