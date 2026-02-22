<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Models\InventoryMovement;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InventoryMovementResource extends Resource
{
    protected static ?string $model = InventoryMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';

    protected static ?string $navigationGroup = 'Produção & Stock';

    protected static ?string $modelLabel = 'movimento';

    protected static ?string $pluralModelLabel = 'movimentos';

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('manage inventory') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['ingredient', 'user']);
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('moved_at', 'desc')
            ->columns([
                TextColumn::make('moved_at')
                    ->label('Data')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ingredient.name')
                    ->label('Ingrediente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::formatType($state))
                    ->color(fn (string $state): string => self::typeColor($state)),
                TextColumn::make('quantity_g')
                    ->label('Quantidade (g)')
                    ->formatStateUsing(function (float $state): string {
                        $prefix = $state >= 0 ? '+' : '';

                        return $prefix.self::formatQuantity($state);
                    })
                    ->color(fn (float $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('Custo')
                    ->formatStateUsing(fn (?float $state): string => $state === null ? '-' : self::currency($state))
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(60)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        InventoryMovement::TYPE_PURCHASE => 'Compra',
                        InventoryMovement::TYPE_ADJUSTMENT => 'Ajuste',
                        InventoryMovement::TYPE_MANUAL_OUT => 'Saída manual',
                        InventoryMovement::TYPE_PRODUCTION => 'Produção',
                    ]),
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryMovements::route('/'),
        ];
    }

    private static function formatQuantity(float $value): string
    {
        return number_format(abs((float) round($value)), 0, ',', '.');
    }

    private static function currency(float $value): string
    {
        return number_format($value, 2, ',', '.').' MT';
    }

    private static function formatType(string $type): string
    {
        return match ($type) {
            InventoryMovement::TYPE_PURCHASE => 'Compra',
            InventoryMovement::TYPE_ADJUSTMENT => 'Ajuste',
            InventoryMovement::TYPE_MANUAL_OUT => 'Saída manual',
            InventoryMovement::TYPE_PRODUCTION => 'Produção',
            default => $type,
        };
    }

    private static function typeColor(string $type): string
    {
        return match ($type) {
            InventoryMovement::TYPE_PURCHASE => 'success',
            InventoryMovement::TYPE_ADJUSTMENT => 'warning',
            InventoryMovement::TYPE_MANUAL_OUT => 'danger',
            InventoryMovement::TYPE_PRODUCTION => 'info',
            default => 'gray',
        };
    }
}
