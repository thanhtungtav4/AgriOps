<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnRecordResource\Pages;
use App\Models\ReturnRecord;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ReturnRecordResource extends Resource
{
    protected static ?string $model = ReturnRecord::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $modelLabel = 'hàng trả về';

    protected static ?string $pluralModelLabel = 'hàng trả về';

    protected static ?string $navigationLabel = 'Hàng trả về';

    protected static string | \UnitEnum | null $navigationGroup = 'Kế hoạch & Kinh doanh';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name')
                    ->required(),
                Forms\Components\Select::make('delivery_note_id')
                    ->label('Phiếu giao')
                    ->relationship('deliveryNote', 'code'),
                Forms\Components\Select::make('packing_lot_id')
                    ->label('Lô đóng gói')
                    ->relationship('packingLot', 'code'),
                Forms\Components\Select::make('recorded_by_user_id')
                    ->label('Người ghi nhận')
                    ->relationship('recordedBy', 'name'),
                Forms\Components\DateTimePicker::make('returned_at')
                    ->label('Thời điểm trả'),
                Forms\Components\TextInput::make('quantity')
                    ->label('Số lượng')
                    ->numeric(),
                Forms\Components\TextInput::make('unit')
                    ->label('Đơn vị')
                    ->maxLength(255),
                Forms\Components\Select::make('reason')
                    ->label('Lý do')
                    ->options(array (
  'bruised_wilted' => 'Dập/héo',
  'wrong_size' => 'Sai size',
  'wrong_weight' => 'Sai cân',
  'bad_color' => 'Sai màu',
  'not_uniform' => 'Không đồng đều',
  'pest_disease' => 'Sâu bệnh',
  'packaging_error' => 'Lỗi đóng gói',
  'late_delivery' => 'Giao trễ',
  'short_quantity' => 'Thiếu hàng',
  'other' => 'Khác',
)),
                Forms\Components\Select::make('handling_action')
                    ->label('Xử lý')
                    ->options(array (
  'discard' => 'Huỷ',
  'sell_side_channel' => 'Bán kênh phụ',
  'reprocess' => 'Sơ chế lại',
  'record_loss' => 'Ghi hao hụt',
  'compensate_next_delivery' => 'Bù đơn sau',
)),
                Forms\Components\TextInput::make('revenue_deduction')
                    ->label('Trừ doanh thu')
                    ->numeric(),
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
                Tables\Columns\TextColumn::make('farm.name')->label('Nông trại')->searchable(),
                Tables\Columns\TextColumn::make('deliveryNote.code')->label('Phiếu giao')->searchable(),
                Tables\Columns\TextColumn::make('packingLot.code')->label('Lô đóng gói')->searchable(),
                Tables\Columns\TextColumn::make('returned_at')->label('Ngày trả')->dateTime(),
                Tables\Columns\TextColumn::make('quantity')->label('Số lượng'),
                Tables\Columns\TextColumn::make('reason')->label('Lý do'),
                Tables\Columns\TextColumn::make('handling_action')->label('Xử lý'),
                Tables\Columns\TextColumn::make('revenue_deduction')->label('Trừ DT'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('reason')->label('Trạng thái')->options(array (
  'bruised_wilted' => 'Dập/héo',
  'wrong_size' => 'Sai size',
  'wrong_weight' => 'Sai cân',
  'bad_color' => 'Sai màu',
  'not_uniform' => 'Không đồng đều',
  'pest_disease' => 'Sâu bệnh',
  'packaging_error' => 'Lỗi đóng gói',
  'late_delivery' => 'Giao trễ',
  'short_quantity' => 'Thiếu hàng',
  'other' => 'Khác',
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
            'index' => Pages\ListReturnRecords::route('/'),
            'create' => Pages\CreateReturnRecord::route('/create'),
            'edit' => Pages\EditReturnRecord::route('/{record}/edit'),
        ];
    }
}
