<?php

namespace App\Filament\Resources\ResourceContents\Schemas;

use App\Models\ResourceContent;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ResourceContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'xl' => 3,
            ])
            ->components([
                Section::make('Contenu de la ressource')
                    ->icon('heroicon-o-document-text')
                    ->description('Ajoutez une ressource utile pour aider les eleves a explorer les metiers du numerique.')
                    ->columnSpan(['xl' => 2])
                    ->schema([
                        Tabs::make('resource_translations')
                            ->label('Traductions')
                            ->tabs(self::translationTabs())
                            ->columnSpanFull(),
                    ]),

                Section::make('Classification')
                    ->icon('heroicon-o-squares-2x2')
                    ->description('Organisez la ressource par type, domaine ou metier.')
                    ->columns(1)
                    ->columnSpan(['xl' => 1])
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->options(ResourceContent::typeOptions())
                            ->default(ResourceContent::TYPE_GUIDE)
                            ->required()
                            ->native(false),

                        TextInput::make('domain_key')
                            ->label('Domaine')
                            ->placeholder('Ex: Intelligence Artificielle'),

                        TextInput::make('career_name')
                            ->label('Metier associe')
                            ->placeholder('Ex: Data Analyst'),
                    ]),

                Section::make('Medias')
                    ->icon('heroicon-o-photo')
                    ->columns(['default' => 1, 'lg' => 2])
                    ->columnSpan(['xl' => 2])
                    ->collapsible()
                    ->schema([
                        FileUpload::make('cover_image')
                            ->label('Image de couverture')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('resources/covers'),

                        FileUpload::make('file_path')
                            ->label('Fichier PDF ou document')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('resources/files')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            ]),

                        TextInput::make('video_url')
                            ->label('Lien video')
                            ->url()
                            ->placeholder('https://... YouTube, Vimeo ou autre')
                            ->columnSpanFull(),
                    ]),

                Section::make('Publication')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(['default' => 1, 'lg' => 2])
                    ->columnSpan(['xl' => 1])
                    ->schema([
                        Select::make('status')
                            ->label('Statut')
                            ->options(ResourceContent::statusOptions())
                            ->default(ResourceContent::STATUS_DRAFT)
                            ->required()
                            ->native(false),

                        DateTimePicker::make('published_at')
                            ->label('Date de publication')
                            ->seconds(false)
                            ->native(false),

                        Toggle::make('is_featured')
                            ->label('Mettre en avant')
                            ->default(false),

                        TextInput::make('views_count')
                            ->label('Vues')
                            ->disabled()
                            ->default(0),
                    ]),
            ]);
    }

    private static function translationTabs(): array
    {
        return collect(config('orientationtech.supported_locales', ['fr' => 'Francais', 'en' => 'English']))
            ->map(fn (string $label, string $locale): Tab => Tab::make($label)
                ->schema([
                    TextInput::make("title.{$locale}")
                        ->label('Titre')
                        ->required($locale === config('app.locale', 'fr'))
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set) use ($locale): void {
                            $set("slug.{$locale}", Str::slug($state));
                        }),

                    TextInput::make("slug.{$locale}")
                        ->label('Lien public')
                        ->required($locale === config('app.locale', 'fr'))
                        ->rules(['alpha_dash'])
                        ->helperText('Slug public pour cette langue.'),

                    Textarea::make("summary.{$locale}")
                        ->label('Resume court')
                        ->rows(3)
                        ->maxLength(700)
                        ->columnSpanFull(),

                    RichEditor::make("content.{$locale}")
                        ->label('Contenu detaille')
                        ->toolbarButtons([
                            ['bold', 'italic', 'underline', 'strike', 'clearFormatting'],
                            ['h1', 'h2', 'h3', 'lead', 'small'],
                            ['blockquote', 'bulletList', 'orderedList', 'link'],
                            ['undo', 'redo'],
                        ])
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('resources/content')
                        ->columnSpanFull(),
                ])
                ->columns(1))
            ->values()
            ->all();
    }
}
