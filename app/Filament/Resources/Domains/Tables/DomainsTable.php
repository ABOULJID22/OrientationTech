<?php

namespace App\Filament\Resources\Domains\Tables;

use App\Models\Domain;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DomainsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Domaine')->weight('bold')->limit(40),
                TextColumn::make('category')->label('Categorie')->badge()->searchable()->sortable(),
                TextColumn::make('difficulty_level')->label('Difficulte')->badge()->sortable(),
                TextColumn::make('future_potential')->label('Potentiel')->badge()->sortable(),
                TextColumn::make('ai_impact')->label('IA')->badge()->sortable(),
                TextColumn::make('views_count')->label('Vues')->sortable(),
                TextColumn::make('likes_count')->label('Likes')->sortable(),
                TextColumn::make('rating_average')->label('Note')->sortable(),
                IconColumn::make('is_active')->label('Actif')->boolean()->sortable(),
                IconColumn::make('is_featured')->label('A la une')->boolean()->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->label('Categorie')->options(fn () => Domain::query()->distinct()->pluck('category', 'category')->filter()->all()),
                SelectFilter::make('difficulty_level')->label('Difficulte')->options(fn () => Domain::query()->distinct()->pluck('difficulty_level', 'difficulty_level')->filter()->all()),
            ])
            ->recordActions([
                Action::make('open')->label('Voir')->icon('heroicon-m-eye')->url(fn ($record) => route('domains.show', $record))->openUrlInNewTab()->button(),
                Action::make('toggle_active')->label(fn ($record) => $record->is_active ? 'Desactiver' : 'Activer')->icon('heroicon-m-power')->color('warning')->visible(fn () => auth()->user()?->isSuperAdmin())->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))->button(),
                Action::make('feature')->label(fn ($record) => $record->is_featured ? 'Retirer une' : 'Mettre en avant')->icon('heroicon-m-star')->color('info')->visible(fn () => auth()->user()?->isSuperAdmin())->action(fn ($record) => $record->update(['is_featured' => ! $record->is_featured]))->button(),
                Action::make('reset_stats')->label('Reset stats')->color('gray')->requiresConfirmation()->visible(fn () => auth()->user()?->isSuperAdmin())->action(fn ($record) => $record->update(['views_count' => 0, 'likes_count' => 0, 'comments_count' => 0, 'ratings_count' => 0, 'rating_average' => 0]))->button(),
                EditAction::make()->visible(fn () => auth()->user()?->isSuperAdmin())->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn () => auth()->user()?->isSuperAdmin()),
                ]),
            ])
            ->defaultSort('display_order');
    }
}
