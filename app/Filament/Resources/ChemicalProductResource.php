<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChemicalProductResource\Pages;
use App\Models\ChemicalProduct;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ChemicalProductResource extends Resource
{
    protected static ?string $model = ChemicalProduct::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $modelLabel = 'sản phẩm vật tư';

    protected static ?string $pluralModelLabel = 'sản phẩm vật tư';

    protected static ?string $navigationLabel = 'Vật tư/thuốc';

    protected static string | \UnitEnum | null $navigationGroup = 'An toàn & Chất lượng';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name'),
                Forms\Components\TextInput::make('name')
                    ->label('Tên sản phẩm')
                    ->maxLength(255),
                Forms\Components\TextInput::make('active_ingredient')
                    ->label('Hoạt chất')
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Loại')
                    ->options(array (
  'pesticide' => 'Thuốc BVTV',
  'herbicide' => 'Diệt cỏ',
  'fungicide' => 'Trừ nấm',
  'fertilizer' => 'Phân bón',
  'biological' => 'Sinh học',
  'other' => 'Khác',
)),
                Forms\Components\TextInput::make('formulation')
                    ->label('Dạng')
                    ->maxLength(255),
                Forms\Components\TextInput::make('registration_number')
                    ->label('Số đăng ký')
                    ->maxLength(255),
                Forms\Components\TextInput::make('manufacturer')
                    ->label('Nhà SX')
                    ->maxLength(255),
                Forms\Components\TextInput::make('supplier')
                    ->label('Nhà cung cấp')
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit')
                    ->label('Đơn vị')
                    ->maxLength(255),
                Forms\Components\TextInput::make('stock_quantity')
                    ->label('Tồn kho')
                    ->numeric(),
                Forms\Components\TextInput::make('min_stock_level')
                    ->label('Tối thiểu')
                    ->numeric(),
                Forms\Components\TextInput::make('price_per_unit')
                    ->label('Giá/đơn vị')
                    ->numeric(),
                Forms\Components\DatePicker::make('expiry_date')
                    ->label('Hạn dùng'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Đang dùng'),
                Forms\Components\Textarea::make('usage_instructions')
                    ->label('Hướng dẫn dùng')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('safety_instructions')
                    ->label('An toàn')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('storage_conditions')
                    ->label('Bảo quản')
                    ->maxLength(65535)
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('name')->label('Tên')->searchable(),
                Tables\Columns\TextColumn::make('farm.name')->label('Nông trại')->searchable(),
                Tables\Columns\TextColumn::make('active_ingredient')->label('Hoạt chất'),
                Tables\Columns\TextColumn::make('type')->label('Loại'),
                Tables\Columns\TextColumn::make('stock_quantity')->label('Tồn kho'),
                Tables\Columns\TextColumn::make('min_stock_level')->label('Tối thiểu'),
                Tables\Columns\TextColumn::make('expiry_date')->label('Hạn dùng')->dateTime(),
                Tables\Columns\TextColumn::make('is_active')->label('Hoạt động'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label('Trạng thái')->options(array (
  'pesticide' => 'Thuốc BVTV',
  'herbicide' => 'Diệt cỏ',
  'fungicide' => 'Trừ nấm',
  'fertilizer' => 'Phân bón',
  'biological' => 'Sinh học',
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
            'index' => Pages\ListChemicalProducts::route('/'),
            'create' => Pages\CreateChemicalProduct::route('/create'),
            'edit' => Pages\EditChemicalProduct::route('/{record}/edit'),
        ];
    }
}
