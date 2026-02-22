<?php

namespace App\Filament\Resources\FinancialTransactionResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\FinancialTransactionResource;
use App\Models\FinancialTransaction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinancialTransaction extends EditRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = FinancialTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($this->record->reference ?? null)) {
            $seed = trim((string) ($data['reason'] ?? $data['counterparty'] ?? $data['source'] ?? ''));
            $data['reference'] = FinancialTransaction::generateReference((int) $this->record->user_id, $seed);
        }

        return $data;
    }
}
