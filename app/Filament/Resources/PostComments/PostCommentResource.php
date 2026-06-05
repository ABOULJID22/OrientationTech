<?php

namespace App\Filament\Resources\PostComments;

use App\Filament\Resources\PostComments\Pages\EditPostComment;
use App\Filament\Resources\PostComments\Pages\ListPostComments;
use App\Filament\Resources\PostComments\Schemas\PostCommentForm;
use App\Filament\Resources\PostComments\Tables\PostCommentsTable;
use App\Models\PostComment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PostCommentResource extends Resource
{
    protected static ?string $model = PostComment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'content';

    public static function getNavigationLabel(): string
    {
        return __('filament.nav.resources.post_comments');
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __('filament.nav.groups.content');
    }

    public static function form(Schema $schema): Schema
    {
        return PostCommentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostCommentsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['post.author', 'user', 'parent']);
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return $query;
        }

        if ($user?->isTeacher()) {
            return $query->whereHas('post', fn (Builder $query): Builder => $query->where('author_id', $user->id));
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
            'index' => ListPostComments::route('/'),
            'edit' => EditPostComment::route('/{record}/edit'),
        ];
    }
}
