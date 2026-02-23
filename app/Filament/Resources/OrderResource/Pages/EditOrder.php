<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\OrderResource;
use App\Services\OrderService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditOrder extends EditRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = OrderResource::class;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $itemsPayload = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->record
            ->items()
            ->get(['recipe_id', 'item_name', 'quantity', 'unit_price'])
            ->map(fn ($item): array => [
                'recipe_id' => $item->recipe_id,
                'item_name' => $item->item_name,
                'quantity' => (int) $item->quantity,
                'unit_price' => round((float) $item->unit_price, 2),
            ])
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();
        if (! $user) {
            return $data;
        }

        $data['reference'] = $this->record->reference;

        $owner = $this->record->user ?? $user;
        $prepared = app(OrderService::class)->prepareData($user, $data, $owner);
        $this->itemsPayload = $prepared['items'];

        return $prepared['attributes'];
    }

    protected function afterSave(): void
    {
        $service = app(OrderService::class);
        $service->syncItems($this->record, $this->itemsPayload);
        $service->syncSalesAndFinancials($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
