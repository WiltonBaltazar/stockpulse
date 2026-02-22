<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
