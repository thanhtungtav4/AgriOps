<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplyContractResource\Pages;
use App\Models\SupplyContract;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SupplyContractResource extends Resource
{
    protected static ?string $model = SupplyContract::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $modelLabel = 'hợp đồng cung ứng';

    protected static ?string $pluralModelLabel = 'hợp đồng cung ứng';

    protected static ?string $navigationLabel = 'Hợp đồng cung ứng';

    protected static string | \UnitEnum | null $navigationGroup = 'Kế hoạch & Kinh doanh';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name'),
                Forms\Components\TextInput::make('customer_name')
                    ->label('Khách hàng')
                    ->maxLength(255),
                Forms\Components\Select::make('customer_type')
                    ->label('Loại khách hàng')
                    ->options(array (
  'restaurant' => 'Nhà hàng',
  'wholesale' => 'Bán sỉ',
  'retail' => 'Bán lẻ',
  'export' => 'Xuất khẩu',
  'other' => 'Khác',
)),
                Forms\Components\Select::make('crop_id')
                    ->label('Cây trồng')
                    ->relationship('crop', 'name')
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Số lượng')
                    ->numeric(),
                Forms\Components\Select::make('unit')
                    ->label('Đơn vị')
                    ->options(array (
  'kg' => 'kg',
  'trái' => 'trái',
  'bó' => 'bó',
  'thùng' => 'thùng',
)),
                Forms\Components\Select::make('frequency')
                    ->label('Tần suất')
                    ->options(array (
  'once' => 'Một lần',
  'daily' => 'Hằng ngày',
  'weekly' => 'Hằng tuần',
  'monthly' => 'Hằng tháng',
  'seasonal' => 'Theo mùa',
)),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Ngày bắt đầu'),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Ngày kết thúc'),
                Forms\Components\Select::make('product_standard_id')
                    ->label('Chuẩn sản phẩm')
                    ->relationship('productStandard', 'name'),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options(array (
  'active' => 'Hiệu lực',
  'completed' => 'Hoàn thành',
  'cancelled' => 'Đã huỷ',
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
                Tables\Columns\TextColumn::make('customer_name')->label('Khách hàng')->searchable(),
                Tables\Columns\TextColumn::make('farm.name')->label('Nông trại')->searchable(),
                Tables\Columns\TextColumn::make('crop.name')->label('Cây trồng')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->label('Số lượng'),
                Tables\Columns\TextColumn::make('unit')->label('Đơn vị'),
                Tables\Columns\TextColumn::make('frequency')->label('Tần suất'),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái'),
                Tables\Columns\TextColumn::make('start_date')->label('Bắt đầu')->dateTime(),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options(array (
  'active' => 'Hiệu lực',
  'completed' => 'Hoàn thành',
  'cancelled' => 'Đã huỷ',
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
            'index' => Pages\ListSupplyContracts::route('/'),
            'create' => Pages\CreateSupplyContract::route('/create'),
            'edit' => Pages\EditSupplyContract::route('/{record}/edit'),
        ];
    }
}
