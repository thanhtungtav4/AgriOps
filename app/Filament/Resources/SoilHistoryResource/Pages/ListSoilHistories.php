<?php

namespace App\Filament\Resources\SoilHistoryResource\Pages;

use App\Filament\Resources\SoilHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSoilHistories extends ListRecords
{
    protected static string $resource = SoilHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tạo lịch sử đất mới'),
        ];
    }
}