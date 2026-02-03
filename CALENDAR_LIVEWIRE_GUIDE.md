# Guide : Calendrier Livewire Professionnel pour Filament

## Option 1 : Filament Full Calendar Widget (⭐ RECOMMANDÉ)

### Installation

```bash
composer require saade/filament-fullcalendar
php artisan vendor:publish --tag="filament-fullcalendar-config"
```

### Configuration

#### 1. Créer le Widget Livewire

```bash
php artisan make:filament-widget CalendarWidget --resource=EventResource
```

#### 2. Configurer le Widget

**`app/Filament/Widgets/CalendarWidget.php`**

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Data\EventData;
use Illuminate\Database\Eloquent\Model;

class CalendarWidget extends FullCalendarWidget
{
    /**
     * Charger les événements pour le calendrier
     */
    public function fetchEvents(array $fetchInfo): array
    {
        $user = auth()->user();
        
        return Event::query()
            ->visibleTo($user)
            ->whereBetween('start_at', [
                $fetchInfo['start'],
                $fetchInfo['end']
            ])
            ->get()
            ->map(function (Event $event) {
                return EventData::make()
                    ->id($event->id)
                    ->title($event->title)
                    ->start($event->start_at)
                    ->end($event->end_at ?? $event->start_at)
                    ->backgroundColor($this->getEventColor($event->user_id))
                    ->allDay((bool) $event->all_day)
                    ->extendedProps([
                        'description' => $event->description,
                        'calendar' => $event->user?->name,
                        'creator' => $event->creator?->name,
                    ]);
            })
            ->toArray();
    }

    /**
     * Clic sur un événement
     */
    public function onEventClick($event): void
    {
        // Ouvrir un modal Filament
        $this->dispatch('open-modal', id: 'event-details', data: $event);
    }

