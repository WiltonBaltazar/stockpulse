<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\SaleResource;
use App\Services\SaleService;
use Filament\Resources\Pages\CreateRecord;
use Throwable;
use Illuminate\Support\Facades\Auth;

class CreateSale extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = SaleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        if (! $user) {
            return $data;
        }

        return app(SaleService::class)->prepareData($user, $data);
    }

    protected function afterCreate(): void
    {
        try {
            app(SaleService::class)->syncOperationalAndFinancials($this->record);
        } catch (Throwable $exception) {
            $this->record->delete();

            throw $exception;
        }
    }
}
