<?php

namespace App\Filament\Resources\PostCommentReports;

use App\Filament\Resources\PostCommentReports\Pages\EditPostCommentReport;
use App\Filament\Resources\PostCommentReports\Pages\ListPostCommentReports;
use App\Filament\Resources\PostCommentReports\Schemas\PostCommentReportForm;
use App\Filament\Resources\PostCommentReports\Tables\PostCommentReportsTable;
use App\Models\PostCommentReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PostCommentReportResource extends Resource
{
    protected static ?string $model = PostCommentReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?int $navigationSort = 31;

    public static function getNavigationLabel(): string
    {
        return __('filament.nav.resources.post_comment_reports');
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __('filament.nav.groups.content');
    }

    public static function form(Schema $schema): Schema
    {
        return PostCommentReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostCommentReportsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['comment.post.author', 'comment.user', 'reporter']);
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return $query;
        }

        if ($user?->isTeacher()) {
            return $query->whereHas('comment.post', fn (Builder $query): Builder => $query->where('author_id', $user->id));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->isSuperAdmin() || $user->isTeacher());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostCommentReports::route('/'),
            'edit' => EditPostCommentReport::route('/{record}/edit'),
        ];
    }
}
