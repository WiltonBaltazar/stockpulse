<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = UserResource::class;
}