    /**
     * Créer un événement
     */
    public function onEventCreate($event): void
    {
        Event::create([
            'title' => $event['title'],
            'start_at' => $event['start'],
            'end_at' => $event['end'],
            'all_day' => $event['allDay'] ?? false,
            'user_id' => $event['calendar_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $this->refreshEvents();
    }

    /**
     * Couleurs par pharmacie
     */
    protected function getEventColor(?int $userId): string
    {
        $palette = [
            '#4f6ba3', '#8b5cf6', '#ef4444', 
            '#10b981', '#f59e0b', '#06b6d4'
        ];
        
        if (!$userId) return $palette[0];
        
        return $palette[$userId % count($palette)];
    }

    /**
     * Configuration du calendrier
     */
    public function config(): array
    {
        return [
            'locale' => 'fr',
            'firstDay' => 1,
            'headerToolbar' => [
                'start' => 'prev,next today',
                'center' => 'title',
                'end' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'initialView' => 'dayGridMonth',
            'slotMinTime' => '06:00:00',
            'slotMaxTime' => '22:00:00',
            'slotDuration' => '00:30:00',
            'height' => 'auto',
            'contentHeight' => 650,
            'nowIndicator' => true,
            'selectable' => true,
            'editable' => false, // Désactiver le drag & drop si non souhaité
        ];
    }
}
```

#### 3. Utiliser le Widget dans une Page Filament

**`app/Filament/Pages/Calendar.php`**

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\CalendarWidget;

class Calendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Calendrier';
    protected static ?string $title = 'Calendrier';
    protected static ?int $navigationSort = 2;
    
    protected static string $view = 'filament.pages.calendar';

    protected function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }
}
```

#### 4. Vue Blade simplifiée

**`resources/views/filament/pages/calendar.blade.php`**

```blade
<x-filament-panels::page>
    {{-- Bouton créer événement (super admin uniquement) --}}
    @if(auth()->user()?->isSuperAdmin())
        <div class="mb-4">
            {{ $this->createEventAction }}
        </div>
    @endif

    {{-- Widget calendrier Livewire --}}
    <x-filament-widgets::widgets
        :widgets="$this->getHeaderWidgets()"
        :columns="$this->getHeaderWidgetsColumns()"
    />
</x-filament-panels::page>
```

### Avantages

✅ **Zéro JavaScript personnalisé** - Tout est géré par Livewire  
✅ **Pas de problèmes de timezone** - PHP gère tout  
✅ **Intégration native Filament** - Utilise les modals/notifications Filament  
✅ **Mise à jour automatique** - Livewire refresh les événements  
✅ **Performance optimale** - Pas de jQuery/moment.js lourd  
✅ **Code propre et maintenable** - POO moderne  

---

## Option 2 : Calendrier Livewire Custom (Plus de contrôle)

Si vous voulez un contrôle total sans dépendance externe.

### Installation

```bash
composer require wire-elements/modal
php artisan livewire:make CalendarComponent
```

### Composant Livewire

**`app/Livewire/CalendarComponent.php`**

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Event;
use Carbon\Carbon;

class CalendarComponent extends Component
{
    public $currentDate;
    public $view = 'month'; // month, week, day
    public $events = [];
    public $selectedEvent = null;
    
    public function mount()
    {
        $this->currentDate = now();
        $this->loadEvents();
    }

    public function loadEvents()
    {
        $start = $this->getStartDate();
        $end = $this->getEndDate();
        
        $this->events = Event::query()
            ->visibleTo(auth()->user())
            ->whereBetween('start_at', [$start, $end])
            ->with(['user', 'creator'])
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start_at->format('Y-m-d H:i:s'),
                    'end' => ($event->end_at ?? $event->start_at)->format('Y-m-d H:i:s'),
                    'color' => $this->getEventColor($event->user_id),
                    'allDay' => (bool) $event->all_day,
                    'description' => $event->description,
                    'calendar' => $event->user?->name,
                ];
            })
            ->toArray();
    }

    public function changeView($view)
    {
        $this->view = $view;
        $this->loadEvents();
    }

    public function previousPeriod()
    {
        match($this->view) {
            'month' => $this->currentDate = $this->currentDate->subMonth(),
            'week' => $this->currentDate = $this->currentDate->subWeek(),
            'day' => $this->currentDate = $this->currentDate->subDay(),
        };
        
        $this->loadEvents();
    }

    public function nextPeriod()
    {
        match($this->view) {
            'month' => $this->currentDate = $this->currentDate->addMonth(),
            'week' => $this->currentDate = $this->currentDate->addWeek(),
            'day' => $this->currentDate = $this->currentDate->addDay(),
        };
        
        $this->loadEvents();
    }

    public function today()
    {
        $this->currentDate = now();
        $this->loadEvents();
    }

    public function selectEvent($eventId)
    {
        $this->selectedEvent = Event::find($eventId);
        $this->dispatch('open-modal', 'event-details');
    }

    protected function getStartDate()
    {
        return match($this->view) {
            'month' => $this->currentDate->copy()->startOfMonth()->startOfWeek(),
            'week' => $this->currentDate->copy()->startOfWeek(),
            'day' => $this->currentDate->copy()->startOfDay(),
        };
    }

    protected function getEndDate()
    {
        return match($this->view) {
            'month' => $this->currentDate->copy()->endOfMonth()->endOfWeek(),
            'week' => $this->currentDate->copy()->endOfWeek(),
            'day' => $this->currentDate->copy()->endOfDay(),
        };
    }

    protected function getEventColor(?int $userId): string
    {
        $palette = ['#4f6ba3', '#8b5cf6', '#ef4444', '#10b981', '#f59e0b'];
        return $userId ? $palette[$userId % count($palette)] : $palette[0];
    }

    public function render()
    {
        return view('livewire.calendar-component', [
            'days' => $this->getDaysForView(),
            'hours' => range(6, 22),
        ]);
    }

    protected function getDaysForView(): array
    {
        $start = $this->getStartDate();
        $end = $this->getEndDate();
        
        $days = [];
        $current = $start->copy();
        
        while ($current <= $end) {
            $days[] = [
                'date' => $current->copy(),
                'isCurrentMonth' => $current->month === $this->currentDate->month,
                'isToday' => $current->isToday(),
                'events' => collect($this->events)->filter(function ($event) use ($current) {
                    $eventStart = Carbon::parse($event['start']);
                    return $eventStart->isSameDay($current);
                }),
            ];
            
            $current->addDay();
        }
        
        return $days;
    }
}
```

### Vue Blade

**`resources/views/livewire/calendar-component.blade.php`**

```blade
<div class="calendar-container" wire:poll.30s="loadEvents">
    {{-- Toolbar --}}
    <div class="calendar-toolbar flex items-center justify-between mb-4 p-4 bg-white rounded-lg shadow">
        <div class="flex gap-2">
            <button wire:click="previousPeriod" class="btn-nav">←</button>
            <button wire:click="today" class="btn-today">Aujourd'hui</button>
            <button wire:click="nextPeriod" class="btn-nav">→</button>
        </div>
        
        <h2 class="text-xl font-bold text-gray-800">
            {{ $currentDate->locale('fr')->isoFormat('MMMM YYYY') }}
        </h2>
        
        <div class="flex gap-2">
            <button wire:click="changeView('month')" 
                    class="btn-view {{ $view === 'month' ? 'active' : '' }}">
                Mois
            </button>
            <button wire:click="changeView('week')" 
                    class="btn-view {{ $view === 'week' ? 'active' : '' }}">
                Semaine
            </button>
            <button wire:click="changeView('day')" 
                    class="btn-view {{ $view === 'day' ? 'active' : '' }}">
                Jour
            </button>
        </div>
    </div>

