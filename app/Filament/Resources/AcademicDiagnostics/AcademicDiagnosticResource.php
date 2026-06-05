<?php

namespace App\Filament\Resources\AcademicDiagnostics;

use App\Filament\Resources\AcademicDiagnostics\Pages\CreateAcademicDiagnostic;
use App\Filament\Resources\AcademicDiagnostics\Pages\EditAcademicDiagnostic;
use App\Filament\Resources\AcademicDiagnostics\Pages\ListAcademicDiagnostics;
use App\Filament\Resources\AcademicDiagnostics\Pages\ViewAcademicDiagnostic;
use App\Filament\Resources\AcademicDiagnostics\Schemas\AcademicDiagnosticForm;
use App\Filament\Resources\AcademicDiagnostics\Schemas\AcademicDiagnosticInfolist;
use App\Filament\Resources\AcademicDiagnostics\Tables\AcademicDiagnosticsTable;
use App\Models\AcademicDiagnostic;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AcademicDiagnosticResource extends Resource
{
    protected static ?string $model = AcademicDiagnostic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'result_label';

    public static function getNavigationLabel(): string
    {
        return __('filament.nav.resources.academic_diagnostics');
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        $user = auth()->user();

        return $user && method_exists($user, 'isStudent') && $user->isStudent()
            ? __('filament.nav.groups.my_orientation')
            : __('filament.nav.groups.orientation');
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
            return ! AcademicDiagnostic::query()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AcademicDiagnosticForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AcademicDiagnosticInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcademicDiagnosticsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAcademicDiagnostics::route('/'),
            'create' => CreateAcademicDiagnostic::route('/create'),
            'view' => ViewAcademicDiagnostic::route('/{record}'),
            'edit' => EditAcademicDiagnostic::route('/{record}/edit'),
        ];
    }

    public static function getNavigationUrl(): string
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'isStudent') && $user->isStudent()) {
            $existing = AcademicDiagnostic::query()
                ->where('user_id', $user->id)
                ->latest('submitted_at')
                ->first();

            if ($existing) {
                return ViewAcademicDiagnostic::getUrl(['record' => $existing]);
            }

            return CreateAcademicDiagnostic::getUrl();
        }

        return static::getUrl();
    }
}
