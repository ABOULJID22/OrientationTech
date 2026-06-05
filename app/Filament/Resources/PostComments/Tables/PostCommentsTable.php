<?php

namespace App\Filament\Resources\PostComments\Tables;

use App\Models\PostComment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostCommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post.title')
                    ->label('Article')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('user.name')
                    ->label('Auteur')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('content')
                    ->label('Commentaire')
                    ->searchable()
                    ->limit(70),

                TextColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn (?string $state): string => PostComment::statusOptions()[$state] ?? (string) $state)
                    ->badge()
                    ->sortable(),

                TextColumn::make('reports_count')
                    ->label('Signalements')
                    ->counts('reports')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(PostComment::statusOptions()),
            ])
            ->recordActions([
                Action::make('hide')
                    ->label('Masquer')
                    ->icon('heroicon-m-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->status !== PostComment::STATUS_HIDDEN)
                    ->action(fn ($record) => $record->update([
                        'status' => PostComment::STATUS_HIDDEN,
                        'hidden_by' => auth()->id(),
                        'hidden_at' => now(),
                    ]))
                    ->button(),

                Action::make('show')
                    ->label('Rendre visible')
                    ->icon('heroicon-m-eye')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->status !== PostComment::STATUS_VISIBLE)
                    ->action(fn ($record) => $record->update([
                        'status' => PostComment::STATUS_VISIBLE,
                        'hidden_by' => null,
                        'hidden_at' => null,
                    ]))
                    ->button(),

                EditAction::make()->label('Modifier')->button(),
                DeleteAction::make()->label('Supprimer')->button(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
