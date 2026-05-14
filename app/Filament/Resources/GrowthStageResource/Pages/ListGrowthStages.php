<?php

namespace App\Filament\Resources\GrowthStageResource\Pages;

use App\Filament\Resources\GrowthStageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGrowthStages extends ListRecords
{
    public static string $resource = GrowthStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tạo giai đoạn'),
        ];
    }
}
