<?php

namespace App\Filament\Resources\AcademicDiagnostics\Schemas;

use App\Services\Diagnostics\AcademicDiagnosticOptions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class AcademicDiagnosticForm
{
    public static function configure(Schema $schema): Schema
    {
        $diagnosticScale = [
            1 => 'Tres faible',
            2 => 'Faible',
            3 => 'Moyen',
            4 => 'Bon',
            5 => 'Excellent',
        ];

        return $schema
            ->columns(1)
            ->components([
                Wizard::make([
                    Step::make('Cycle et niveau')
                        ->description('Identifier ton parcours scolaire actuel.')
                        ->schema([
                            Select::make('macro_cycle')
                                ->label('Cycle academique')
                                ->options(AcademicDiagnosticOptions::macroCycles())
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set): void {
                                    $set('academic_level', null);
                                    $set('track_branch', null);
                                    $set('institution_type', null);
                                }),
                            Select::make('academic_level')
                                ->label('Niveau academique')
                                ->options(fn (Get $get): array => AcademicDiagnosticOptions::levelsByCycle($get('macro_cycle')))
                                ->required()
                                ->live(),
                        ]),
                    Step::make('Interet principal')
                        ->description('Preciser ce qui attire le plus l eleve.')
                        ->schema([
                            Select::make('interest_theme')
                                ->label('Centre d interet')
                                ->options(AcademicDiagnosticOptions::interestThemes())
                                ->required(),
                            TextInput::make('remark')
                                ->label('Remarque libre')
                                ->placeholder('Exemple : je veux relier la medecine et l IA')
                                ->maxLength(500)
                                ->columnSpanFull(),
                        ]),
                    Step::make('Contexte scolaire')
                        ->description('Ajouter les informations utiles pour affiner l analyse.')
                        ->schema([
                            Select::make('track_branch')
                                ->label('Branche ou orientation')
                                ->options(fn (Get $get): array => AcademicDiagnosticOptions::branchesByCycleOrLevel($get('macro_cycle'), $get('academic_level')))
                                ->searchable(),
                            Select::make('institution_type')
                                ->label('Type d etablissement')
                                ->options(fn (Get $get): array => AcademicDiagnosticOptions::institutionTypesByCycle($get('macro_cycle')))
                                ->searchable(),
                            Select::make('specialty_family')
                                ->label('Famille de specialite')
                                ->options(AcademicDiagnosticOptions::specialtyFamilies())
                                ->searchable(),
                        ]),
                    Step::make('Profil initial')
                        ->description('Comprendre les matieres, interets, activites et motivations.')
                        ->schema([
                            Textarea::make('diagnostic_answers.preferred_subjects')
                                ->label('Matieres preferees')
                                ->placeholder('Exemple : mathematiques, sciences physiques, informatique, langues')
                                ->rows(2)
                                ->maxLength(1000)
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('diagnostic_answers.interests')
                                ->label('Centres d interet')
                                ->placeholder('Exemple : technologie, sante, dessin, commerce, environnement')
                                ->rows(2)
                                ->maxLength(1000)
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('diagnostic_answers.favorite_activities')
                                ->label('Activites favorites')
                                ->placeholder('Exemple : creer, aider, organiser, chercher, reparer, programmer, expliquer')
                                ->rows(2)
                                ->maxLength(1000)
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('diagnostic_answers.future_ambitions')
                                ->label('Ambitions futures')
                                ->placeholder('Que veux-tu devenir ou construire plus tard ?')
                                ->rows(2)
                                ->maxLength(1000)
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('diagnostic_answers.career_goals')
                                ->label('Objectifs professionnels')
                                ->placeholder('Quels metiers, secteurs ou missions t attirent ?')
                                ->rows(2)
                                ->maxLength(1000)
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('diagnostic_answers.motivations')
                                ->label('Motivations')
                                ->placeholder('Qu est-ce qui te donne envie de progresser ?')
                                ->rows(2)
                                ->maxLength(1000)
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('diagnostic_answers.family_school_environment')
                                ->label('Environnement familial et scolaire')
                                ->placeholder('Soutien familial, conditions d etude, acces ordinateur/Internet, contraintes')
                                ->rows(2)
                                ->maxLength(1000)
                                ->columnSpanFull(),
                            Radio::make('diagnostic_answers.math_logic')
                                ->label('Niveau en mathematiques, logique et raisonnement')
                                ->options($diagnosticScale)
                                ->required(),
                            Radio::make('diagnostic_answers.digital_ease')
                                ->label('Aisance avec le numerique et les outils informatiques')
                                ->options($diagnosticScale)
                                ->required(),
                        ]),
                    Step::make('Ikigai simple')
                        ->description('Croiser ce que tu aimes, tes forces, les besoins du monde et les metiers possibles.')
                        ->schema([
                            Textarea::make('diagnostic_answers.ikigai_love')
                                ->label('Ce que tu aimes')
                                ->placeholder('Activites favorites, passions, centres d interet')
                                ->rows(2)
                                ->maxLength(1000)
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('diagnostic_answers.ikigai_good_at')
                                ->label('Ce dans quoi tu excelles')
                                ->placeholder('Competences naturelles, forces academiques, talents personnels')
                                ->rows(2)
                                ->maxLength(1000)
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('diagnostic_answers.ikigai_world_needs')
                                ->label('Ce dont le monde a besoin')
                                ->placeholder('Problemes de societe, secteurs porteurs, besoins futurs du marche')
                                ->rows(2)
                                ->maxLength(1000)
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('diagnostic_answers.ikigai_profession')
                                ->label('Ce qui peut devenir une profession')
                                ->placeholder('Metiers d avenir, secteurs en croissance, opportunites economiques')
                                ->rows(2)
                                ->maxLength(1000)
                                ->required()
                                ->columnSpanFull(),
                            Radio::make('diagnostic_answers.service_motivation')
                                ->label('Je veux que mon futur metier ait un impact utile')
                                ->options($diagnosticScale)
                                ->required(),
                            Radio::make('diagnostic_answers.future_market_interest')
                                ->label('Je suis interesse par les metiers d avenir')
                                ->options($diagnosticScale)
                                ->required(),
                        ]),
                    Step::make('Details')
                        ->description('Completer les informations finales du diagnostic.')
                        ->schema([
                            TextInput::make('specialty_label')
                                ->label('Intitule de specialite')
                                ->maxLength(255),
                            Select::make('biof_language')
                                ->label('Langue d enseignement')
                                ->options(AcademicDiagnosticOptions::biofLanguages())
                                ->required(),
                        ]),
                ])
                    ->startOnStep(1)
                    ->persistStepInQueryString(),
            ]);
    }
}
