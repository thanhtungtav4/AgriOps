<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChemicalUsageResource\Pages;
use App\Models\ChemicalUsage;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ChemicalUsageResource extends Resource
{
    protected static ?string $model = ChemicalUsage::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $modelLabel = 'ghi nhận sử dụng thuốc';

    protected static ?string $pluralModelLabel = 'ghi nhận sử dụng thuốc';

    protected static ?string $navigationLabel = 'Sử dụng thuốc';

    protected static string | \UnitEnum | null $navigationGroup = 'An toàn & Chất lượng';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name')
                    ->required(),
                Forms\Components\Select::make('incident_id')
                    ->label('Sự cố')
                    ->relationship('incident', 'id'),
                Forms\Components\Select::make('planting_batch_id')
                    ->label('Lứa trồng')
                    ->relationship('plantingBatch', 'code'),
                Forms\Components\Select::make('work_task_id')
                    ->label('Công việc')
                    ->relationship('workTask', 'title'),
                Forms\Components\Select::make('plot_id')
                    ->label('Lô đất')
                    ->relationship('plot', 'name'),
                Forms\Components\Select::make('bed_id')
                    ->label('Luống')
                    ->relationship('bed', 'code'),
                Forms\Components\Select::make('applied_by_user_id')
                    ->label('Người dùng')
                    ->relationship('appliedByUser', 'name'),
                Forms\Components\TextInput::make('product_name')
                    ->label('Tên sản phẩm')
                    ->maxLength(255),
                Forms\Components\Select::make('product_type')
                    ->label('Loại')
                    ->options(array (
  'chemical' => 'Hoá học',
  'biological' => 'Sinh học',
)),
                Forms\Components\TextInput::make('active_ingredient')
                    ->label('Hoạt chất')
                    ->maxLength(255),
                Forms\Components\TextInput::make('dosage_value')
                    ->label('Liều lượng')
                    ->numeric(),
                Forms\Components\TextInput::make('dosage_unit')
                    ->label('Đơn vị liều')
                    ->maxLength(255),
                Forms\Components\TextInput::make('quantity_value')
                    ->label('Số lượng dùng')
                    ->numeric(),
                Forms\Components\TextInput::make('quantity_unit')
                    ->label('Đơn vị lượng')
                    ->maxLength(255),
                Forms\Components\TextInput::make('cost_amount')
                    ->label('Chi phí')
                    ->numeric(),
                Forms\Components\TextInput::make('isolation_days')
                    ->label('Số ngày cách ly')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('applied_at')
                    ->label('Thời điểm dùng'),
                Forms\Components\DateTimePicker::make('isolation_ends_at')
                    ->label('Hết cách ly'),
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
                Tables\Columns\TextColumn::make('product_name')->label('Sản phẩm')->searchable(),
                Tables\Columns\TextColumn::make('farm.name')->label('Nông trại')->searchable(),
                Tables\Columns\TextColumn::make('plantingBatch.code')->label('Lứa')->searchable(),
                Tables\Columns\TextColumn::make('product_type')->label('Loại'),
                Tables\Columns\TextColumn::make('quantity_value')->label('Lượng'),
                Tables\Columns\TextColumn::make('applied_at')->label('Ngày dùng')->dateTime(),
                Tables\Columns\TextColumn::make('isolation_ends_at')->label('Hết cách ly')->dateTime(),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_type')->label('Trạng thái')->options(array (
  'chemical' => 'Hoá học',
  'biological' => 'Sinh học',
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
            'index' => Pages\ListChemicalUsages::route('/'),
            'create' => Pages\CreateChemicalUsage::route('/create'),
            'edit' => Pages\EditChemicalUsage::route('/{record}/edit'),
        ];
    }
}
