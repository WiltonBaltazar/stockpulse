<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\SaleResource;
use App\Services\SaleService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Throwable;
use Illuminate\Support\Facades\Auth;

class EditSale extends EditRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = SaleResource::class;

    /**
     * @var array<string, mixed>
     */
    private array $originalSaleAttributes = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();
        if (! $user) {
            return $data;
        }

        $data['reference'] = $this->record->reference;

        $owner = $this->record->user ?? $user;

        return app(SaleService::class)->prepareData($user, $data, $owner);
    }

    protected function afterSave(): void
    {
        try {
            app(SaleService::class)->syncOperationalAndFinancials($this->record);
        } catch (Throwable $exception) {
            if ($this->originalSaleAttributes !== []) {
                $this->record->forceFill($this->originalSaleAttributes)->saveQuietly();
                $this->record->refresh();
            }

            throw $exception;
        }
    }

    protected function beforeSave(): void
    {
        $this->originalSaleAttributes = $this->record->getAttributes();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(fn () => app(SaleService::class)->removeOperationalAndFinancials($this->record)),
        ];
    }
}
