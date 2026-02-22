<?php

namespace App\Filament\Resources\ProductionBatchResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\ProductionBatchResource;
use App\Models\ProductionBatch;
use App\Services\ProductionBatchService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateProductionBatch extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = ProductionBatchResource::class;

    protected ?array $cachedPreview = null;

    protected ?string $cachedPreviewKey = null;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->disabled(fn (): bool => $this->hasStockShortage())
            ->tooltip(fn (): ?string => $this->getStockShortageTooltip());
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->disabled(fn (): bool => $this->hasStockShortage())
            ->tooltip(fn (): ?string => $this->getStockShortageTooltip());
    }

    protected function handleRecordCreation(array $data): Model
    {
        $user = Auth::user();
        if (! $user) {
            throw ValidationException::withMessages([
                'recipe_id' => 'Utilizador não autenticado.',
            ]);
        }

        /** @var ProductionBatch $batch */
        try {
            $batch = app(ProductionBatchService::class)->createBatch(
                user: $user,
                recipeId: (int) ($data['recipe_id'] ?? 0),
                producedUnits: (int) ($data['produced_units'] ?? 0),
                producedAt: $data['produced_at'] ?? now(),
                notes: $data['notes'] ?? null,
            );
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Não foi possível registrar o lote')
                ->body(collect($exception->errors())->flatten()->implode(' '))
                ->danger()
                ->send();

            throw $exception;
        }

        return $batch;
    }

    private function hasStockShortage(): bool
    {
        $shortages = $this->getPreview()['shortages'] ?? [];

        return $shortages !== [];
    }

    private function getStockShortageTooltip(): ?string
    {
        $shortages = $this->getPreview()['shortages'] ?? [];
        if ($shortages === []) {
            return null;
        }

        $details = collect($shortages)
            ->map(fn (array $item): string => sprintf(
                '%s (%s %s)',
                $item['ingredient_name'],
                number_format((float) round((float) $item['shortage_g']), 0, ',', '.'),
                (string) ($item['shortage_unit'] ?? 'g')
            ))
            ->implode(', ');

        return 'Estoque insuficiente: '.$details;
    }

    /**
     * @return array<string, mixed>
     */
    private function getPreview(): array
    {
        $recipeId = (int) ($this->data['recipe_id'] ?? 0);
        $producedUnits = (int) ($this->data['produced_units'] ?? 0);
        $key = $recipeId.'|'.$producedUnits;

        if ($this->cachedPreviewKey === $key && $this->cachedPreview !== null) {
            return $this->cachedPreview;
        }

        $this->cachedPreviewKey = $key;

        if ($recipeId <= 0 || $producedUnits <= 0) {
            return $this->cachedPreview = [];
        }

        $recipe = ProductionBatchResource::resolveRecipe($recipeId);
        if (! $recipe) {
            return $this->cachedPreview = [];
        }

        return $this->cachedPreview = app(ProductionBatchService::class)->previewForRecipe($recipe, $producedUnits);
    }
}
