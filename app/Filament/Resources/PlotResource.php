<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlotResource\Pages;
use App\Models\Plot;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlotResource extends Resource
{
    protected static ?string $model = Plot::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Dữ liệu nền';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map';

    protected static ?string $modelLabel = 'khu trồng';

    protected static ?string $pluralModelLabel = 'khu trồng';

    protected static ?string $navigationLabel = 'Khu trồng';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name')
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->label('Mã khu')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('name')
                    ->label('Tên khu')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('area_m2')
                    ->label('Diện tích (m²)')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('soil_type')
                    ->label('Loại đất')
                    ->maxLength(100),
                Forms\Components\TextInput::make('water_source')
                    ->label('Nguồn nước')
                    ->maxLength(100),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'available' => 'Sẵn sàng',
                        'preparing' => 'Đang chuẩn bị',
                        'planting' => 'Đang trồng',
                        'harvesting' => 'Đang thu hoạch',
                        'rest' => 'Nghỉ đất',
                        'restoring' => 'Phục hồi đất',
                        'suspended' => 'Tạm ngưng',
                    ])
                    ->default('available'),
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
                Tables\Columns\TextColumn::make('code')->label('Mã khu')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Tên khu'),
                Tables\Columns\TextColumn::make('area_m2')->label('Diện tích (m²)'),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái')->formatStateUsing(fn (?string $state): string => self::statusOptions()[$state] ?? ($state ?? '-')),
                Tables\Columns\TextColumn::make('soil_type')->label('Loại đất'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(self::statusOptions()),
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

    private static function statusOptions(): array
    {
        return [
            'available' => 'Sẵn sàng',
            'preparing' => 'Đang chuẩn bị',
            'planting' => 'Đang trồng',
            'harvesting' => 'Đang thu hoạch',
            'rest' => 'Nghỉ đất',
            'restoring' => 'Phục hồi đất',
            'suspended' => 'Tạm ngưng',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlots::route('/'),
            'create' => Pages\CreatePlot::route('/create'),
            'edit' => Pages\EditPlot::route('/{record}/edit'),
        ];
    }
}
