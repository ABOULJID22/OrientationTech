<?php

namespace App\Filament\Resources\TestPersonnalises\Schemas;

use App\Services\TestPersonnalises\TestPersonnaliseQuestionnaire;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class TestPersonnaliseForm
{
    public static function configure(Schema $schema): Schema
    {
        $responseScale = TestPersonnaliseQuestionnaire::responseScale();

        $steps = [
            Step::make('Objectif du test')
                ->description('Comprendre comment repondre avant de commencer.')
                ->schema([
                    Section::make('Test de personnalite et orientation')
                        ->description('Ce test aide a identifier tes traits dominants et leur compatibilite avec les domaines numeriques.')
                        ->schema([
                            Placeholder::make('about_test_message')
                                ->content('Reponds selon ce qui te ressemble vraiment. Le resultat analyse la creativite, l esprit analytique, le leadership, la communication, l autonomie, le travail en equipe, l adaptabilite, la gestion du stress et la curiosite intellectuelle.'),
                        ]),
                ]),
            Step::make('Contexte')
                ->description('Preciser le niveau cible du test.')
                ->schema([
                    Select::make('target_level')
                        ->label('Niveau cible')
                        ->options(TestPersonnaliseQuestionnaire::targetLevels())
                        ->required(),
                    TextInput::make('test_name')
                        ->label('Nom du test')
                        ->default('Test personnalise OrientationTech')
                        ->required(),
                    TextInput::make('version')
                        ->label('Annee')
                        ->default((string) now()->year)
                        ->required(),
                ]),
        ];

        foreach (TestPersonnaliseQuestionnaire::axes() as $axis) {
            $questions = [];

            foreach ($axis['questions'] ?? [] as $question) {
                $questions[] = Radio::make('answers.' . $question['id'])
                    ->label($question['text'])
                    ->options($responseScale)
                    ->required()
                    ->inline(false);
            }

            $steps[] = Step::make($axis['label'])
                ->description('Temps estime : ' . ($axis['time_minutes'] ?? 0) . ' min')
                ->schema([
                    Section::make($axis['label'])
                        ->description('Aucun axe ne decide seul de ton orientation. La recommandation finale combine plusieurs signaux.')
                        ->schema([
                            Grid::make(1)->schema($questions),
                        ]),
                ]);
        }

        $steps[] = Step::make('Validation')
            ->description('Verifier avant l envoi.')
            ->schema([
                Placeholder::make('summary_hint')
                    ->content('Apres validation, OrientationTech calcule tes scores de personnalite, tes correspondances avec les domaines numeriques, puis fusionne ces resultats avec ton diagnostic academique et ton Ikigai.'),
            ]);

        return $schema
            ->columns(1)
            ->components([
                Wizard::make($steps)
                    ->startOnStep(1)
                    ->persistStepInQueryString(),
            ]);
    }
}
