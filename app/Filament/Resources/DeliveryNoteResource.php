<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryNoteResource\Pages;
use App\Models\DeliveryNote;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DeliveryNoteResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $modelLabel = 'phiếu giao hàng';

    protected static ?string $pluralModelLabel = 'phiếu giao hàng';

    protected static ?string $navigationLabel = 'Phiếu giao hàng';

    protected static string | \UnitEnum | null $navigationGroup = 'Kế hoạch & Kinh doanh';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name')
                    ->required(),
                Forms\Components\Select::make('packing_lot_id')
                    ->label('Lô đóng gói')
                    ->relationship('packingLot', 'code')
                    ->required(),
                Forms\Components\Select::make('supply_contract_id')
                    ->label('Hợp đồng')
                    ->relationship('supplyContract', 'id'),
                Forms\Components\Select::make('delivered_by_user_id')
                    ->label('Người giao')
                    ->relationship('deliveredBy', 'name'),
                Forms\Components\TextInput::make('code')
                    ->label('Mã phiếu')
                    ->maxLength(255),
                Forms\Components\TextInput::make('customer_name')
                    ->label('Khách hàng')
                    ->maxLength(255),
                Forms\Components\TextInput::make('customer_type')
                    ->label('Loại khách')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('delivered_at')
                    ->label('Thời điểm giao'),
                Forms\Components\TextInput::make('planned_quantity')
                    ->label('SL kế hoạch')
                    ->numeric(),
                Forms\Components\TextInput::make('accepted_quantity')
                    ->label('SL nhận')
                    ->numeric(),
                Forms\Components\TextInput::make('returned_quantity')
                    ->label('SL trả')
                    ->numeric(),
                Forms\Components\TextInput::make('net_quantity')
                    ->label('SL ròng')
                    ->numeric(),
                Forms\Components\TextInput::make('unit')
                    ->label('Đơn vị')
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Đơn giá')
                    ->numeric(),
                Forms\Components\TextInput::make('gross_revenue')
                    ->label('Doanh thu gộp')
                    ->numeric(),
                Forms\Components\TextInput::make('return_deduction')
                    ->label('Trừ trả hàng')
                    ->numeric(),
                Forms\Components\TextInput::make('side_channel_revenue')
                    ->label('Doanh thu phụ')
                    ->numeric(),
                Forms\Components\TextInput::make('net_revenue')
                    ->label('Doanh thu ròng')
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options(array (
  'delivered' => 'Đã giao',
  'accepted' => 'Đã nhận',
  'partially_returned' => 'Trả một phần',
  'returned' => 'Trả hết',
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
                Tables\Columns\TextColumn::make('packingLot.code')->label('Lô đóng gói')->searchable(),
                Tables\Columns\TextColumn::make('customer_name')->label('Khách hàng')->searchable(),
                Tables\Columns\TextColumn::make('delivered_at')->label('Ngày giao')->dateTime(),
                Tables\Columns\TextColumn::make('net_quantity')->label('SL ròng'),
                Tables\Columns\TextColumn::make('net_revenue')->label('Doanh thu ròng'),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options(array (
  'delivered' => 'Đã giao',
  'accepted' => 'Đã nhận',
  'partially_returned' => 'Trả một phần',
  'returned' => 'Trả hết',
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
            'index' => Pages\ListDeliveryNotes::route('/'),
            'create' => Pages\CreateDeliveryNote::route('/create'),
            'edit' => Pages\EditDeliveryNote::route('/{record}/edit'),
        ];
    }
}
