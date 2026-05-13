<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BedResource\Pages;
use App\Models\Bed;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BedResource extends Resource
{
    protected static ?string $model = Bed::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('plot_id')
                    ->relationship('plot', 'name')
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('length_m')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('width_m')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('area_m2')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('expected_plants')
                    ->integer()
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'preparing' => 'Preparing',
                        'planting' => 'Planting',
                        'growing' => 'Growing',
                        'harvesting' => 'Harvesting',
                        'rest' => 'Rest',
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
                Tables\Columns\TextColumn::make('plot.name')->searchable(),
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('length_m'),
                Tables\Columns\TextColumn::make('width_m'),
                Tables\Columns\TextColumn::make('area_m2'),
                Tables\Columns\TextColumn::make('expected_plants'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plot_id')
                    ->relationship('plot', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'preparing' => 'Preparing',
                        'planting' => 'Planting',
                        'growing' => 'Growing',
                        'harvesting' => 'Harvesting',
                        'rest' => 'Rest',
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
            'index' => Pages\ListBeds::route('/'),
            'create' => Pages\CreateBed::route('/create'),
            'edit' => Pages\EditBed::route('/{record}/edit'),
        ];
    }
}
