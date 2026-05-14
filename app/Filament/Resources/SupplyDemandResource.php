<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplyDemandResource\Pages;
use App\Models\SupplyDemand;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SupplyDemandResource extends Resource
{
    protected static ?string $model = SupplyDemand::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $modelLabel = 'nhu cầu cung ứng';

    protected static ?string $pluralModelLabel = 'nhu cầu cung ứng';

    protected static ?string $navigationLabel = 'Nhu cầu cung ứng';

    protected static string | \UnitEnum | null $navigationGroup = 'Kế hoạch & Kinh doanh';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name'),
                Forms\Components\Select::make('supply_contract_id')
                    ->label('Hợp đồng')
                    ->relationship('contract', 'id'),
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
                Forms\Components\DatePicker::make('target_date')
                    ->label('Ngày mục tiêu'),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options(array (
  'pending' => 'Chờ xử lý',
  'planned' => 'Đã lập kế hoạch',
  'fulfilled' => 'Đã đáp ứng',
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
                Tables\Columns\TextColumn::make('contract.id')->label('Hợp đồng'),
                Tables\Columns\TextColumn::make('crop.name')->label('Cây trồng')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->label('Số lượng'),
                Tables\Columns\TextColumn::make('unit')->label('Đơn vị'),
                Tables\Columns\TextColumn::make('frequency')->label('Tần suất'),
                Tables\Columns\TextColumn::make('target_date')->label('Ngày mục tiêu')->dateTime(),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options(array (
  'pending' => 'Chờ xử lý',
  'planned' => 'Đã lập kế hoạch',
  'fulfilled' => 'Đã đáp ứng',
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
            'index' => Pages\ListSupplyDemands::route('/'),
            'create' => Pages\CreateSupplyDemand::route('/create'),
            'edit' => Pages\EditSupplyDemand::route('/{record}/edit'),
        ];
    }
}
