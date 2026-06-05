<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $defaultLocale = 'fr';

    public function up(): void
    {
        $this->wrapExistingValues('resource_contents', ['title', 'slug', 'summary', 'content']);
        $this->wrapExistingValues('domains', ['name', 'slug', 'short_description', 'full_description', 'why_important', 'salary_note', 'start_tips', 'keywords']);

        Schema::table('resource_contents', function (Blueprint $table): void {
            $table->dropUnique('resource_contents_slug_unique');
            $table->json('title')->change();
            $table->json('slug')->change();
            $table->json('summary')->nullable()->change();
            $table->json('content')->nullable()->change();
        });

        Schema::table('domains', function (Blueprint $table): void {
            $table->dropUnique('domains_slug_unique');
            $table->json('name')->change();
            $table->json('slug')->change();
            $table->json('short_description')->nullable()->change();
            $table->json('full_description')->nullable()->change();
            $table->json('why_important')->nullable()->change();
            $table->json('salary_note')->nullable()->change();
            $table->json('start_tips')->nullable()->change();
            $table->json('keywords')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('resource_contents', function (Blueprint $table): void {
            $table->string('title')->change();
            $table->string('slug')->change();
            $table->text('summary')->nullable()->change();
            $table->longText('content')->nullable()->change();
        });

        Schema::table('domains', function (Blueprint $table): void {
            $table->string('name')->change();
            $table->string('slug')->change();
            $table->text('short_description')->nullable()->change();
            $table->longText('full_description')->nullable()->change();
            $table->longText('why_important')->nullable()->change();
            $table->text('salary_note')->nullable()->change();
            $table->text('start_tips')->nullable()->change();
            $table->text('keywords')->nullable()->change();
        });

        $this->unwrapTranslatedValues('resource_contents', ['title', 'slug', 'summary', 'content']);
        $this->unwrapTranslatedValues('domains', ['name', 'slug', 'short_description', 'full_description', 'why_important', 'salary_note', 'start_tips', 'keywords']);

        Schema::table('resource_contents', function (Blueprint $table): void {
            $table->unique('slug');
        });

        Schema::table('domains', function (Blueprint $table): void {
            $table->unique('slug');
        });
    }

    private function wrapExistingValues(string $table, array $columns): void
    {
        DB::table($table)
            ->orderBy('id')
            ->select(array_merge(['id'], $columns))
            ->chunkById(100, function ($records) use ($table, $columns): void {
                foreach ($records as $record) {
                    $updates = [];

                    foreach ($columns as $column) {
                        $value = $record->{$column};

                        if ($value === null || $this->looksLikeLocaleMap($value)) {
                            continue;
                        }

                        $updates[$column] = json_encode([
                            $this->defaultLocale => $value,
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }

                    if ($updates !== []) {
                        DB::table($table)->where('id', $record->id)->update($updates);
                    }
                }
            });
    }

    private function unwrapTranslatedValues(string $table, array $columns): void
    {
        DB::table($table)
            ->orderBy('id')
            ->select(array_merge(['id'], $columns))
            ->chunkById(100, function ($records) use ($table, $columns): void {
                foreach ($records as $record) {
                    $updates = [];

                    foreach ($columns as $column) {
                        $updates[$column] = $this->translationToString($record->{$column});
                    }

                    DB::table($table)->where('id', $record->id)->update($updates);
                }
            });
    }

    private function looksLikeLocaleMap(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) && array_key_exists($this->defaultLocale, $decoded);
    }

    private function translationToString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $decoded = is_string($value) ? json_decode($value, true) : null;

        if (! is_array($decoded)) {
            return (string) $value;
        }

        return $decoded[$this->defaultLocale]
            ?? $decoded[config('app.fallback_locale')]
            ?? collect($decoded)->filter()->first()
            ?? null;
    }
};
