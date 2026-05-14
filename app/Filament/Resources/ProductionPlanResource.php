<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionPlanResource\Pages;
use App\Models\ProductionPlan;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductionPlanResource extends Resource
{
    protected static ?string $model = ProductionPlan::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $modelLabel = 'kế hoạch sản xuất';

    protected static ?string $pluralModelLabel = 'kế hoạch sản xuất';

    protected static ?string $navigationLabel = 'Kế hoạch sản xuất';

    protected static string | \UnitEnum | null $navigationGroup = 'Kế hoạch & Kinh doanh';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('supply_contract_id')
                    ->label('Hợp đồng')
                    ->relationship('supplyContract', 'id'),
                Forms\Components\Select::make('supply_demand_id')
                    ->label('Nhu cầu')
                    ->relationship('supplyDemand', 'id'),
                Forms\Components\Select::make('crop_id')
                    ->label('Cây trồng')
                    ->relationship('crop', 'name')
                    ->required(),
                Forms\Components\Select::make('variety_id')
                    ->label('Giống')
                    ->relationship('variety', 'name'),
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name')
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
                Forms\Components\DatePicker::make('target_delivery_date')
                    ->label('Ngày giao mục tiêu'),
                Forms\Components\TextInput::make('estimated_cost')
                    ->label('Chi phí dự kiến')
                    ->numeric(),
                Forms\Components\TextInput::make('estimated_revenue')
                    ->label('Doanh thu dự kiến')
                    ->numeric(),
                Forms\Components\TextInput::make('estimated_margin')
                    ->label('Lợi nhuận dự kiến')
                    ->numeric(),
                Forms\Components\TextInput::make('margin_percent')
                    ->label('Biên lợi nhuận (%)')
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options(array (
  'draft' => 'Nháp',
  'planning' => 'Đang lập',
  'approved' => 'Đã duyệt',
  'in_progress' => 'Đang làm',
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
                Tables\Columns\TextColumn::make('farm.name')->label('Nông trại')->searchable(),
                Tables\Columns\TextColumn::make('crop.name')->label('Cây trồng')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->label('Số lượng'),
                Tables\Columns\TextColumn::make('unit')->label('Đơn vị'),
                Tables\Columns\TextColumn::make('target_delivery_date')->label('Ngày giao')->dateTime(),
                Tables\Columns\TextColumn::make('estimated_revenue')->label('Doanh thu'),
                Tables\Columns\TextColumn::make('margin_percent')->label('Biên %'),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options(array (
  'draft' => 'Nháp',
  'planning' => 'Đang lập',
  'approved' => 'Đã duyệt',
  'in_progress' => 'Đang làm',
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
            'index' => Pages\ListProductionPlans::route('/'),
            'create' => Pages\CreateProductionPlan::route('/create'),
            'edit' => Pages\EditProductionPlan::route('/{record}/edit'),
        ];
    }
}
