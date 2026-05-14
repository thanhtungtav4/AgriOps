<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FarmResource\Pages;
use App\Models\Farm;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FarmResource extends Resource
{
    protected static ?string $model = Farm::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $modelLabel = 'nông trại';

    protected static ?string $pluralModelLabel = 'nông trại';

    protected static ?string $navigationLabel = 'Nông trại';

    protected static string | \UnitEnum | null $navigationGroup = 'Dữ liệu nền';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Tên nông trại')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label('Mã nông trại')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('address')
                    ->label('Địa chỉ')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('climate_zone')
                    ->label('Vùng khí hậu')
                    ->maxLength(100),
                Forms\Components\TextInput::make('responsible_person')
                    ->label('Người phụ trách')
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_area_m2')
                    ->label('Tổng diện tích (m²)')
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'active' => 'Đang hoạt động',
                        'inactive' => 'Ngưng hoạt động',
                    ])
                    ->default('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Tên nông trại')->searchable(),
                Tables\Columns\TextColumn::make('code')->label('Mã')->searchable(),
                Tables\Columns\TextColumn::make('climate_zone')->label('Vùng khí hậu'),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'active' => 'Đang hoạt động',
                    'inactive' => 'Ngưng hoạt động',
                    default => $state ?? '-',
                }),
                Tables\Columns\TextColumn::make('total_area_m2')->label('Diện tích (m²)'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'active' => 'Đang hoạt động',
                        'inactive' => 'Ngưng hoạt động',
                    ]),
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
            'index' => Pages\ListFarms::route('/'),
            'create' => Pages\CreateFarm::route('/create'),
            'edit' => Pages\EditFarm::route('/{record}/edit'),
        ];
    }
}
