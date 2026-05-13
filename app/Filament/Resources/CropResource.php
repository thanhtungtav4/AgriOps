<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CropResource\Pages;
use App\Models\Crop;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CropResource extends Resource
{
    protected static ?string $model = Crop::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-globe-asia-australia';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('group')
                    ->options([
                        'leafy' => 'Leafy',
                        'fruit' => 'Fruit',
                        'root' => 'Root',
                        'fruit_tree' => 'Fruit Tree',
                    ])
                    ->default('leafy'),
                Forms\Components\Select::make('sale_unit')
                    ->options([
                        'kg' => 'kg',
                        'trái' => 'Trái',
                        'bó' => 'Bó',
                        'thùng' => 'Thùng',
                    ])
                    ->default('kg'),
                Forms\Components\Select::make('production_unit')
                    ->options([
                        'cây' => 'Cây',
                        'm2' => 'm²',
                        'luống' => 'Luống',
                    ])
                    ->default('cây'),
                Forms\Components\Checkbox::make('can_harvest_multiple'),
                Forms\Components\Checkbox::make('has_multiple_cycles'),
                Forms\Components\TextInput::make('avg_growth_days')
                    ->integer()
                    ->default(0),
                Forms\Components\TextInput::make('harvest_exploitation_days')
                    ->integer()
                    ->default(0),
                Forms\Components\TextInput::make('rest_days')
                    ->integer()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('group'),
                Tables\Columns\TextColumn::make('sale_unit'),
                Tables\Columns\TextColumn::make('production_unit'),
                Tables\Columns\IconColumn::make('can_harvest_multiple')
                    ->boolean(),
                Tables\Columns\IconColumn::make('has_multiple_cycles')
                    ->boolean(),
                Tables\Columns\TextColumn::make('avg_growth_days'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'leafy' => 'Leafy',
                        'fruit' => 'Fruit',
                        'root' => 'Root',
                        'fruit_tree' => 'Fruit Tree',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrops::route('/'),
            'create' => Pages\CreateCrop::route('/create'),
            'edit' => Pages\EditCrop::route('/{record}/edit'),
        ];
    }
}
