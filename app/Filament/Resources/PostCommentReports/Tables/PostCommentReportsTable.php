<?php

namespace App\Filament\Resources\PostCommentReports\Tables;

use App\Models\PostComment;
use App\Models\PostCommentReport;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostCommentReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('comment.post.title')
                    ->label('Article')
                    ->limit(35),

                TextColumn::make('comment.content')
                    ->label('Commentaire')
                    ->limit(55),

                TextColumn::make('reporter.name')
                    ->label('Signale par')
                    ->limit(25),

                TextColumn::make('reason')
                    ->label('Motif')
                    ->limit(35),

                TextColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn (?string $state): string => PostCommentReport::statusOptions()[$state] ?? (string) $state)
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(PostCommentReport::statusOptions()),
            ])
            ->recordActions([
                Action::make('hide_comment')
                    ->label('Masquer commentaire')
                    ->icon('heroicon-m-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->comment?->update([
                            'status' => PostComment::STATUS_HIDDEN,
                            'hidden_by' => auth()->id(),
                            'hidden_at' => now(),
                        ]);

                        $record->update([
                            'status' => PostCommentReport::STATUS_REVIEWED,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                    })
                    ->button(),

                Action::make('dismiss')
                    ->label('Rejeter')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'status' => PostCommentReport::STATUS_DISMISSED,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]))
                    ->button(),

                EditAction::make()->label('Modifier')->button(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
