<?php

namespace App\Filament\Resources\ResourceContents;

use App\Filament\Resources\ResourceContents\Pages\CreateResourceContent;
use App\Filament\Resources\ResourceContents\Pages\EditResourceContent;
use App\Filament\Resources\ResourceContents\Pages\ListResourceContents;
use App\Filament\Resources\ResourceContents\Schemas\ResourceContentForm;
use App\Filament\Resources\ResourceContents\Tables\ResourceContentsTable;
use App\Models\ResourceContent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ResourceContentResource extends Resource
{
    protected static ?string $model = ResourceContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $recordRouteKeyName = 'id';

    public static function getNavigationLabel(): string
    {
        return __('filament.nav.resources.resource_contents');
    }

    public static function getLabel(): string
    {
        return 'Ressource pedagogique';
    }

    public static function getPluralLabel(): string
    {
        return 'Ressources pedagogiques';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __('filament.nav.groups.content');
    }

    public static function form(Schema $schema): Schema
    {
        return ResourceContentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResourceContentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResourceContents::route('/'),
            'create' => CreateResourceContent::route('/create'),
            'edit' => EditResourceContent::route('/{record:id}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return $query;
        }

        if ($user?->isTeacher()) {
            return $query->where('teacher_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->isSuperAdmin() || $user->isTeacher());
    }
}
