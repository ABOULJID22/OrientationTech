<?php

namespace App\Filament\Resources\Domains\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DomainForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'xl' => 3])
            ->components([
                Section::make('Presentation')
                    ->columnSpan(['xl' => 2])
                    ->schema([
                        Tabs::make('domain_translations')
                            ->label('Traductions')
                            ->tabs(self::translationTabs())
                            ->columnSpanFull(),
                        TextInput::make('category')->label('Categorie'),
                    ]),

                Section::make('Indicateurs')
                    ->columnSpan(['xl' => 1])
                    ->schema([
                        TextInput::make('difficulty_level')->label('Difficulte'),
                        TextInput::make('future_potential')->label('Potentiel futur'),
                        TextInput::make('ai_impact')->label('Impact IA'),
                        TextInput::make('freelance_opportunity')->numeric()->minValue(0)->maxValue(5),
                        TextInput::make('remote_opportunity')->numeric()->minValue(0)->maxValue(5),
                        Toggle::make('is_active')->label('Actif')->default(true),
                        Toggle::make('is_featured')->label('Mis en avant')->default(false),
                    ]),

                Section::make('Competences et parcours')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'lg' => 2])
                    ->schema([
                        self::list('student_profile', 'Profil eleve adapte'),
                        self::list('technical_skills', 'Competences techniques'),
                        self::list('soft_skills', 'Competences personnelles'),
                        self::list('tools', 'Outils et technologies'),
                        self::list('related_jobs', 'Metiers associes'),
                        self::list('learning_path', 'Parcours apprentissage'),
                        self::list('schools_morocco', 'Ecoles et formations Maroc'),
                        self::list('certifications', 'Certifications'),
                    ]),

                Section::make('Salaires et conseils')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'lg' => 4])
                    ->schema([
                        TextInput::make('junior_salary_min')->numeric()->label('Junior min'),
                        TextInput::make('junior_salary_max')->numeric()->label('Junior max'),
                        TextInput::make('senior_salary_min')->numeric()->label('Senior min'),
                        TextInput::make('senior_salary_max')->numeric()->label('Senior max'),
                        self::list('advantages', 'Avantages'),
                        self::list('challenges', 'Difficultes'),
                        self::list('practical_projects', 'Projets pratiques'),
                    ]),
            ]);
    }

    private static function list(string $name, string $label): Textarea
    {
        return Textarea::make($name)
            ->label($label)
            ->rows(3)
            ->helperText('Separez les elements avec |')
            ->afterStateHydrated(function (Textarea $component, $state): void {
                if (is_array($state)) {
                    $component->state(implode(' | ', $state));
                }
            })
            ->dehydrateStateUsing(fn ($state): array => collect(explode('|', (string) $state))->map(fn ($item) => trim($item))->filter()->values()->all());
    }

    private static function translationTabs(): array
    {
        return collect(config('orientationtech.supported_locales', ['fr' => 'Francais', 'en' => 'English']))
            ->map(fn (string $label, string $locale): Tab => Tab::make($label)
                ->schema([
                    TextInput::make("name.{$locale}")
                        ->label('Nom')
                        ->required($locale === config('app.locale', 'fr'))
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set("slug.{$locale}", Str::slug($state))),

                    TextInput::make("slug.{$locale}")
                        ->label('Slug')
                        ->required($locale === config('app.locale', 'fr'))
                        ->rules(['alpha_dash']),

                    Textarea::make("short_description.{$locale}")
                        ->label('Resume eleve')
                        ->rows(3)
                        ->columnSpanFull(),

                    RichEditor::make("full_description.{$locale}")
                        ->label('Description complete')
                        ->columnSpanFull(),

                    Textarea::make("why_important.{$locale}")
                        ->label('Pourquoi c est important')
                        ->rows(3)
                        ->columnSpanFull(),

                    Textarea::make("salary_note.{$locale}")
                        ->label('Note salaires')
                        ->rows(2)
                        ->columnSpanFull(),

                    Textarea::make("start_tips.{$locale}")
                        ->label('Conseils pour commencer')
                        ->rows(3),

                    Textarea::make("keywords.{$locale}")
                        ->label('Mots cles')
                        ->rows(3),
                ])
                ->columns(['default' => 1, 'lg' => 2]))
            ->values()
            ->all();
    }
}
