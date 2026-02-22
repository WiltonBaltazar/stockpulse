<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\PermissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = PermissionResource::class;
}
