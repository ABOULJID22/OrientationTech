<?php

namespace App\Filament\Resources\Tags\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->label('Name'),
            TextColumn::make('slug')->label('Slug'),
        ]);
    }
}
