<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use App\Models\Feature;
use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Vendas & Financeiro';

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?string $modelLabel = 'cliente';

    protected static ?string $pluralModelLabel = 'clientes';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return (Auth::user()?->can('manage clients') ?? false)
            && (Auth::user()?->hasFeature(Feature::CLIENTS) ?? false);
    }

    public static function canCreate(): bool
    {
        return (Auth::user()?->can('manage clients') ?? false)
            && (Auth::user()?->hasFeature(Feature::CLIENTS) ?? false);
    }

    public static function canEdit($record): bool
    {
        return (Auth::user()?->can('manage clients') ?? false)
            && (Auth::user()?->hasFeature(Feature::CLIENTS) ?? false);
    }

    public static function canDelete($record): bool
    {
        return (Auth::user()?->can('manage clients') ?? false)
            && (Auth::user()?->hasFeature(Feature::CLIENTS) ?? false);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withCount('sales')
            ->withSum('sales as sales_total_amount', 'total_amount');

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
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('contact_number')
                    ->label('Contacto')
                    ->helperText('Formato MZ: +258841234567 ou 841234567')
                    ->placeholder('84 123 4567')
                    ->prefix('+258')
                    ->tel()
                    ->mask('99 999 9999')
                    ->stripCharacters([' '])
                    ->maxLength(40)
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Client::normalizeMozambicanContact($state) ?? $state : null)
                    ->rule(
                        static function (): Closure {
                            return static function (string $attribute, mixed $value, Closure $fail): void {
                                if (blank($value)) {
                                    return;
                                }

                                if (! Client::isValidMozambicanContact((string) $value)) {
                                    $fail('Use um número válido de Moçambique (ex: +258841234567).');
                                }
                            };
                        }
                    ),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('document_number')
                    ->label('Documento (NIF/BI)')
                    ->maxLength(80),
                TextInput::make('address')
                    ->label('Endereço')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true)
                    ->required(),
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
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_number')
                    ->label('Contacto')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sales_count')
                    ->label('Vendas')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('sales_total_amount')
                    ->label('Total vendido')
                    ->formatStateUsing(fn ($state): string => self::currency((float) ($state ?? 0)))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Ativo'),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    private static function currency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }
}
