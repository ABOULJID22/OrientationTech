<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Header: avatar, name, role
                ImageEntry::make('avatar')
                    ->label(__('user.infolist.avatar'))
                    ->circular(),

                TextEntry::make('name')
                    ->label(__('user.infolist.full_name')),
                TextEntry::make('email')->label(__('user.infolist.email'))->placeholder('-'),
                TextEntry::make('phone')->label(__('user.infolist.phone'))->placeholder('-'),
                TextEntry::make('phone_2')->label(__('user.infolist.phone_2'))->placeholder('-'),
                TextEntry::make('address')->label(__('user.infolist.address'))->placeholder('-'),
                TextEntry::make('city')->label(__('user.infolist.city'))->placeholder('-'),

                // Pharmacy block (only for client)
                TextEntry::make('pharmacy_name')
                    ->label(__('user.infolist.pharmacy_name'))
                    ->visible(fn ($record) => $record?->hasRole('client'))
                    ->placeholder('-'),
                TextEntry::make('pharmacist_name')
                    ->label(__('user.infolist.pharmacist_name'))
                    ->visible(fn ($record) => $record?->hasRole('client'))
                    ->placeholder('-'),
                TextEntry::make('pharmacy_phone')
                    ->label(__('user.infolist.pharmacy_phone'))
                    ->visible(fn ($record) => $record?->hasRole('client'))
                    ->placeholder('-'),
                TextEntry::make('pharmacy_address')
                    ->label(__('user.infolist.pharmacy_address'))
                    ->visible(fn ($record) => $record?->hasRole('client'))
                    ->placeholder('-'),

                // Admin / timestamps
                TextEntry::make('created_at')
                    ->label(__('user.infolist.created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_login_at')
                    ->label(__('user.infolist.last_login_at'))
                    ->dateTime()
                    ->placeholder('-'),
                /* TextEntry::make('email_verified_at')
                    ->label(__('user.infolist.email_verified_at'))
                    ->dateTime()
                    ->placeholder('-'), */
                TextEntry::make('updated_at')
                    ->label(__('user.infolist.updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
