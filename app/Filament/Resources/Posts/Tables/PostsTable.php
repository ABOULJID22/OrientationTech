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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

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
                            ->sortable()
                            ->toggleable(),

                        IconColumn::make('is_featured')
                            ->boolean()
                            ->label('Featured')
                            ->sortable(),

                        TextColumn::make('views_count')
                            ->label('Views')
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
                    'scheduled' => 'Scheduled',
                    'published' => 'Published',
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
