<?php

namespace App\Filament\Pages;

use App\Models\AcademicDiagnostic;
use App\Models\TestPersonnalise;
use App\Models\User;
use App\Services\Notifications\PlatformNotificationService;
use App\Services\Orientation\AIOrientationService;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use UnitEnum;

class RapportOrientationComplet extends Page
{
    protected string $view = 'filament.pages.rapport-orientation-complet';

    protected static ?string $navigationLabel = 'Rapport d\'orientation complet';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?int $navigationSort = 40;

    public array $report = [];

    public ?AcademicDiagnostic $diagnostic = null;

    public ?TestPersonnalise $personality = null;

    public ?User $user = null;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        $user = auth()->user();

        return $user && method_exists($user, 'isStudent') && $user->isStudent()
            ? __('filament.nav.groups.my_orientation')
            : __('filament.nav.groups.orientation');
    }

    public function mount(): void
    {
        $this->user = $this->resolveReportUser()?->loadMissing('studentProfile');
        $userId = $this->user?->id;

        if (! $userId || ! $this->user) {
            return;
        }

        $this->diagnostic = AcademicDiagnostic::query()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->latest('submitted_at')
            ->first();

        $this->personality = TestPersonnalise::query()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->latest('submitted_at')
            ->first();

        if (! $this->diagnostic || ! $this->personality) {
            return;
        }

        if (! ($this->diagnostic->isCompleted() && $this->personality->isCompleted())) {
            return;
        }

        $this->report = (new AIOrientationService(
            $this->diagnostic,
            $this->personality,
            $this->user,
        ))->generateFullReport();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            $studentId = request()->query('student');

            return is_string($studentId) && static::studentHasCompletedOrientation($studentId);
        }

        if (method_exists($user, 'isStudent') && $user->isStudent()) {
            return static::studentHasCompletedOrientation($user->id);
        }

        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user
            && method_exists($user, 'isStudent')
            && $user->isStudent()
            && static::studentHasCompletedOrientation($user->id);
    }

    public function getHeading(): string
    {
        return 'Rapport d\'orientation complet';
    }

    public function getSubheading(): ?string
    {
        return 'Analyse personnalisee basee sur le diagnostic academique, l Ikigai et le test personnalise.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Exporter PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->visible(fn (): bool => ! empty($this->report))
                ->action(fn () => $this->exportPdf()),
        ];
    }

    public function exportPdf()
    {
        if ($this->user) {
            app(PlatformNotificationService::class)->notifyReportPdfGenerated($this->user);
        }

        $filename = 'rapport-orientationtech-' . Str::slug($this->user?->name ?? 'eleve') . '-' . now()->format('Y-m-d') . '.pdf';

        $pdf = Pdf::loadView('pdf.rapport-orientation-complet', [
            'report' => $this->report,
            'diagnostic' => $this->diagnostic,
            'personality' => $this->personality,
            'user' => $this->user,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    private function resolveReportUser(): ?User
    {
        $authUser = auth()->user();

        if (! $authUser) {
            return null;
        }

        if (method_exists($authUser, 'isSuperAdmin') && $authUser->isSuperAdmin()) {
            $studentId = request()->query('student');

            if (! is_string($studentId) || ! static::studentHasCompletedOrientation($studentId)) {
                return null;
            }

            return User::query()->find($studentId);
        }

        if (method_exists($authUser, 'isStudent') && $authUser->isStudent()) {
            return $authUser;
        }

        return null;
    }

    private static function studentHasCompletedOrientation(string $studentId): bool
    {
        $diagnosticCompleted = AcademicDiagnostic::query()
            ->where('user_id', $studentId)
            ->where('status', 'completed')
            ->exists();

        $personalityCompleted = TestPersonnalise::query()
            ->where('user_id', $studentId)
            ->where('status', 'completed')
            ->exists();

        return $diagnosticCompleted && $personalityCompleted;
    }
}
