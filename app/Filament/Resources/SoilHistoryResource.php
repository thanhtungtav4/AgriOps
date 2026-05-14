<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoilHistoryResource\Pages;
use App\Models\SoilHistory;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SoilHistoryResource extends Resource
{
    protected static ?string $model = SoilHistory::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map';

    protected static ?string $modelLabel = 'lịch sử đất';

    protected static ?string $pluralModelLabel = 'lịch sử đất';

    protected static ?string $navigationLabel = 'Lịch sử đất';

    protected static string | \UnitEnum | null $navigationGroup = 'Sản xuất';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('plot_id')
                    ->label('Lô đất')
                    ->relationship('plot', 'name'),
                Forms\Components\Select::make('bed_id')
                    ->label('Luống')
                    ->relationship('bed', 'code'),
                Forms\Components\Select::make('record_type')
                    ->label('Loại ghi nhận')
                    ->options(array (
  'test_result' => 'Kết quả test',
  'amendment' => 'Cải tạo',
  'reading' => 'Đọc nhanh',
)),
                Forms\Components\DatePicker::make('recorded_at')
                    ->label('Ngày ghi nhận'),
                Forms\Components\TextInput::make('ph')
                    ->label('pH')
                    ->numeric(),
                Forms\Components\TextInput::make('nitrogen')
                    ->label('Nitrogen')
                    ->numeric(),
                Forms\Components\TextInput::make('phosphorus')
                    ->label('Phosphorus')
                    ->numeric(),
                Forms\Components\TextInput::make('potassium')
                    ->label('Potassium')
                    ->numeric(),
                Forms\Components\TextInput::make('organic_matter')
                    ->label('Hữu cơ (%)')
                    ->numeric(),
                Forms\Components\TextInput::make('moisture')
                    ->label('Ẩm độ (%)')
                    ->numeric(),
                Forms\Components\TextInput::make('amendment_applied')
                    ->label('Cải tạo đã dùng')
                    ->maxLength(255),
                Forms\Components\TextInput::make('amendment_quantity')
                    ->label('Lượng cải tạo')
                    ->numeric(),
                Forms\Components\TextInput::make('amendment_unit')
                    ->label('Đơn vị')
                    ->maxLength(255),
                Forms\Components\Select::make('source')
                    ->label('Nguồn')
                    ->options(array (
  'lab' => 'Phòng lab',
  'manual' => 'Thủ công',
)),
                Forms\Components\TextInput::make('lab_name')
                    ->label('Tên lab')
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
                Tables\Columns\TextColumn::make('plot.name')->label('Lô đất')->searchable(),
                Tables\Columns\TextColumn::make('bed.code')->label('Luống')->searchable(),
                Tables\Columns\TextColumn::make('record_type')->label('Loại'),
                Tables\Columns\TextColumn::make('recorded_at')->label('Ngày')->dateTime(),
                Tables\Columns\TextColumn::make('ph')->label('pH'),
                Tables\Columns\TextColumn::make('source')->label('Nguồn'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('record_type')->label('Trạng thái')->options(array (
  'test_result' => 'Kết quả test',
  'amendment' => 'Cải tạo',
  'reading' => 'Đọc nhanh',
)),
                Tables\Filters\SelectFilter::make('source')->label('Trạng thái')->options(array (
  'lab' => 'Phòng lab',
  'manual' => 'Thủ công',
)),
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
            'index' => Pages\ListSoilHistories::route('/'),
            'create' => Pages\CreateSoilHistory::route('/create'),
            'edit' => Pages\EditSoilHistory::route('/{record}/edit'),
        ];
    }
}
