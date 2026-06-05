<?php

namespace App\Filament\Pages;

use App\Filament\Resources\AcademicDiagnostics\AcademicDiagnosticResource;
use App\Filament\Resources\TestPersonnalises\TestPersonnaliseResource;
use App\Filament\Pages\RapportOrientationComplet;
use App\Models\AcademicDiagnostic;
use App\Models\TestPersonnalise;
use App\Services\Orientation\OrientationCompleteResultService;
use App\Services\TestPersonnalises\TestPersonnaliseResultService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use UnitEnum;

class MesResultatsDePersonnalites extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Mes résultats';

    protected static ?int $navigationSort = 30;

    protected static UnitEnum|string|null $navigationGroup = null;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        $user = auth()->user();

        return $user && method_exists($user, 'isStudent') && $user->isStudent()
            ? __('filament.nav.groups.my_orientation')
            : __('filament.nav.groups.orientation');
    }

    protected string $view = 'filament.pages.mes-resultats-de-personnalites';

    public ?AcademicDiagnostic $diagnostic = null;

    public ?TestPersonnalise $personnalise = null;

    public array $mergedResult = [];

    public function mount(OrientationCompleteResultService $service, TestPersonnaliseResultService $resultService): void
    {
        $userId = auth()->id();

        $this->diagnostic = AcademicDiagnostic::query()
            ->where('user_id', $userId)
            ->latest('submitted_at')
            ->first();

        $this->personnalise = TestPersonnalise::query()
            ->where('user_id', $userId)
            ->latest('submitted_at')
            ->first();

        $scoresWereRefreshed = $this->refreshPersonnaliseScores($resultService);

        if ($this->hasBothTestsCompleted()) {
            if ($scoresWereRefreshed) {
                $service->forgetCached($this->diagnostic, $this->personnalise);
            }

            $this->mergedResult = $service->buildCached($this->diagnostic, $this->personnalise);
        }
    }

    public function reconnectGemini(OrientationCompleteResultService $service, TestPersonnaliseResultService $resultService): void
    {
        if (! $this->hasBothTestsCompleted()) {
            Notification::make()
                ->title('Completez les deux tests avant de reconnecter Gemini.')
                ->warning()
                ->send();

            return;
        }

        $this->refreshPersonnaliseScores($resultService);
        $service->forgetCached($this->diagnostic, $this->personnalise);
        $this->mergedResult = $service->buildCached($this->diagnostic, $this->personnalise);

        Notification::make()
            ->title('Gemini a ete reconnecte et le resultat a ete rafraichi.')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && method_exists($user, 'isStudent') && $user->isStudent();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function hasBothTestsCompleted(): bool
    {
        return (bool) ($this->diagnostic?->isCompleted() && $this->personnalise?->isCompleted());
    }

    public function diagnosticCompleted(): bool
    {
        return (bool) $this->diagnostic?->isCompleted();
    }

    public function personnaliseCompleted(): bool
    {
        return (bool) $this->personnalise?->isCompleted();
    }

    public function diagnosticLink(): string
    {
        if ($this->diagnostic) {
            return AcademicDiagnosticResource::getUrl('edit', ['record' => $this->diagnostic]);
        }

        return AcademicDiagnosticResource::getUrl('create');
    }

    public function personnaliseLink(): string
    {
        if ($this->personnalise) {
            return TestPersonnaliseResource::getUrl('view', ['record' => $this->personnalise]);
        }

        return TestPersonnaliseResource::getUrl('create');
    }

    public function personnaliseEditLink(): string
    {
        if ($this->personnalise) {
            return TestPersonnaliseResource::getUrl('edit', ['record' => $this->personnalise]);
        }

        return TestPersonnaliseResource::getUrl('create');
    }

    public function rapportLink(): string
    {
        return RapportOrientationComplet::getUrl();
    }

    public function personnaliseLocked(): bool
    {
        return ! $this->diagnosticCompleted();
    }

    private function refreshPersonnaliseScores(TestPersonnaliseResultService $resultService): bool
    {
        if (! $this->personnalise || empty($this->personnalise->answers)) {
            return false;
        }

        $results = $resultService->calculate($this->personnalise->answers ?? []);
        $updates = [
            'axis_scores' => $results['axis_scores'] ?? [],
            'domain_scores' => $results['domain_scores'] ?? [],
            'primary_domain' => $results['primary_domain'] ?? null,
            'secondary_domain' => $results['secondary_domain'] ?? null,
            'result_summary' => $results['result_summary'] ?? null,
            'result_payload' => $results['result_payload'] ?? [],
        ];

        foreach ($updates as $key => $value) {
            if ($this->personnalise->{$key} !== $value) {
                $this->personnalise->forceFill($updates)->saveQuietly();
                $this->personnalise->refresh();

                return true;
            }
        }

        return false;
    }
}
