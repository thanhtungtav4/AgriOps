<?php

namespace App\Filament\Resources\PlantingBatchResource\Pages;

use App\Filament\Resources\PlantingBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlantingBatches extends ListRecords
{
    protected static string $resource = PlantingBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tạo lô trồng mới'),
        ];
    }
}