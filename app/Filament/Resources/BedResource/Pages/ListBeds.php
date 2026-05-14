<?php

namespace App\Filament\Resources\BedResource\Pages;

use App\Filament\Resources\BedResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBeds extends ListRecords
{
    public static string $resource = BedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tạo luống'),
        ];
    }
}
