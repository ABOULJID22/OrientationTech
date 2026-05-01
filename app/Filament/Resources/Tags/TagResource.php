<?php

namespace App\Filament\Resources\Tags;

use App\Models\Tag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Resources\Tags\Schemas\TagForm;
use App\Filament\Resources\Tags\Tables\TagsTable;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }
}
