<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkTaskResource\Pages;
use App\Models\WorkTask;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WorkTaskResource extends Resource
{
    protected static ?string $model = WorkTask::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $modelLabel = 'công việc';

    protected static ?string $pluralModelLabel = 'công việc';

    protected static ?string $navigationLabel = 'Công việc';

    protected static string | \UnitEnum | null $navigationGroup = 'Sản xuất';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('farm_id')
                    ->label('Nông trại')
                    ->relationship('farm', 'name')
                    ->required(),
                Forms\Components\Select::make('planting_batch_id')
                    ->label('Lứa trồng')
                    ->relationship('plantingBatch', 'code'),
                Forms\Components\Select::make('plot_id')
                    ->label('Lô đất')
                    ->relationship('plot', 'name'),
                Forms\Components\Select::make('bed_id')
                    ->label('Luống')
                    ->relationship('bed', 'code'),
                Forms\Components\Select::make('growth_stage_id')
                    ->label('Giai đoạn')
                    ->relationship('growthStage', 'name'),
                Forms\Components\Select::make('assigned_user_id')
                    ->label('Người phụ trách')
                    ->relationship('assignedUser', 'name'),
                Forms\Components\TextInput::make('title')
                    ->label('Tiêu đề')
                    ->maxLength(255),
                Forms\Components\TextInput::make('task_type')
                    ->label('Loại việc')
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options(array (
  'planned' => 'Đã lập',
  'assigned' => 'Đã giao',
  'in_progress' => 'Đang làm',
  'done' => 'Hoàn tất',
  'cancelled' => 'Huỷ',
)),
                Forms\Components\Select::make('priority')
                    ->label('Ưu tiên')
                    ->options(array (
  'low' => 'Thấp',
  'normal' => 'Thường',
  'high' => 'Cao',
  'urgent' => 'Khẩn',
)),
                Forms\Components\DatePicker::make('planned_start_date')
                    ->label('Ngày bắt đầu KH'),
                Forms\Components\DatePicker::make('planned_due_date')
                    ->label('Hạn hoàn tất'),
                Forms\Components\Textarea::make('instructions')
                    ->label('Hướng dẫn')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('completion_note')
                    ->label('Ghi chú hoàn tất')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('title')->label('Tiêu đề')->searchable(),
                Tables\Columns\TextColumn::make('farm.name')->label('Nông trại')->searchable(),
                Tables\Columns\TextColumn::make('plantingBatch.code')->label('Lứa')->searchable(),
                Tables\Columns\TextColumn::make('task_type')->label('Loại'),
                Tables\Columns\TextColumn::make('priority')->label('Ưu tiên'),
                Tables\Columns\TextColumn::make('planned_due_date')->label('Hạn')->dateTime(),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options(array (
  'planned' => 'Đã lập',
  'assigned' => 'Đã giao',
  'in_progress' => 'Đang làm',
  'done' => 'Hoàn tất',
  'cancelled' => 'Huỷ',
)),
                Tables\Filters\SelectFilter::make('priority')->label('Trạng thái')->options(array (
  'low' => 'Thấp',
  'normal' => 'Thường',
  'high' => 'Cao',
  'urgent' => 'Khẩn',
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
            'index' => Pages\ListWorkTasks::route('/'),
            'create' => Pages\CreateWorkTask::route('/create'),
            'edit' => Pages\EditWorkTask::route('/{record}/edit'),
        ];
    }
}
