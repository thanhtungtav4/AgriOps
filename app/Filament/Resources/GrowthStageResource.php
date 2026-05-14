<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GrowthStageResource\Pages;
use App\Models\GrowthStage;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GrowthStageResource extends Resource
{
    protected static ?string $model = GrowthStage::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Dữ liệu nền';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $modelLabel = 'giai đoạn sinh trưởng';

    protected static ?string $pluralModelLabel = 'giai đoạn sinh trưởng';

    protected static ?string $navigationLabel = 'Giai đoạn sinh trưởng';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('crop_id')
                    ->label('Cây trồng')
                    ->relationship('crop', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Tên giai đoạn')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label('Mã giai đoạn')
                    ->maxLength(50),
                Forms\Components\TextInput::make('order')
                    ->label('Thứ tự')
                    ->integer()
                    ->default(0),
                Forms\Components\TextInput::make('duration_days')
                    ->label('Thời lượng (ngày)')
                    ->integer()
                    ->default(0),
                Forms\Components\Textarea::make('description')
                    ->label('Mô tả')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('crop.name')->label('Cây trồng')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Tên giai đoạn')->searchable(),
                Tables\Columns\TextColumn::make('code')->label('Mã'),
                Tables\Columns\TextColumn::make('order')->label('Thứ tự')->sortable(),
                Tables\Columns\TextColumn::make('duration_days')->label('Số ngày'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('crop_id')
                    ->label('Cây trồng')
                    ->relationship('crop', 'name'),
            ])
            ->actions([
                Actions\EditAction::make()->label('Sửa'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()->label('Xoá đã chọn'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrowthStages::route('/'),
            'create' => Pages\CreateGrowthStage::route('/create'),
            'edit' => Pages\EditGrowthStage::route('/{record}/edit'),
        ];
    }
}
