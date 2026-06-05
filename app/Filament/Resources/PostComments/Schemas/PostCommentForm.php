<?php

namespace App\Filament\Resources\PostComments\Schemas;

use App\Models\PostComment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostCommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Moderation')
                ->schema([
                    Textarea::make('content')
                        ->label('Commentaire')
                        ->required()
                        ->rows(6),

                    Select::make('status')
                        ->label('Statut')
                        ->options(PostComment::statusOptions())
                        ->required()
                        ->native(false),
                ]),
        ]);
    }
}
