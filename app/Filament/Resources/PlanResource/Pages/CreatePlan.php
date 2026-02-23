<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\PlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = PlanResource::class;
}
