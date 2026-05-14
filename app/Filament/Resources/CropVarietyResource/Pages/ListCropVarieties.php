<?php

namespace App\Filament\Resources\CropVarietyResource\Pages;

use App\Filament\Resources\CropVarietyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCropVarieties extends ListRecords
{
    public static string $resource = CropVarietyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tạo giống cây'),
        ];
    }
}
