<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlotResource\Pages;
use App\Models\Plot;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlotResource extends Resource
{
    protected static ?string $model = Plot::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-map';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('farm_id')
                    ->relationship('farm', 'name')
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('area_m2')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('soil_type')
                    ->maxLength(100),
                Forms\Components\TextInput::make('water_source')
                    ->maxLength(100),
                Forms\Components\Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'preparing' => 'Preparing',
                        'planting' => 'Planting',
                        'harvesting' => 'Harvesting',
                        'rest' => 'Rest',
                        'restoring' => 'Restoring',
                        'suspended' => 'Suspended',
                    ])
                    ->default('available'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('farm.name')->searchable(),
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('area_m2'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('soil_type'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('farm_id')
                    ->relationship('farm', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'preparing' => 'Preparing',
                        'planting' => 'Planting',
                        'harvesting' => 'Harvesting',
                        'rest' => 'Rest',
                        'restoring' => 'Restoring',
                        'suspended' => 'Suspended',
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
            'index' => Pages\ListPlots::route('/'),
            'create' => Pages\CreatePlot::route('/create'),
            'edit' => Pages\EditPlot::route('/{record}/edit'),
        ];
    }
}
