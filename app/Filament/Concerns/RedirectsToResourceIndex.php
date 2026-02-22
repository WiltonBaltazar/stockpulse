<?php

namespace App\Filament\Concerns;

trait RedirectsToResourceIndex
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
