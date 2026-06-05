<?php

namespace App\Filament\Resources\ResourceContents\Tables;

use App\Models\ResourceContent;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ResourceContentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(asset('images/img1.jpg')),

                TextColumn::make('title')
                    ->label('Titre')
                    ->weight('bold')
                    ->limit(50),

                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state): string => ResourceContent::typeOptions()[$state] ?? (string) $state)
                    ->badge()
                    ->sortable(),

                TextColumn::make('teacher.name')
                    ->label('Professeur')
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn (?string $state): string => ResourceContent::statusOptions()[$state] ?? (string) $state)
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('A la une')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Publication')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('views_count')
                    ->label('Vues')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(ResourceContent::typeOptions()),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ResourceContent::statusOptions()),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->icon('heroicon-m-pencil-square')
                    ->button(),

                DeleteAction::make()
                    ->label('Supprimer')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }
}
