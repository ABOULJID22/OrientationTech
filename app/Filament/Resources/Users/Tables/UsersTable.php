<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table 
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label(__('users.table.avatar'))
                    ->circular()
                    ->height(40)
                    ->width(40)
                    ->disk('public')
                    ->getStateUsing(function ($record) {
                        $state = $record->avatar_url ?? null;

                        // Fallback to default avatar if empty
                        if (!$state) {
                            return asset('images/avater.png');
                        }

                        // If already a full URL, return as-is
                        if (Str::startsWith($state, ['http://', 'https://'])) {
                            return $state;
                        }

                        // If already a public storage URL (/storage/...), verify existence
                        if (Str::startsWith($state, '/storage/')) {
                            $relative = ltrim(Str::after($state, '/storage/'), '/');
                            return Storage::disk('public')->exists($relative)
                                ? $state
                                : asset('images/avater.png');
                        }

                        // If an absolute local path was stored, convert to storage-relative path
                        if (Str::contains($state, ['storage/app/public', 'storage\\app\\public'])) {
                            $state = 'avatar/' . basename($state);
                        }

                        // Build public URL if file exists, otherwise fallback
                        return Storage::disk('public')->exists($state)
                            ? Storage::disk('public')->url($state)
                            : asset('images/avater.png');
                    }),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('users.table.email'))
                    ->searchable(),
                TextColumn::make('role_label')
                    ->label(__('users.table.role'))
                    ->getStateUsing(function ($record) {
                        // If the user model has helper methods, prefer them
                        if (method_exists($record, 'isClient') && $record->isClient()) {
                            return __('users.role.pharmacies');
                        }

                        if (method_exists($record, 'isPharmacist') && $record->isPharmacist()) {
                            return __('users.role.pharmacien');
                        }

                        // Fallback to roles relation
                        if (optional($record->roles)->pluck('name')->contains('pharmacist')) {
                            return __('users.role.pharmacien');
                        }

                        return __('users.role.not_pharmacien');
                    })
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('users.table.phone'))
                    ->toggleable(),
                
        
                TextColumn::make('last_login_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                
               
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->visible(fn () => auth()->user()?->isSuperAdmin() ?? false),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
