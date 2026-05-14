<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostSeasonReviewResource\Pages;
use App\Models\PostSeasonReview;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PostSeasonReviewResource extends Resource
{
    protected static ?string $model = PostSeasonReview::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $modelLabel = 'báo cáo sau vụ';

    protected static ?string $pluralModelLabel = 'báo cáo sau vụ';

    protected static ?string $navigationLabel = 'Báo cáo sau vụ';

    protected static string | \UnitEnum | null $navigationGroup = 'Báo cáo';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Select::make('production_plan_id')
                    ->label('Kế hoạch sản xuất')
                    ->relationship('productionPlan', 'id')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options(array (
  'draft' => 'Nháp',
  'submitted' => 'Đã gửi',
  'approved' => 'Đã duyệt',
  'rejected' => 'Từ chối',
)),
                Forms\Components\Textarea::make('actual_performance_summary')
                    ->label('Tóm tắt thực tế')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('actual_total_cost')
                    ->label('Chi phí thực tế')
                    ->numeric(),
                Forms\Components\TextInput::make('budget_variance')
                    ->label('Chênh lệch ngân sách')
                    ->numeric(),
                Forms\Components\Textarea::make('yield_analysis')
                    ->label('Phân tích năng suất')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('quality_assessment')
                    ->label('Đánh giá chất lượng')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('resource_utilization_review')
                    ->label('Đánh giá tài nguyên')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('pest_disease_review')
                    ->label('Sâu bệnh')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('weather_impact_analysis')
                    ->label('Thời tiết')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('lessons_learned')
                    ->label('Bài học')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('recommendations')
                    ->label('Đề xuất')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('next_season_improvements')
                    ->label('Cải tiến vụ sau')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('rejected_reason')
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
                Tables\Columns\TextColumn::make('productionPlan.id')->label('Kế hoạch'),
                Tables\Columns\TextColumn::make('productionPlan.farm.name')->label('Nông trại')->searchable(),
                Tables\Columns\TextColumn::make('productionPlan.crop.name')->label('Cây trồng')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái'),
                Tables\Columns\TextColumn::make('actual_total_cost')->label('Chi phí TT'),
                Tables\Columns\TextColumn::make('budget_variance')->label('Chênh lệch'),
                Tables\Columns\TextColumn::make('submitted_at')->label('Ngày gửi')->dateTime(),
                Tables\Columns\TextColumn::make('approved_at')->label('Ngày duyệt')->dateTime(),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options(array (
  'draft' => 'Nháp',
  'submitted' => 'Đã gửi',
  'approved' => 'Đã duyệt',
  'rejected' => 'Từ chối',
)),
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
            'index' => Pages\ListPostSeasonReviews::route('/'),
            'create' => Pages\CreatePostSeasonReview::route('/create'),
            'edit' => Pages\EditPostSeasonReview::route('/{record}/edit'),
        ];
    }
}
