<?php

namespace App\Filament\Resources\Noservices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class NoservicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Title (locale-aware, fallback)
                TextColumn::make('title')
                    ->label(fn () => app()->isLocale('fr') ? 'Titre' : 'Title')
                    ->getStateUsing(function ($record) {
                        // prefer structured en/fr accessor first
                        $en = $record->en ?? null;
                        $fr = $record->fr ?? null;

                        if (app()->isLocale('en')) {
                            return $en[0]['title'] ?? $record->title ?? $fr[0]['titre'] ?? $record->titre ?? '';
                        }

                        return $fr[0]['titre'] ?? $record->titre ?? $en[0]['title'] ?? $record->title ?? '';
                    })
                    ->searchable(),

                // Subtitle (locale-aware, fallback)
                TextColumn::make('subtitle')
                    ->label(fn () => app()->isLocale('fr') ? 'Sous-titre' : 'Subtitle')
                    ->getStateUsing(function ($record) {
                        $en = $record->en ?? null;
                        $fr = $record->fr ?? null;

                        if (app()->isLocale('en')) {
                            return $en[0]['subtitle'] ?? $record->subtitle ?? $fr[0]['soustitre'] ?? $record->soustitre ?? '';
                        }

                        return $fr[0]['soustitre'] ?? $record->soustitre ?? $en[0]['subtitle'] ?? $record->subtitle ?? '';
                    })
                    ->searchable(),

                // Details list (locale-aware, fallback) - show short comma-separated preview
                TextColumn::make('details')
                    ->label(fn () => app()->isLocale('fr') ? 'Détails' : 'Details')
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();

                        if ($locale === 'en') {
                            $items = $record->en[0]['details'] ?? $record->details ?? $record->fr[0]['detalserivces'] ?? $record->detalserivces ?? [];
                        } else {
                            $items = $record->fr[0]['detalserivces'] ?? $record->detalserivces ?? $record->en[0]['details'] ?? $record->details ?? [];
                        }

                        // If items are arrays of ['item'=>...], map them
                        if (is_array($items) && count($items) > 0) {
                            $mapped = array_map(function ($it) {
                                if (is_array($it)) {
                                    return $it['item'] ?? (array_values($it)[0] ?? null);
                                }
                                return $it;
                            }, $items);
                            $mapped = array_filter($mapped, fn($v) => $v !== null && $v !== '');
                            return Str::limit(implode(', ', $mapped), 120);
                        }

                        return '';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
