<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Administração';

    protected static ?string $navigationLabel = 'Subscrições';

    protected static ?string $modelLabel = 'subscrição';

    protected static ?string $pluralModelLabel = 'subscrições';

    protected static ?int $navigationSort = 3;

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
        return (Auth::user()?->isAdmin() ?? false) && (Auth::user()?->can('manage plans') ?? false);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'plan']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Utilizador')
                    ->relationship('user', 'name')
                    ->getOptionLabelFromRecordUsing(fn (User $record): string => $record->name.' ('.$record->email.')')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required(),
                Select::make('plan_id')
                    ->label('Plano')
                    ->relationship('plan', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Plan $record): string => $record->name.' ('.number_format((float) $record->price, 2, ',', '.').' '.$record->currency.')')
                    ->searchable(['name', 'code'])
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                        $plan = filled($state) ? Plan::query()->find((int) $state) : null;
                        if (! $plan) {
                            return;
                        }

                        $set('price', round((float) $plan->price, 2));
                        $set('currency', (string) $plan->currency);

                        $startedAtRaw = $get('started_at');
                        $startedAt = filled($startedAtRaw) ? Carbon::parse((string) $startedAtRaw) : now();
                        $set('ends_at', $startedAt->copy()->addMonthsNoOverflow(max((int) $plan->duration_months, 1)));
                    }),
                Select::make('status')
                    ->label('Estado')
                    ->options(Subscription::statusOptions())
                    ->required()
                    ->default(Subscription::STATUS_ACTIVE)
                    ->native(false),
                DateTimePicker::make('started_at')
                    ->label('Início')
                    ->required()
                    ->seconds(false)
                    ->default(now())
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                        if (blank($state)) {
                            return;
                        }

                        $planId = $get('plan_id');
                        $plan = filled($planId) ? Plan::query()->find((int) $planId) : null;
                        if (! $plan) {
                            return;
                        }

                        $startedAt = Carbon::parse((string) $state);
                        $set('ends_at', $startedAt->copy()->addMonthsNoOverflow(max((int) $plan->duration_months, 1)));
                    }),
                DateTimePicker::make('ends_at')
                    ->label('Fim')
                    ->seconds(false)
                    ->helperText('Se vazio, será calculado pelo plano (duração mensal).'),
                TextInput::make('price')
                    ->label('Preço cobrado')
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
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('started_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Utilizador')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('plan.name')
                    ->label('Plano')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn (Subscription $record): string => $record->resolved_status)
                    ->formatStateUsing(fn (string $state): string => Subscription::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Subscription::STATUS_ACTIVE => 'success',
                        Subscription::STATUS_CANCELLED => 'warning',
                        Subscription::STATUS_EXPIRED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('started_at')
                    ->label('Início')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Fim')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('price')
                    ->label('Preço')
                    ->formatStateUsing(fn (float $state, Subscription $record): string => number_format($state, 2, ',', '.').' '.$record->currency)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Subscription::statusOptions()),
                SelectFilter::make('plan_id')
                    ->label('Plano')
                    ->relationship('plan', 'name')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
