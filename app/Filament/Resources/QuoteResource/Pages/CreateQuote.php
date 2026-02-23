<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\QuoteResource;
use App\Services\QuoteService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateQuote extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = QuoteResource::class;

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

        $prepared = app(QuoteService::class)->prepareData($user, $data);
        $this->itemsPayload = $prepared['items'];

        return $prepared['attributes'];
    }

    protected function afterCreate(): void
    {
        app(QuoteService::class)->syncItems($this->record, $this->itemsPayload);
    }
}
