<?php

namespace App\Filament\Resources;

use App\Filament\Pages\RapportOrientationComplet;
use App\Filament\Resources\OrientationReportResource\Pages\ListOrientationReports;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class OrientationReportResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return 'Rapports d\'orientation';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __('filament.nav.groups.orientation');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Eleve')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('latest_diagnostic')
                    ->label('Diagnostic')
                    ->badge()
                    ->getStateUsing(fn (User $record): string => $record->academicDiagnostics()
                        ->where('status', 'completed')
                        ->latest('submitted_at')
                        ->value('result_label') ?? 'Termine'),
                TextColumn::make('latest_personality')
                    ->label('Domaine principal')
                    ->badge()
                    ->getStateUsing(fn (User $record): string => $record->testPersonnalises()
                        ->where('status', 'completed')
                        ->latest('submitted_at')
                        ->value('primary_domain') ?? 'Determine'),
                TextColumn::make('diagnostic_submitted_at')
                    ->label('Diagnostic soumis')
                    ->dateTime()
                    ->sortable(false)
                    ->getStateUsing(fn (User $record) => $record->academicDiagnostics()
                        ->where('status', 'completed')
                        ->latest('submitted_at')
                        ->value('submitted_at')),
                TextColumn::make('personality_submitted_at')
                    ->label('Test soumis')
                    ->dateTime()
                    ->sortable(false)
                    ->getStateUsing(fn (User $record) => $record->testPersonnalises()
                        ->where('status', 'completed')
                        ->latest('submitted_at')
                        ->value('submitted_at')),
            ])
            ->recordActions([
                Action::make('view_report')
                    ->label('Voir rapport')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (User $record): string => RapportOrientationComplet::getUrl([
                        'student' => $record->id,
                    ])),
            ])
            ->defaultSort('name');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->role(User::ROLE_STUDENT)
            ->whereHas('academicDiagnostics', fn (Builder $query): Builder => $query->where('status', 'completed'))
            ->whereHas('testPersonnalises', fn (Builder $query): Builder => $query->where('status', 'completed'));
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrientationReports::route('/'),
        ];
    }
}
