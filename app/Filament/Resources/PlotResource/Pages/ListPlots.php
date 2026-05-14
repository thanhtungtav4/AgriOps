<?php

namespace App\Filament\Resources\PlotResource\Pages;

use App\Filament\Resources\PlotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlots extends ListRecords
{
    public static string $resource = PlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tạo khu trồng'),
        ];
    }
}
