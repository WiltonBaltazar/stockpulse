<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\RecipeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateRecipe extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = RecipeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
