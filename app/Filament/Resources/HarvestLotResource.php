<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HarvestLotResource\Pages;
use App\Models\HarvestLot;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HarvestLotResource extends Resource
{
    protected static ?string $model = HarvestLot::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $modelLabel = 'lô thu hoạch';

    protected static ?string $pluralModelLabel = 'lô thu hoạch';

    protected static ?string $navigationLabel = 'Lô thu hoạch';

    protected static string | \UnitEnum | null $navigationGroup = 'Sản xuất';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name')
                    ->required(),
                Forms\Components\Select::make('planting_batch_id')
                    ->label('Lứa trồng')
                    ->relationship('plantingBatch', 'code')
                    ->required(),
                Forms\Components\Select::make('pre_harvest_inspection_id')
                    ->label('Nghiệm thu')
                    ->relationship('preHarvestInspection', 'id'),
                Forms\Components\Select::make('work_task_id')
                    ->label('Công việc')
                    ->relationship('workTask', 'title'),
                Forms\Components\Select::make('plot_id')
                    ->label('Lô đất')
                    ->relationship('plot', 'name'),
                Forms\Components\Select::make('bed_id')
                    ->label('Luống')
                    ->relationship('bed', 'code'),
                Forms\Components\Select::make('harvested_by_user_id')
                    ->label('Người thu')
                    ->relationship('harvestedBy', 'name'),
                Forms\Components\TextInput::make('code')
                    ->label('Mã lô')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('harvest_date')
                    ->label('Ngày thu'),
                Forms\Components\TextInput::make('raw_quantity')
                    ->label('SL thô')
                    ->numeric(),
                Forms\Components\TextInput::make('unit')
                    ->label('Đơn vị')
                    ->maxLength(255),
                Forms\Components\TextInput::make('grade_a_quantity')
                    ->label('Loại A')
                    ->numeric(),
                Forms\Components\TextInput::make('grade_b_quantity')
                    ->label('Loại B')
                    ->numeric(),
                Forms\Components\TextInput::make('grade_c_quantity')
                    ->label('Loại C')
                    ->numeric(),
                Forms\Components\TextInput::make('reject_quantity')
                    ->label('Loại bỏ')
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options(array (
  'available' => 'Sẵn sàng',
  'reserved' => 'Đã giữ',
  'packed' => 'Đã đóng gói',
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
                Tables\Columns\TextColumn::make('plantingBatch.code')->label('Lứa')->searchable(),
                Tables\Columns\TextColumn::make('harvest_date')->label('Ngày thu')->dateTime(),
                Tables\Columns\TextColumn::make('raw_quantity')->label('SL thô'),
                Tables\Columns\TextColumn::make('unit')->label('Đơn vị'),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options(array (
  'available' => 'Sẵn sàng',
  'reserved' => 'Đã giữ',
  'packed' => 'Đã đóng gói',
  'cancelled' => 'Huỷ',
)),
                Tables\Filters\SelectFilter::make('farm_id')->relationship('farm', 'name'),
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
            'index' => Pages\ListHarvestLots::route('/'),
            'create' => Pages\CreateHarvestLot::route('/create'),
            'edit' => Pages\EditHarvestLot::route('/{record}/edit'),
        ];
    }
}
