<?php

namespace App\Filament\Resources\ProductStandardResource\Pages;

use App\Filament\Resources\ProductStandardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductStandards extends ListRecords
{
    public static string $resource = ProductStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
