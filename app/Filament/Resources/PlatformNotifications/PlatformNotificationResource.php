<?php

namespace App\Filament\Resources\PlatformNotifications;

use App\Filament\Resources\PlatformNotifications\Pages\CreatePlatformNotification;
use App\Filament\Resources\PlatformNotifications\Pages\EditPlatformNotification;
use App\Filament\Resources\PlatformNotifications\Pages\ListPlatformNotifications;
use App\Filament\Resources\PlatformNotifications\Schemas\PlatformNotificationForm;
use App\Filament\Resources\PlatformNotifications\Tables\PlatformNotificationsTable;
use App\Models\PlatformNotification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PlatformNotificationResource extends Resource
{
    protected static ?string $model = PlatformNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return 'Notifications plateforme';
    }

    public static function getLabel(): string
    {
        return 'Notification';
    }

    public static function getPluralLabel(): string
    {
        return 'Notifications plateforme';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Administration';
    }

    public static function form(Schema $schema): Schema
    {
        return PlatformNotificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlatformNotificationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlatformNotifications::route('/'),
            'create' => CreatePlatformNotification::route('/create'),
            'edit' => EditPlatformNotification::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }
}
