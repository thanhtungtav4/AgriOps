<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlantingBatchResource\Pages;
use App\Models\PlantingBatch;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PlantingBatchResource extends Resource
{
    protected static ?string $model = PlantingBatch::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $modelLabel = 'lứa trồng';

    protected static ?string $pluralModelLabel = 'lứa trồng';

    protected static ?string $navigationLabel = 'Lứa trồng';

    protected static string | \UnitEnum | null $navigationGroup = 'Sản xuất';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name')
                    ->required(),
                Forms\Components\Select::make('crop_id')
                    ->label('Cây trồng')
                    ->relationship('crop', 'name')
                    ->required(),
                Forms\Components\Select::make('variety_id')
                    ->label('Giống')
                    ->relationship('variety', 'name'),
                Forms\Components\Select::make('production_plan_id')
                    ->label('Kế hoạch')
                    ->relationship('productionPlan', 'id'),
                Forms\Components\TextInput::make('code')
                    ->label('Mã lứa')
                    ->maxLength(255),
                Forms\Components\TextInput::make('planned_quantity')
                    ->label('SL kế hoạch')
                    ->numeric(),
                Forms\Components\Select::make('planned_unit')
                    ->label('Đơn vị')
                    ->options(array (
  'kg' => 'kg',
  'trái' => 'trái',
  'bó' => 'bó',
  'thùng' => 'thùng',
)),
                Forms\Components\TextInput::make('planned_area_m2')
                    ->label('Diện tích KH (m2)')
                    ->numeric(),
                Forms\Components\DatePicker::make('planned_start_date')
                    ->label('Ngày bắt đầu KH'),
                Forms\Components\DatePicker::make('planned_harvest_date')
                    ->label('Ngày thu KH'),
                Forms\Components\TextInput::make('actual_quantity')
                    ->label('SL thực tế')
                    ->numeric(),
                Forms\Components\TextInput::make('actual_area_m2')
                    ->label('Diện tích TT')
                    ->numeric(),
                Forms\Components\DatePicker::make('actual_start_date')
                    ->label('Ngày bắt đầu TT'),
                Forms\Components\DatePicker::make('actual_harvest_date')
                    ->label('Ngày thu TT'),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options(array (
  'planned' => 'Đã lập',
  'soil_prep' => 'Làm đất',
  'planting' => 'Gieo trồng',
  'growing' => 'Sinh trưởng',
  'flowering' => 'Ra hoa',
  'fruiting' => 'Nuôi trái',
  'harvesting' => 'Thu hoạch',
  'completed' => 'Hoàn thành',
  'cancelled' => 'Huỷ',
)),
                Forms\Components\Textarea::make('notes')
                    ->label('Ghi chú')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('code')->label('Mã')->searchable(),
                Tables\Columns\TextColumn::make('farm.name')->label('Nông trại')->searchable(),
                Tables\Columns\TextColumn::make('crop.name')->label('Cây trồng')->searchable(),
                Tables\Columns\TextColumn::make('planned_quantity')->label('SL KH'),
                Tables\Columns\TextColumn::make('planned_area_m2')->label('Diện tích'),
                Tables\Columns\TextColumn::make('planned_start_date')->label('Bắt đầu')->dateTime(),
                Tables\Columns\TextColumn::make('planned_harvest_date')->label('Thu hoạch')->dateTime(),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options(array (
  'planned' => 'Đã lập',
  'soil_prep' => 'Làm đất',
  'planting' => 'Gieo trồng',
  'growing' => 'Sinh trưởng',
  'flowering' => 'Ra hoa',
  'fruiting' => 'Nuôi trái',
  'harvesting' => 'Thu hoạch',
  'completed' => 'Hoàn thành',
  'cancelled' => 'Huỷ',
)),
                Tables\Filters\SelectFilter::make('farm_id')->relationship('farm', 'name'),
                Tables\Filters\SelectFilter::make('crop_id')->relationship('crop', 'name'),
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
            'index' => Pages\ListPlantingBatches::route('/'),
            'create' => Pages\CreatePlantingBatch::route('/create'),
            'edit' => Pages\EditPlantingBatch::route('/{record}/edit'),
        ];
    }
}