    {{-- Vue Mois --}}
    @if($view === 'month')
        <div class="calendar-grid grid grid-cols-7 gap-1 bg-white rounded-lg shadow p-4">
            {{-- En-têtes des jours --}}
            @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $day)
                <div class="calendar-header text-center font-semibold text-gray-600 py-2">
                    {{ $day }}
                </div>
            @endforeach
            
            {{-- Jours du mois --}}
            @foreach($days as $day)
                <div class="calendar-day min-h-[100px] border border-gray-200 p-2 rounded
                            {{ $day['isToday'] ? 'bg-blue-50 border-blue-400' : '' }}
                            {{ !$day['isCurrentMonth'] ? 'bg-gray-50 text-gray-400' : '' }}">
                    <div class="text-sm font-medium mb-1">
                        {{ $day['date']->format('d') }}
                    </div>
                    
                    {{-- Événements du jour --}}
                    @foreach($day['events']->take(3) as $event)
                        <div wire:click="selectEvent({{ $event['id'] }})"
                             class="text-xs p-1 mb-1 rounded cursor-pointer hover:opacity-80"
                             style="background-color: {{ $event['color'] }}; color: white;">
                            {{ $event['title'] }}
                        </div>
                    @endforeach
                    
                    @if($day['events']->count() > 3)
                        <div class="text-xs text-blue-600">
                            +{{ $day['events']->count() - 3 }} plus
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Vue Semaine/Jour (grille horaire) --}}
    @if(in_array($view, ['week', 'day']))
        <div class="calendar-time-grid bg-white rounded-lg shadow p-4 overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="border p-2 w-20">Heure</th>
                        @foreach($days as $day)
                            <th class="border p-2">
                                <div class="font-semibold">{{ $day['date']->format('D d') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($hours as $hour)
                        <tr>
                            <td class="border p-2 text-sm text-gray-600">
                                {{ sprintf('%02d:00', $hour) }}
                            </td>
                            @foreach($days as $day)
                                <td class="border p-2 relative h-16">
                                    @foreach($day['events'] as $event)
                                        @php
                                            $eventStart = \Carbon\Carbon::parse($event['start']);
                                            $eventHour = $eventStart->hour;
                                        @endphp
                                        
                                        @if($eventHour === $hour)
                                            <div wire:click="selectEvent({{ $event['id'] }})"
                                                 class="text-xs p-1 rounded cursor-pointer"
                                                 style="background-color: {{ $event['color'] }}; color: white;">
                                                {{ $eventStart->format('H:i') }} - {{ $event['title'] }}
                                            </div>
                                        @endif
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Loading indicator --}}
    <div wire:loading class="fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded shadow">
        Chargement...
    </div>
</div>

<style>
.btn-nav, .btn-today, .btn-view {
    @apply px-4 py-2 rounded border transition-colors;
}
.btn-nav { @apply border-gray-300 hover:bg-gray-100; }
.btn-today { @apply bg-blue-500 text-white hover:bg-blue-600; }
.btn-view { @apply border-gray-300 hover:bg-gray-100; }
.btn-view.active { @apply bg-blue-500 text-white border-blue-500; }
</style>
```

---

## Comparaison des Options

| Critère | Option 1 (Package) | Option 2 (Custom) |
|---------|-------------------|-------------------|
| **Installation** | 5 min | 30 min |
| **Code à écrire** | Minimal | Moyen |
| **Personnalisation** | Limitée | Totale |
| **Maintenance** | Package géré | Vous gérez |
| **Performance** | Optimisée | Dépend de vous |
| **Support** | Communauté | Vous-même |

---

## Recommandation Finale

### ⭐ **Pour votre cas : Option 1 (Filament Full Calendar Widget)**

**Pourquoi ?**
- ✅ Installation en 10 minutes
- ✅ Zéro problème de timezone
- ✅ Pas de jQuery/FullCalendar v3 obsolète
- ✅ Intégration native avec Filament
- ✅ Livewire = mise à jour temps réel
- ✅ Code maintenable et professionnel

---

## Migration depuis l'ancien code

### Étapes

1. **Installer le package**
```bash
composer require saade/filament-fullcalendar
```

2. **Créer le widget**
```bash
php artisan make:filament-widget CalendarWidget
```

3. **Remplacer la vue actuelle** par le widget

4. **Supprimer l'ancien code**
- Supprimer jQuery/FullCalendar v3
- Supprimer le JavaScript personnalisé
- Supprimer les routes AJAX complexes

5. **Tester** 🎉

---

## Support

Si vous voulez que je développe l'Option 1 (package) ou l'Option 2 (custom) pour votre projet, dites-moi et je créerai tous les fichiers nécessaires ! 🚀
