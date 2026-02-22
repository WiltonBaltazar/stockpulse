<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\RecipeResource;
use App\Models\Recipe;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecipe extends EditRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = RecipeResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Recipe $record */
        $record = $this->record;
        $hasProduction = $record->productionBatches()->exists();
        $hasSales = $record->sales()->exists();
        $hasHistory = $hasProduction || $hasSales;

        return [
            Actions\Action::make('archive')
                ->label('Arquivar')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->visible(fn (): bool => Recipe::supportsActiveState() && (bool) $this->record->is_active)
                ->requiresConfirmation()
                ->action(function (): void {
                    if (! Recipe::supportsActiveState()) {
                        return;
                    }

                    $this->record->update(['is_active' => false]);
                    $this->record->refresh();
                }),
            Actions\Action::make('restore')
                ->label('Reativar')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->visible(fn (): bool => Recipe::supportsActiveState() && ! $this->record->is_active)
                ->requiresConfirmation()
                ->action(function (): void {
                    if (! Recipe::supportsActiveState()) {
                        return;
                    }

                    $this->record->update(['is_active' => true]);
                    $this->record->refresh();
                }),
            Actions\DeleteAction::make()
                ->disabled($hasHistory)
                ->tooltip($hasHistory
                    ? 'Não é possível apagar esta receita porque ela possui histórico (produção ou vendas). Use Arquivar.'
                    : null),
        ];
    }
}
