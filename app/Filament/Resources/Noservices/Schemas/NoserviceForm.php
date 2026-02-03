<?php

namespace App\Filament\Resources\Noservices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NoserviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([


                TextInput::make('Français')
                    ->dehydrated(false)
                    ->disabled()
                    ->default('Remarque : Les champs en français sont obligatoires. Pour les détails, saisissez un élément par ligne.')
                    ->columnSpanFull(),

              

                TextInput::make('titre')
                    ->required()
                    ->label('Titre (FR)'),

                TextInput::make('soustitre')
                    ->required()
                    ->label('Sous-titre (FR)'),

                Textarea::make('detalserivces')
                    ->label('Détails (FR)')
                    ->helperText('Un élément par ligne.')
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('resultats')
                    ->required()
                    ->label('Résultats (FR)')
                    ->columnSpanFull(),


                TextInput::make('__heading_en')
                    ->label('English')
                    ->dehydrated(false)
                    ->disabled()
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->label('Title (EN)')
                    ->default(null),

                TextInput::make('subtitle')
                    ->label('Subtitle (EN)')
                    ->default(null),

                Textarea::make('details')
                    ->label('Details (EN)')
                    ->helperText('One item per line. Lines will be saved as an array.')
                    ->columnSpanFull(),

                Textarea::make('result')
                    ->label('Result (EN)')
                    ->default(null)
                    ->columnSpanFull(),



           
                ]);
    }
}
