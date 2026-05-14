<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CropVarietyResource\Pages;
use App\Models\CropVariety;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CropVarietyResource extends Resource
{
    protected static ?string $model = CropVariety::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Dữ liệu nền';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $modelLabel = 'giống cây';

    protected static ?string $pluralModelLabel = 'giống cây';

    protected static ?string $navigationLabel = 'Giống cây';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('crop_id')
                    ->label('Cây trồng')
                    ->relationship('crop', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Tên giống')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label('Mã giống')
                    ->maxLength(50),
                Forms\Components\Textarea::make('description')
                    ->label('Mô tả')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('crop.name')->label('Cây trồng')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Tên giống')->searchable(),
                Tables\Columns\TextColumn::make('code')->label('Mã giống'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('crop_id')
                    ->label('Cây trồng')
                    ->relationship('crop', 'name'),
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
            'index' => Pages\ListCropVarieties::route('/'),
            'create' => Pages\CreateCropVariety::route('/create'),
            'edit' => Pages\EditCropVariety::route('/{record}/edit'),
        ];
    }
}
