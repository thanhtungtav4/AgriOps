<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackingLotResource\Pages;
use App\Models\PackingLot;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PackingLotResource extends Resource
{
    protected static ?string $model = PackingLot::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $modelLabel = 'lô đóng gói';

    protected static ?string $pluralModelLabel = 'lô đóng gói';

    protected static ?string $navigationLabel = 'Lô đóng gói';

    protected static string | \UnitEnum | null $navigationGroup = 'Sản xuất';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name'),
                Forms\Components\Select::make('created_by_user_id')
                    ->label('Người tạo')
                    ->relationship('createdBy', 'name'),
                Forms\Components\TextInput::make('code')
                    ->label('Mã lô')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('packed_at')
                    ->label('Thời điểm đóng gói'),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options(array (
  'draft' => 'Nháp',
  'packed' => 'Đã đóng gói',
  'published' => 'Công khai',
  'cancelled' => 'Huỷ',
)),
                Forms\Components\TextInput::make('total_input_quantity')
                    ->label('SL đầu vào')
                    ->numeric(),
                Forms\Components\TextInput::make('total_output_quantity')
                    ->label('SL đầu ra')
                    ->numeric(),
                Forms\Components\TextInput::make('unit')
                    ->label('Đơn vị')
                    ->maxLength(255),
                Forms\Components\TextInput::make('qr_code')
                    ->label('QR code')
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('packed_at')->label('Đóng gói')->dateTime(),
                Tables\Columns\TextColumn::make('total_input_quantity')->label('Đầu vào'),
                Tables\Columns\TextColumn::make('total_output_quantity')->label('Đầu ra'),
                Tables\Columns\TextColumn::make('unit')->label('Đơn vị'),
                Tables\Columns\TextColumn::make('qr_code')->label('QR')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options(array (
  'draft' => 'Nháp',
  'packed' => 'Đã đóng gói',
  'published' => 'Công khai',
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
            'index' => Pages\ListPackingLots::route('/'),
            'create' => Pages\CreatePackingLot::route('/create'),
            'edit' => Pages\EditPackingLot::route('/{record}/edit'),
        ];
    }
}
