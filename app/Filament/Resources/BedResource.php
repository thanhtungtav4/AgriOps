<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BedResource\Pages;
use App\Models\Bed;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BedResource extends Resource
{
    protected static ?string $model = Bed::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Dữ liệu nền';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'luống';

    protected static ?string $pluralModelLabel = 'luống';

    protected static ?string $navigationLabel = 'Luống';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('plot_id')
                    ->label('Khu trồng')
                    ->relationship('plot', 'name')
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->label('Mã luống')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('length_m')
                    ->label('Chiều dài (m)')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('width_m')
                    ->label('Chiều rộng (m)')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('area_m2')
                    ->label('Diện tích (m²)')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('expected_plants')
                    ->label('Số cây dự kiến')
                    ->integer()
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'available' => 'Sẵn sàng',
                        'preparing' => 'Đang chuẩn bị',
                        'planting' => 'Đang trồng',
                        'growing' => 'Đang sinh trưởng',
                        'harvesting' => 'Đang thu hoạch',
                        'rest' => 'Nghỉ đất',
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
                Tables\Columns\TextColumn::make('plot.name')->label('Khu trồng')->searchable(),
                Tables\Columns\TextColumn::make('code')->label('Mã luống')->searchable(),
                Tables\Columns\TextColumn::make('length_m')->label('Dài (m)'),
                Tables\Columns\TextColumn::make('width_m')->label('Rộng (m)'),
                Tables\Columns\TextColumn::make('area_m2')->label('Diện tích (m²)'),
                Tables\Columns\TextColumn::make('expected_plants')->label('Số cây dự kiến'),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái')->formatStateUsing(fn (?string $state): string => self::statusOptions()[$state] ?? ($state ?? '-')),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plot_id')
                    ->label('Khu trồng')
                    ->relationship('plot', 'name'),
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
            'growing' => 'Đang sinh trưởng',
            'harvesting' => 'Đang thu hoạch',
            'rest' => 'Nghỉ đất',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeds::route('/'),
            'create' => Pages\CreateBed::route('/create'),
            'edit' => Pages\EditBed::route('/{record}/edit'),
        ];
    }
}
