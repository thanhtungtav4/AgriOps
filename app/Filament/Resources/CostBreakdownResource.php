<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CostBreakdownResource\Pages;
use App\Models\CostBreakdown;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CostBreakdownResource extends Resource
{
    protected static ?string $model = CostBreakdown::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $modelLabel = 'phân tích chi phí';

    protected static ?string $pluralModelLabel = 'phân tích chi phí';

    protected static ?string $navigationLabel = 'Phân tích chi phí';

    protected static string | \UnitEnum | null $navigationGroup = 'Tài chính';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name'),
                Forms\Components\Select::make('breakdown_type')
                    ->label('Loại')
                    ->options(array (
  'farm' => 'Farm',
  'production_plan' => 'Kế hoạch',
  'planting_batch' => 'Lứa trồng',
  'seasonal' => 'Mùa vụ',
)),
                Forms\Components\Select::make('production_plan_id')
                    ->label('Kế hoạch')
                    ->relationship('productionPlan', 'id'),
                Forms\Components\Select::make('planting_batch_id')
                    ->label('Lứa trồng')
                    ->relationship('plantingBatch', 'code'),
                Forms\Components\DatePicker::make('period_start')
                    ->label('Từ ngày'),
                Forms\Components\DatePicker::make('period_end')
                    ->label('Đến ngày'),
                Forms\Components\TextInput::make('period_label')
                    ->label('Nhãn kỳ')
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_seed_cost')
                    ->label('Giống')
                    ->numeric(),
                Forms\Components\TextInput::make('total_fertilizer_cost')
                    ->label('Phân')
                    ->numeric(),
                Forms\Components\TextInput::make('total_chemical_cost')
                    ->label('Thuốc')
                    ->numeric(),
                Forms\Components\TextInput::make('total_water_cost')
                    ->label('Nước')
                    ->numeric(),
                Forms\Components\TextInput::make('total_labor_cost')
                    ->label('Nhân công')
                    ->numeric(),
                Forms\Components\TextInput::make('total_machinery_cost')
                    ->label('Máy móc')
                    ->numeric(),
                Forms\Components\TextInput::make('total_land_rent_cost')
                    ->label('Thuê đất')
                    ->numeric(),
                Forms\Components\TextInput::make('total_other_cost')
                    ->label('Khác')
                    ->numeric(),
                Forms\Components\TextInput::make('total_cost')
                    ->label('Tổng chi phí')
                    ->numeric(),
                Forms\Components\TextInput::make('total_yield_kg')
                    ->label('Sản lượng kg')
                    ->numeric(),
                Forms\Components\TextInput::make('cost_per_kg')
                    ->label('Chi phí/kg')
                    ->numeric(),
                Forms\Components\TextInput::make('total_revenue')
                    ->label('Doanh thu')
                    ->numeric(),
                Forms\Components\TextInput::make('gross_margin')
                    ->label('Lãi gộp')
                    ->numeric(),
                Forms\Components\TextInput::make('gross_margin_percent')
                    ->label('Lãi gộp %')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('farm.name')->label('Nông trại')->searchable(),
                Tables\Columns\TextColumn::make('breakdown_type')->label('Loại'),
                Tables\Columns\TextColumn::make('period_label')->label('Kỳ'),
                Tables\Columns\TextColumn::make('period_start')->label('Từ')->dateTime(),
                Tables\Columns\TextColumn::make('period_end')->label('Đến')->dateTime(),
                Tables\Columns\TextColumn::make('total_cost')->label('Tổng chi phí'),
                Tables\Columns\TextColumn::make('total_revenue')->label('Doanh thu'),
                Tables\Columns\TextColumn::make('gross_margin_percent')->label('Lãi %'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('breakdown_type')->label('Trạng thái')->options(array (
  'farm' => 'Farm',
  'production_plan' => 'Kế hoạch',
  'planting_batch' => 'Lứa trồng',
  'seasonal' => 'Mùa vụ',
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
            'index' => Pages\ListCostBreakdowns::route('/'),
            'create' => Pages\CreateCostBreakdown::route('/create'),
            'edit' => Pages\EditCostBreakdown::route('/{record}/edit'),
        ];
    }
}
