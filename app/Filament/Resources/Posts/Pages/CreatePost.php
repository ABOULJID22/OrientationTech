<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $translations = collect($data['translations'] ?? [])
            ->map(function ($item) {
                if (is_array($item) && array_key_exists('locale', $item)) {
                    return $item;
                }

                if (is_array($item)) {
                    $first = Arr::first($item, fn ($value) => is_array($value));
                    if (is_array($first)) {
                        return $first;
                    }
                }

                return $item;
            })
            ->filter(fn ($item) => is_array($item));

        $primary = $translations
            ->firstWhere('locale', 'fr')
            ?: $translations->first();

        if (is_array($primary)) {
            $data['title'] = Arr::get($primary, 'title', $data['title'] ?? null);
            $data['slug'] = Arr::get($primary, 'slug', $data['slug'] ?? null);
            $data['content'] = Arr::get($primary, 'content', $data['content'] ?? null);
        }

        if (! filled($data['slug'] ?? null) && filled($data['title'] ?? null)) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (! filled($data['slug'] ?? null)) {
            $fallbackSlug = Str::slug($data['title'] ?? Str::random(8));
            $data['slug'] = $fallbackSlug ?: Str::random(12);
        }

        if (! filled($data['title'] ?? null)) {
            $data['title'] = $data['slug'];
        }

        $data['translations'] = $translations
            ->values()
            ->all();

        $data['author_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        // Use the configured fallback locale (primary content language) when
        // populating the post's main attributes. This prevents overwriting the
        // post slug when a secondary translation (e.g. English) is edited.
        $primaryLocale = config('app.fallback_locale') ?: 'fr';
        $t = $record->translation($primaryLocale) ?: $record->translations()->first();

        if ($t) {
            $record->forceFill([
                'title' => $t->title,
                'slug' => $t->slug,
                'content' => $t->content,
            ])->saveQuietly();
        }
    }
}
