<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialTransactionResource\Pages;
use App\Models\FinancialTransaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FinancialTransactionResource extends Resource
{
    protected static ?string $model = FinancialTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Vendas & Financeiro';

    protected static ?string $modelLabel = 'transação financeira';

    protected static ?string $pluralModelLabel = 'transações financeiras';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('manage finances') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('manage finances') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->can('manage finances') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->can('manage finances') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('user');
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
                DatePicker::make('transaction_date')
                    ->label('Data')
                    ->required()
                    ->default(now())
                    ->native(false),
                Select::make('type')
                    ->label('Tipo')
                    ->options(FinancialTransaction::typeOptions())
                    ->required()
                    ->native(false),
                Select::make('status')
                    ->label('Estado')
                    ->options(FinancialTransaction::statusOptions())
                    ->required()
                    ->default(FinancialTransaction::STATUS_COMPLETED)
                    ->native(false),
                Select::make('source')
                    ->label('Fonte')
                    ->options(FinancialTransaction::sourceOptions())
                    ->required()
                    ->default(FinancialTransaction::SOURCE_SALES)
                    ->live()
                    ->native(false),
                TextInput::make('amount')
                    ->label('Valor')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01)
                    ->suffix('MT'),
                TextInput::make('credits')
                    ->label('Créditos')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->visible(fn ($get): bool => $get('source') === FinancialTransaction::SOURCE_CREDITS),
                TextInput::make('package_name')
                    ->label('Pacote de créditos')
                    ->maxLength(255)
                    ->visible(fn ($get): bool => $get('source') === FinancialTransaction::SOURCE_CREDITS),
                TextInput::make('counterparty')
                    ->label('Utilizador/Cliente')
                    ->maxLength(255),
                TextInput::make('reference')
                    ->label('Referência')
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
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Data')
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => FinancialTransaction::typeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === FinancialTransaction::TYPE_INCOME ? 'success' : 'danger'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => FinancialTransaction::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        FinancialTransaction::STATUS_COMPLETED => 'success',
                        FinancialTransaction::STATUS_PENDING => 'warning',
                        FinancialTransaction::STATUS_CANCELLED => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('source')
                    ->label('Fonte')
                    ->formatStateUsing(fn (string $state): string => FinancialTransaction::sourceOptions()[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn (float $state): string => self::currency($state))
                    ->sortable(),
                TextColumn::make('credits')
                    ->label('Créditos')
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '-' : (string) $state)
                    ->toggleable(),
                TextColumn::make('counterparty')
                    ->label('Utilizador/Cliente')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('reference')
                    ->label('Referência')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Registado por')
                    ->toggleable()
                    ->visible(fn (): bool => Auth::user()?->isAdmin() ?? false),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(FinancialTransaction::typeOptions()),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(FinancialTransaction::statusOptions()),
                SelectFilter::make('source')
                    ->label('Fonte')
                    ->options(FinancialTransaction::sourceOptions()),
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
            'index' => Pages\ListFinancialTransactions::route('/'),
            'create' => Pages\CreateFinancialTransaction::route('/create'),
            'edit' => Pages\EditFinancialTransaction::route('/{record}/edit'),
        ];
    }

    private static function currency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }
}
