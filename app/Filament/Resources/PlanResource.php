<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Administração';

    protected static ?string $navigationLabel = 'Planos';

    protected static ?string $modelLabel = 'plano';

    protected static ?string $pluralModelLabel = 'planos';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return (Auth::user()?->isAdmin() ?? false) && (Auth::user()?->can('manage plans') ?? false);
    }

    public static function canCreate(): bool
    {
        return (Auth::user()?->isAdmin() ?? false) && (Auth::user()?->can('manage plans') ?? false);
    }

    public static function canEdit($record): bool
    {
        return (Auth::user()?->isAdmin() ?? false) && (Auth::user()?->can('manage plans') ?? false);
    }

    public static function canDelete($record): bool
    {
        if (! ((Auth::user()?->isAdmin() ?? false) && (Auth::user()?->can('manage plans') ?? false))) {
            return false;
        }

        return $record instanceof Plan && $record->code !== Plan::CODE_BASIC;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['features', 'subscriptions']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label('Código')
                    ->required()
                    ->maxLength(60)
                    ->unique(ignoreRecord: true)
                    ->helperText('Exemplo: basic, pro, premium.'),
                TextInput::make('price')
                    ->label('Preço')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01)
                    ->default(0)
                    ->suffix('MT'),
                TextInput::make('currency')
                    ->label('Moeda')
                    ->required()
                    ->maxLength(8)
                    ->default('MT'),
                TextInput::make('duration_months')
                    ->label('Duração (meses)')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->default(1)
                    ->minValue(1)
                    ->step(1)
                    ->helperText('Cada subscrição deste plano terá esta duração mensal.'),
                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true)
                    ->required(),
                Textarea::make('description')
                    ->label('Descrição')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                CheckboxList::make('features')
                    ->label('Funcionalidades do plano')
                    ->relationship('features', 'name')
                    ->columns(2)
                    ->required()
                    ->helperText('Se não selecionar uma funcionalidade, ela ficará oculta e bloqueada para utilizadores desse plano.')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->columns([
                TextColumn::make('name')
                    ->label('Plano')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Código')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Preço')
                    ->formatStateUsing(fn (float $state, Plan $record): string => number_format($state, 2, ',', '.').' '.$record->currency)
                    ->sortable(),
                TextColumn::make('duration_months')
                    ->label('Duração')
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.').' mês(es)')
                    ->sortable(),
                TextColumn::make('features_count')
                    ->label('Funcionalidades')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('subscriptions_count')
                    ->label('Subscrições')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
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
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
