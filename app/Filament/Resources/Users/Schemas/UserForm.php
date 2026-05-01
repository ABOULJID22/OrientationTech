<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;

class UserForm
{
        public static function configure(Schema $schema): Schema
        {
                return $schema
                        ->components([
                                Select::make('roles')->relationship('roles', 'name')->multiple()->preload()->searchable()->reactive()->required(),
                                TextInput::make('name')->required(),
                                TextInput::make('email')->label(__('users.form.email'))->email()->required(),
                                TextInput::make('first_name')->label(__('users.form.first_name')),
                                TextInput::make('last_name')->label(__('users.form.last_name')),
                                TextInput::make('phone')->tel()->label(__('users.form.phone')),
                                TextInput::make('phone_2')->tel()->label(__('users.form.phone_2')),
                                TextInput::make('address')->label(__('users.form.address')),
                                TextInput::make('city')->label(__('users.form.city')),
                                TextInput::make('postal_code')->label(__('users.form.postal_code')),
                                TextInput::make('country')->label(__('users.form.country')),
                                TextInput::make('job_title')->label(__('users.form.job_title')),
                                Toggle::make('is_active')->label(__('users.form.active'))->default(true),
                                FileUpload::make('avatar_url')
                                        ->default(null)
                                        ->image()
                                        ->imageEditor()
                                        ->maxSize(2048)
                                        ->acceptedFileTypes(['image/jpeg','image/png','image/webp'])
                                        ->visibility('public')
                                        ->disk('public')
                                        ->directory('avatar')
                                        ->preserveFilenames(false),
                                TextInput::make('password')
                                        ->password()
                                        ->revealable()
                                        ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                                        ->dehydrated(fn ($state) => filled($state)),
                        ]);
        }
}
