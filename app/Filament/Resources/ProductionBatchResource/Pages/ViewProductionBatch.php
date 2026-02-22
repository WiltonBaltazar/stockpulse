<?php

namespace App\Filament\Resources\ProductionBatchResource\Pages;

use App\Filament\Resources\ProductionBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductionBatch extends ViewRecord
{
    protected static string $resource = ProductionBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Voltar')
                ->url(static::getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }
}
