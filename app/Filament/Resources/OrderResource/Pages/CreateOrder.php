<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\OrderResource;
use App\Services\OrderService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateOrder extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = OrderResource::class;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $itemsPayload = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        if (! $user) {
            return $data;
        }

        $prepared = app(OrderService::class)->prepareData($user, $data);
        $this->itemsPayload = $prepared['items'];

        return $prepared['attributes'];
    }

    protected function afterCreate(): void
    {
        $service = app(OrderService::class);
        $service->syncItems($this->record, $this->itemsPayload);
        $service->syncSalesAndFinancials($this->record);
    }
}
