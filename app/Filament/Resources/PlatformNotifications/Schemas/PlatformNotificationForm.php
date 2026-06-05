<?php

namespace App\Filament\Resources\PlatformNotifications\Schemas;

use App\Models\PlatformNotification;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlatformNotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'xl' => 3,
            ])
            ->components([
                Section::make('Message')
                    ->icon('heroicon-o-bell-alert')
                    ->description('Redigez une notification claire pour informer les utilisateurs des nouveautes, actions importantes ou changements de fonctionnalite.')
                    ->columnSpan(['xl' => 2])
                    ->schema([
                        Hidden::make('created_by')
                            ->default(fn () => auth()->id()),

                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(120)
                            ->placeholder('Ex: Nouveau rapport disponible'),

                        Textarea::make('body')
                            ->label('Message')
                            ->required()
                            ->rows(5)
                            ->maxLength(1000)
                            ->placeholder('Expliquez simplement ce que l utilisateur doit savoir.')
                            ->columnSpanFull(),

                        TextInput::make('action_label')
                            ->label('Libelle du bouton')
                            ->placeholder('Voir le rapport'),

                        TextInput::make('action_url')
                            ->label('Lien d action')
                            ->url()
                            ->placeholder('https://... ou /admin/...'),
                    ]),

                Section::make('Ciblage')
                    ->icon('heroicon-o-user-group')
                    ->description('Choisissez les utilisateurs qui recevront la notification dans la cloche Filament.')
                    ->columnSpan(['xl' => 1])
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->options(PlatformNotification::typeOptions())
                            ->default(PlatformNotification::TYPE_INFO)
                            ->required()
                            ->native(false),

                        Select::make('feature_key')
                            ->label('Fonctionnalite')
                            ->options(PlatformNotification::featureOptions())
                            ->default('general')
                            ->required()
                            ->native(false),

                        CheckboxList::make('target_roles')
                            ->label('Destinataires')
                            ->options(PlatformNotification::roleOptions())
                            ->columns(1)
                            ->required()
                            ->helperText('Vous pouvez cibler plusieurs roles en meme temps.'),

                        Select::make('status')
                            ->label('Statut')
                            ->options(PlatformNotification::statusOptions())
                            ->default(PlatformNotification::STATUS_DRAFT)
                            ->required()
                            ->native(false)
                            ->disabled(fn ($record): bool => $record?->status === PlatformNotification::STATUS_SENT),
                    ]),
            ]);
    }
}
