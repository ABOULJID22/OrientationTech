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
                // closure to decide if the current form has the 'client' role selected
                $hasClientRole = function (callable $get): bool {
                        $selected = $get('roles');

                        if (empty($selected)) {
                                return false;
                        }

                        // $selected may contain role ids, names, or arrays (depending on context)
                        foreach ((array) $selected as $val) {
                                // direct name
                                if ($val === 'client' || $val === 'Client') {
                                        return true;
                                }

                                // array with name key (relationship payload)
                                if (is_array($val) && isset($val['name']) && ($val['name'] === 'client' || $val['name'] === 'Client')) {
                                        return true;
                                }

                                // numeric id -> try to resolve role name
                                if (is_numeric($val)) {
                                        $role = \Spatie\Permission\Models\Role::find($val);
                                        if ($role && $role->name === 'client') {
                                                return true;
                                        }
                                }
                        }

                        return false;
                };

                return $schema
                        ->components([
                                        Select::make('roles')->relationship('roles', 'name')->multiple()->preload()->searchable()->reactive()->required(),

                                        // When the user is a client, we hide the editable `name` field
                                        // and use `pharmacy_name` as the visible field. If the
                                        // `pharmacy_name` is updated, we sync it into `name` so saved
                                        // records keep a consistent display name.
                                        TextInput::make('name')
                                                ->required()
                                                ->visible(fn ($get) => ! $hasClientRole($get)),

                                        TextInput::make('pharmacy_name')
                                                ->label(__('users.form.pharmacy_name'))
                                                ->visible(fn ($get) => $hasClientRole($get))
                                                ->required()
                                                ->lazy()
                                                ->afterStateUpdated(function (?string $state, callable $set, callable $get) use ($hasClientRole) {
                                                        // Only sync into `name` if the form currently has the client role
                                                        if (! $hasClientRole($get)) {
                                                                return;
                                                        }

                                                        // Always sync the pharmacy_name into name. If empty, set name to null
                                                        // so server-side will handle it (or DB constraints will apply).
                                                        $set('name', $state === null || $state === '' ? null : $state);
                                                }),
                    TextInput::make('pharmacist_name')->label(__('users.form.pharmacist_name_full'))->visible(fn ($get) => $hasClientRole($get))->required(),
                    TextInput::make('email')->label(__('users.form.email'))->email()->required(),

                    TextInput::make('first_name')->label(__('users.form.first_name'))->visible(fn ($get) => ! $hasClientRole($get)),
                    TextInput::make('last_name')->label(__('users.form.last_name'))->visible(fn ($get) => ! $hasClientRole($get)),

                    TextInput::make('phone')->tel()->label(__('users.form.phone')),
                    TextInput::make('phone_2')->tel()->label(__('users.form.phone_2')),
                    TextInput::make('address')->label(__('users.form.address')),
                    TextInput::make('city')->label(__('users.form.city')),
                    TextInput::make('postal_code')->label(__('users.form.postal_code')),
                    TextInput::make('country')->label(__('users.form.country')),
                    TextInput::make('website')->label(__('users.form.website'))->url()->visible(fn ($get) => $hasClientRole($get)),
                    TextInput::make('job_title')->label(__('users.form.job_title')),
                    TextInput::make('registration_number')->label(__('users.form.registration_number'))->visible(fn ($get) => $hasClientRole($get)),
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
                    //DateTimePicker::make('last_login_at')->label('Dernière connexion'), delete ce ligne
                    TextInput::make('password')->password()->revealable()->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)->dehydrated(fn ($state) => filled($state)),
            ]);
    }
}
