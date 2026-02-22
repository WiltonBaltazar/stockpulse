<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = RoleResource::class;
}
