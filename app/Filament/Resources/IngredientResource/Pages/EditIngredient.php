<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\IngredientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIngredient extends EditRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = IngredientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
