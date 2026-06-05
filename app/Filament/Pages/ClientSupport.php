<?php

namespace App\Filament\Pages;

use App\Mail\ContactMessageMail;
use App\Models\Contact;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;
use BackedEnum;
use UnitEnum;

class ClientSupport extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-lifebuoy';
    // Provide a default label; getNavigationLabel() will still translate if needed
    protected static ?string $navigationLabel = 'Support client';
    protected static ?string $title = 'Support';
    protected string $view = 'filament.pages.client-support';
    // Place client support after the calendar in navigation
    protected static ?int $navigationSort = 10;

    protected static UnitEnum|string|null $navigationGroup = null;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __('filament.nav.groups.support');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.nav.resources.demande_support_clients');
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->isStudent();
    }
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->isStudent();
    }
}
