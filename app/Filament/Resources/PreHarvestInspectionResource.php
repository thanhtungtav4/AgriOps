<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PreHarvestInspectionResource\Pages;
use App\Models\PreHarvestInspection;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PreHarvestInspectionResource extends Resource
{
    protected static ?string $model = PreHarvestInspection::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $modelLabel = 'nghiệm thu trước thu hoạch';

    protected static ?string $pluralModelLabel = 'nghiệm thu trước thu hoạch';

    protected static ?string $navigationLabel = 'Nghiệm thu trước thu hoạch';

    protected static string | \UnitEnum | null $navigationGroup = 'An toàn & Chất lượng';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name')
                    ->required(),
                Forms\Components\Select::make('planting_batch_id')
                    ->label('Lứa trồng')
                    ->relationship('plantingBatch', 'code')
                    ->required(),
                Forms\Components\Select::make('plot_id')
                    ->label('Lô đất')
                    ->relationship('plot', 'name'),
                Forms\Components\Select::make('inspector_user_id')
                    ->label('Người kiểm tra')
                    ->relationship('inspector', 'name'),
                Forms\Components\Select::make('approved_by_user_id')
                    ->label('Người duyệt')
                    ->relationship('approvedBy', 'name'),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options(array (
  'submitted' => 'Đã gửi',
  'approved' => 'Đã duyệt',
  'rejected' => 'Từ chối',
)),
                Forms\Components\DateTimePicker::make('inspected_at')
                    ->label('Thời điểm kiểm tra'),
                Forms\Components\DateTimePicker::make('approved_at')
                    ->label('Thời điểm duyệt'),
                Forms\Components\DateTimePicker::make('rejected_at')
                    ->label('Thời điểm từ chối'),
                Forms\Components\Textarea::make('notes')
                    ->label('Ghi chú')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Lý do từ chối')
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
                Tables\Columns\TextColumn::make('plantingBatch.code')->label('Lứa')->searchable(),
                Tables\Columns\TextColumn::make('plot.name')->label('Lô đất')->searchable(),
                Tables\Columns\TextColumn::make('inspector.name')->label('Người kiểm tra')->searchable(),
                Tables\Columns\TextColumn::make('inspected_at')->label('Ngày kiểm tra')->dateTime(),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options(array (
  'submitted' => 'Đã gửi',
  'approved' => 'Đã duyệt',
  'rejected' => 'Từ chối',
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
            'index' => Pages\ListPreHarvestInspections::route('/'),
            'create' => Pages\CreatePreHarvestInspection::route('/create'),
            'edit' => Pages\EditPreHarvestInspection::route('/{record}/edit'),
        ];
    }
}
