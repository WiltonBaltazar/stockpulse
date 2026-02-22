<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\IngredientResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateIngredient extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = IngredientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
