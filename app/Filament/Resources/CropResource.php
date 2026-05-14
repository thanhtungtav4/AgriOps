<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CropResource\Pages;
use App\Models\Crop;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CropResource extends Resource
{
    protected static ?string $model = Crop::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Dữ liệu nền';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-asia-australia';

    protected static ?string $modelLabel = 'cây trồng';

    protected static ?string $pluralModelLabel = 'cây trồng';

    protected static ?string $navigationLabel = 'Cây trồng';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Tên cây trồng')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('group')
                    ->label('Nhóm cây')
                    ->options(self::groupOptions())
                    ->default('leafy'),
                Forms\Components\Select::make('sale_unit')
                    ->label('Đơn vị bán')
                    ->options([
                        'kg' => 'kg',
                        'trái' => 'Trái',
                        'bó' => 'Bó',
                        'thùng' => 'Thùng',
                    ])
                    ->default('kg'),
                Forms\Components\Select::make('production_unit')
                    ->label('Đơn vị sản xuất')
                    ->options([
                        'cây' => 'Cây',
                        'm2' => 'm²',
                        'luống' => 'Luống',
                    ])
                    ->default('cây'),
                Forms\Components\Checkbox::make('can_harvest_multiple')
                    ->label('Thu hoạch nhiều lần'),
                Forms\Components\Checkbox::make('has_multiple_cycles')
                    ->label('Có nhiều chu kỳ'),
                Forms\Components\TextInput::make('avg_growth_days')
                    ->label('Số ngày sinh trưởng TB')
                    ->integer()
                    ->default(0),
                Forms\Components\TextInput::make('harvest_exploitation_days')
                    ->label('Số ngày khai thác thu hoạch')
                    ->integer()
                    ->default(0),
                Forms\Components\TextInput::make('rest_days')
                    ->label('Số ngày nghỉ giữa chu kỳ')
                    ->integer()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Tên cây trồng')->searchable(),
                Tables\Columns\TextColumn::make('group')->label('Nhóm')->formatStateUsing(fn (?string $state): string => self::groupOptions()[$state] ?? ($state ?? '-')),
                Tables\Columns\TextColumn::make('sale_unit')->label('Đơn vị bán'),
                Tables\Columns\TextColumn::make('production_unit')->label('Đơn vị sản xuất'),
                Tables\Columns\IconColumn::make('can_harvest_multiple')
                    ->label('Thu nhiều lần')
                    ->boolean(),
                Tables\Columns\IconColumn::make('has_multiple_cycles')
                    ->label('Nhiều chu kỳ')
                    ->boolean(),
                Tables\Columns\TextColumn::make('avg_growth_days')->label('Ngày sinh trưởng'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('Nhóm cây')
                    ->options(self::groupOptions()),
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

    private static function groupOptions(): array
    {
        return [
            'leafy' => 'Rau ăn lá / rau thơm',
            'fruit' => 'Rau ăn quả',
            'root' => 'Củ / rễ / thân ngầm',
            'fruit_tree' => 'Cây ăn trái',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrops::route('/'),
            'create' => Pages\CreateCrop::route('/create'),
            'edit' => Pages\EditCrop::route('/{record}/edit'),
        ];
    }
}
