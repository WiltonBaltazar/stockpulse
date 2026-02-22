<?php

namespace App\Filament\Resources\FinancialTransactionResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\FinancialTransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateFinancialTransaction extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = FinancialTransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
