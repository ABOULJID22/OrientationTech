<?php

namespace App\Filament\Resources\PostCommentReports\Schemas;

use App\Models\PostCommentReport;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostCommentReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Traitement du signalement')
                ->schema([
                    Select::make('status')
                        ->label('Statut')
                        ->options(PostCommentReport::statusOptions())
                        ->required()
                        ->native(false),

                    Textarea::make('details')
                        ->label('Details')
                        ->rows(5),
                ]),
        ]);
    }
}
