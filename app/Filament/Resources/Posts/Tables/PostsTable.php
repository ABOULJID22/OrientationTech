<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Models\Post;
use App\Services\Notifications\PlatformNotificationService;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Panel::make([
                    Stack::make([
                        ImageColumn::make('cover_image')
                            ->label('')
                            ->disk('public')
                            ->extraImgAttributes([
                                'class' => 'w-full h-40 object-cover rounded-md',
                            ]),

                        TextColumn::make('title')
                            ->weight('bold')
                            ->searchable()
                            ->limit(90),

                        TextColumn::make('category.name')
                            ->label('Category')
                            ->sortable()
                            ->toggleable(),

                        TextColumn::make('author.name')
                            ->label('Author')
                            ->sortable()
                            ->toggleable(),

                        TextColumn::make('status')
                            ->label('Status')
                            ->formatStateUsing(fn (?string $state): string => Post::statusOptions()[$state] ?? (string) $state)
                            ->badge()
                            ->sortable()
                            ->toggleable(),

                        IconColumn::make('is_featured')
                            ->boolean()
                            ->label('Featured')
                            ->sortable(),

                        TextColumn::make('views_count')
                            ->label('Views')
                            ->sortable(),

                        TextColumn::make('likes_count')
                            ->label('Likes')
                            ->sortable(),

                        TextColumn::make('comments_count')
                            ->label('Comments')
                            ->sortable(),

                        TextColumn::make('published_at')
                            ->dateTime()
                            ->label('Published')
                            ->sortable(),
                    ])->space(2),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'pending' => 'Pending validation',
                    'scheduled' => 'Scheduled',
                    'published' => 'Published',
                    'rejected' => 'Rejected',
                    'archived' => 'Archived',
                ]),
                SelectFilter::make('category')->relationship('category','name'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('pages.blog.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn ($record): bool => $record->status === Post::STATUS_PUBLISHED)
                    ->button(),

                Action::make('approve')
                    ->label('Valider')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => auth()->user()?->isSuperAdmin() && $record->status !== Post::STATUS_PUBLISHED)
                    ->action(function ($record): void {
                        $oldStatus = $record->status;

                        $record->update([
                            'status' => Post::STATUS_PUBLISHED,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'published_at' => $record->published_at ?: now(),
                            'rejected_at' => null,
                            'rejection_reason' => null,
                        ]);

                        app(PlatformNotificationService::class)->notifyPostStatusChanged($record->fresh(['author']), $oldStatus);
                    })
                    ->button(),

                Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motif de refus')
                            ->required()
                            ->rows(4),
                    ])
                    ->visible(fn ($record): bool => auth()->user()?->isSuperAdmin() && $record->status !== Post::STATUS_REJECTED)
                    ->action(function ($record, array $data): void {
                        $oldStatus = $record->status;

                        $record->update([
                            'status' => Post::STATUS_REJECTED,
                            'rejected_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        app(PlatformNotificationService::class)->notifyPostStatusChanged($record->fresh(['author']), $oldStatus);
                    })
                    ->button(),

                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button(),

                DeleteAction::make()
                    ->label('Delete')
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
            ]);
    }
}
