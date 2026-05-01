<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tag')
                ->schema([
                    TextInput::make('name')->required()->label('Name'),
                    TextInput::make('slug')->required()->label('Slug'),
                ]),
        ]);
    }
}
