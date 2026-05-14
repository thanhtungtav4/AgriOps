<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductStandardResource\Pages;
use App\Models\ProductStandard;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductStandardResource extends Resource
{
    protected static ?string $model = ProductStandard::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Dữ liệu nền';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $modelLabel = 'tiêu chuẩn sản phẩm';

    protected static ?string $pluralModelLabel = 'tiêu chuẩn sản phẩm';

    protected static ?string $navigationLabel = 'Tiêu chuẩn sản phẩm';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('crop_id')
                    ->label('Cây trồng')
                    ->relationship('crop', 'name')
                    ->required(),
                Forms\Components\Select::make('variety_id')
                    ->relationship('variety', 'name')
                    ->label('Giống cây'),
                Forms\Components\TextInput::make('name')
                    ->label('Tên tiêu chuẩn')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label('Mã tiêu chuẩn')
                    ->maxLength(50),
                Forms\Components\Textarea::make('specifications')
                    ->label('Quy cách / yêu cầu chất lượng')
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
                Tables\Columns\TextColumn::make('variety.name')->label('Giống cây')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Tên tiêu chuẩn')->searchable(),
                Tables\Columns\TextColumn::make('code')->label('Mã'),
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
            'index' => Pages\ListProductStandards::route('/'),
            'create' => Pages\CreateProductStandard::route('/create'),
            'edit' => Pages\EditProductStandard::route('/{record}/edit'),
        ];
    }
}
