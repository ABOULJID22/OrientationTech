<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('translations')
                    ->relationship('translations')
                    ->label(__('categories.translations'))
                    ->defaultItems(0)
                    ->minItems(1)
                    ->reorderable(false)
                    ->maxItems(2)
                    ->schema([
                        Select::make('locale')
                            ->label(__('postForm.locale.label'))
                            ->options([
                                'fr' => __('translations.lang.fr'),
                                'en' => __('translations.lang.en'),
                            ])
                            ->required()
                            ->disableOptionWhen(function ($value, callable $get) {
                                $items = collect($get('../../translations') ?? [])
                                    ->pluck('locale')
                                    ->filter();
                                $current = $get('locale');
                                return $items->contains($value) && $current !== $value;
                            }),
                        TextInput::make('name')->label(__('categories.name'))->required()->live(onBlur: true)->afterStateUpdated(function (?string $state, callable $set, callable $get) {
                            // Only auto-fill the slug when the slug field is currently empty
                            $current = $get('slug');
                            if (empty($current) && $state !== null) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                        TextInput::make('slug')->label(__('categories.slug'))->required(),
                        Textarea::make('description')->label(__('categories.description'))->default(null)->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
