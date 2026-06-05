<?php

namespace App\Filament\Resources\TestPersonnalises;

use App\Filament\Resources\TestPersonnalises\Pages\CreateTestPersonnalise;
use App\Filament\Resources\TestPersonnalises\Pages\EditTestPersonnalise;
use App\Filament\Resources\TestPersonnalises\Pages\ListTestPersonnalises;
use App\Filament\Resources\TestPersonnalises\Pages\ViewTestPersonnalise;
use App\Filament\Resources\TestPersonnalises\Schemas\TestPersonnaliseForm;
use App\Filament\Resources\TestPersonnalises\Schemas\TestPersonnaliseInfolist;
use App\Filament\Resources\TestPersonnalises\Tables\TestPersonnalisesTable;
use App\Models\TestPersonnalise;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TestPersonnaliseResource extends Resource
{
    protected static ?string $model = TestPersonnalise::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'primary_domain';

    public static function getNavigationLabel(): string
    {
        return __('filament.nav.resources.personality_tests');
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        $user = auth()->user();

        return $user && method_exists($user, 'isStudent') && $user->isStudent()
            ? __('filament.nav.groups.my_orientation')
            : __('filament.nav.groups.orientation');
    }

    public static function form(Schema $schema): Schema
    {
        return TestPersonnaliseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TestPersonnaliseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TestPersonnalisesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTestPersonnalises::route('/'),
            'create' => CreateTestPersonnalise::route('/create'),
            'view' => ViewTestPersonnalise::route('/{record}'),
            'edit' => EditTestPersonnalise::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $query;
        }

        return $query->where('user_id', auth()->id());
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        if (method_exists($user, 'isStudent') && $user->isStudent()) {
            return ! TestPersonnalise::query()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && (
            (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) ||
            (method_exists($user, 'isStudent') && $user->isStudent())
        );
    }
}
