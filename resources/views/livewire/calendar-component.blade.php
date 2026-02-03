@php
    if (!function_exists('calendar_tint_color')) {
        function calendar_tint_color(string $hex, float $factor): string
        {
            $hex = ltrim($hex, '#');

            if (strlen($hex) === 3) {
                $hex = implode('', array_map(fn ($char) => str_repeat($char, 2), str_split($hex)));
            }

            if (strlen($hex) !== 6) {
                return '#4f6ba3';
            }

            $factor = max(0, min(1, $factor));

            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));

            $tint = static fn (int $channel) => (int) round($channel + (255 - $channel) * $factor);

            return sprintf('#%02x%02x%02x', $tint($r), $tint($g), $tint($b));
        }
    }
@endphp



<div class="calendar-livewire-container calendar-pro antialiased">
    {{-- Message flash --}}
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 text-green-700 dark:text-green-400 rounded-r-lg flex items-center gap-3 shadow-sm animate-slide-in">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('message') }}</span>
        </div>
    @endif

    {{-- Toolbar Style Google --}}
    <div class="calendar-toolbar d-flex flex-wrap align-items-center justify-content-between">
        <div class="flex items-center gap-3">
            <button type="button" wire:click="today" class="calendar-button">
                {{ __('calendar.today') }}
            </button>
            <div class="calendar-nav">
                <button type="button" wire:click="previousPeriod" aria-label="{{ __('calendar.previous_period') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button type="button" wire:click="nextPeriod" aria-label="{{ __('calendar.next_period') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <h2 class="text-xl md:text-2xl font-normal" style="color: var(--calendar-text);">
            {{ $currentDate->locale(app()->getLocale())->isoFormat('MMMM YYYY') }}
        </h2>
        
        <div class="flex items-center gap-3">
            @if($isSuperAdmin)
                <button type="button" wire:click="openCreateModal" class="calendar-button is-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('calendar.create') }}
                </button>
            @endif
            
            <div class="calendar-view-switch">
                <button type="button"
                        wire:click="changeView('month')"
                        aria-pressed="{{ $view === 'month' ? 'true' : 'false' }}"
                        class="{{ $view === 'month' ? 'is-active' : '' }}">
                    {{ __('calendar.view.month') }}
                </button>
                <button type="button"
                        wire:click="changeView('week')"
                        aria-pressed="{{ $view === 'week' ? 'true' : 'false' }}"
                        class="{{ $view === 'week' ? 'is-active' : '' }}">
                    {{ __('calendar.view.week') }}
                </button>
                <button type="button"
                        wire:click="changeView('day')"
                        aria-pressed="{{ $view === 'day' ? 'true' : 'false' }}"
                        class="{{ $view === 'day' ? 'is-active' : '' }}">
                    {{ __('calendar.view.day') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Vue Mois Style Google --}}
    @if($view === 'month')
    <div class="calendar-card overflow-hidden calendar-card--scrollable">
            @php
                $weeks = collect($days)->chunk(7);
            @endphp
            <table class="table table-bordered text-center calendar-month-table mb-0">
                <thead class="table-light">
                    <tr>
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $dayKey)
                            <th scope="col" class="calendar-header">{{ __('calendar.weekdays.' . $dayKey) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($weeks as $week)
                        <tr>
                            @foreach($week as $day)
                                <td class="calendar-day-cell align-top {{ $day['isToday'] ? 'is-today' : '' }} {{ !$day['isCurrentMonth'] ? 'is-outside' : '' }}">
                                    <div class="calendar-day-cell__inner">
                                        <div class="calendar-day-cell__header">
                                            <span class="calendar-date-badge">
                                                {{ $day['date']->format('d') }}
                                            </span>
                                        </div>

                                        @if($day['events']->count() > 0)
                                            <div class="calendar-day-count">
                                                <button type="button"
                                                        wire:click="openDayModal('{{ $day['date']->format('Y-m-d') }}')">
                                                    <strong>{{ $day['events']->count() }}</strong>
                                                    {{ trans_choice('calendar.event_label', $day['events']->count()) }}
                                                </button>
                                            </div>
                                        @endif

                                        <div class="calendar-day-cell__events">
                                            @foreach($day['events']->take(2) as $event)
                                                @php
                                                    $eventColor = $event['color'];
                                                    $eventSurface = calendar_tint_color($eventColor, 0.82);
                                                    $eventSurfaceAlt = calendar_tint_color($eventColor, 0.74);
                                                    $eventBorder = calendar_tint_color($eventColor, 0.6);
                                                @endphp
                                                <div wire:click="selectEvent({{ $event['id'] }})"
                                                     class="calendar-event calendar-event--compact truncate"
                                                     style="--event-color: {{ $eventColor }}; background: linear-gradient(180deg, {{ $eventSurface }} 0%, {{ $eventSurfaceAlt }} 100%); border-color: {{ $eventBorder }};">
                                                    <span class="calendar-event-dot" style="--event-color: {{ $eventColor }};"></span>
                                                    @if(!$event['allDay'])
                                                        <span class="calendar-event-time">{{ \Carbon\Carbon::parse($event['start'])->format('H:i') }}</span>
                                                    @endif
                                                    <span class="calendar-event-title truncate">{{ $event['title'] }}</span>
                                                </div>
                                            @endforeach

                                            @if($day['events']->count() > 2)
                                                <div class="calendar-day-cell__more">
                                                    @php
                                                        $remainingEvents = $day['events']->count() - 2;
                                                    @endphp
                                                    <button type="button" wire:click="openDayModal('{{ $day['date']->format('Y-m-d') }}')">
                                                        {{ trans_choice('calendar.more_events', $remainingEvents, ['count' => $remainingEvents]) }}
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Vue Semaine/Jour Style Google --}}
    @if(in_array($view, ['week', 'day']))
    <div class="calendar-time-grid calendar-card overflow-hidden calendar-card--scrollable">
            <table class="table table-bordered align-middle mb-0">
                    <thead class="sticky top-0" style="background: var(--calendar-panel);">
                        <tr>
                            <th class="calendar-time-label-cell is-header" style="background: rgba(79, 107, 163, .05);"></th>
                            @foreach($days as $day)
                                <th class="p-3 text-center min-w-[120px]" style="background: rgba(79, 107, 163, .05);">
                                    <div class="calendar-weekday-label">
                                        {{ $day['date']->locale(app()->getLocale())->isoFormat('ddd') }}
                                    </div>
                                    @if($day['isToday'])
                                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-full text-xl font-medium" style="background: var(--calendar-primary); color: #fff;">
                                            {{ $day['date']->format('d') }}
                                        </div>
                                    @else
                                        <div class="text-2xl font-light" style="color: var(--calendar-text);">
                                            {{ $day['date']->format('d') }}
                                        </div>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hours as $hour)
                            <tr class="group">
                                <td class="calendar-time-label-cell" style="background: rgba(79, 107, 163, .05);">
                                    <div class="calendar-time-label">
                                        {{ sprintf('%02d:00', $hour) }}
                                    </div>
                                </td>
                                @foreach($days as $day)
                                    <td class="p-1 relative h-16 {{ $day['isToday'] ? 'is-today' : '' }}">
                                        @foreach($day['events'] as $event)
                                            @php
                                                $eventStart = \Carbon\Carbon::parse($event['start']);
                                                $eventHour = $eventStart->hour;
                                            @endphp
                                            
                                            @if($eventHour === $hour && !$event['allDay'])
                                                @php
                                                    $blockBg = calendar_tint_color($event['color'], 0.78);
                                                    $blockBgAlt = calendar_tint_color($event['color'], 0.7);
                                                    $blockBorder = calendar_tint_color($event['color'], 0.55);
                                                @endphp
                                                <div wire:click="selectEvent({{ $event['id'] }})"
                                                     class="calendar-event calendar-event--block mb-1"
                                                     style="--event-color: {{ $event['color'] }}; background: linear-gradient(180deg, {{ $blockBg }} 0%, {{ $blockBgAlt }} 100%); border-color: {{ $blockBorder }};">
                                                    <span class="calendar-event-time">{{ $eventStart->format('H:i') }}</span>
                                                    <span class="calendar-event-title">{{ $event['title'] }}</span>
                                                    @if($event['calendar'])
                                                        <span class="calendar-event-meta truncate">{{ $event['calendar'] }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                        
                                        {{-- Événements toute la journée --}}
                                        @if($hour === 6)
                                            @foreach($day['events']->where('allDay', true) as $event)
                                                @php
                                                    $pillBg = calendar_tint_color($event['color'], 0.8);
                                                    $pillBgAlt = calendar_tint_color($event['color'], 0.72);
                                                    $pillBorder = calendar_tint_color($event['color'], 0.58);
                                                @endphp
                                                <div wire:click="selectEvent({{ $event['id'] }})"
                                                     class="calendar-event calendar-event--pill mb-1"
                                                     style="--event-color: {{ $event['color'] }}; background: linear-gradient(180deg, {{ $pillBg }} 0%, {{ $pillBgAlt }} 100%); border-color: {{ $pillBorder }};">
                                                    <span class="calendar-event-dot" style="--event-color: {{ $event['color'] }};"></span>
                                                    <span class="calendar-event-title truncate">{{ $event['title'] }}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
            </table>
        </div>
    @endif

    {{-- Loading indicator --}}
    <div wire:loading class="fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50">
        <div class="flex items-center gap-2">
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ __('calendar.loading') }}
        </div>
    </div>

    {{-- Modal Détails Événement Style Google --}}
    @if($showEventModal && $selectedEvent)
        <div class="modal-backdrop" wire:click="closeEventModal">
            <div class="modal-dialog modal-lg" role="dialog" aria-modal="true" wire:click.stop>
                <div class="modal-content">
                    <div class="modal-accent" style="background-color: {{ $selectedEvent['color'] }}"></div>

                    <div class="modal-header">
                        <h3 class="modal-title">
                            {{ $selectedEvent['title'] }}
                        </h3>
                        <button type="button" class="btn-close" wire:click="closeEventModal" aria-label="{{ __('calendar.close') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="calendar-detail-list">
                        <div class="calendar-detail-item">
                            <div class="calendar-detail-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="calendar-detail-content">
                                <div class="calendar-detail-title">{{ __('calendar.modal.event.schedule') }}</div>
                                <div class="calendar-detail-text">
                                    @if($selectedEvent['allDay'])
                                        {{ \Carbon\Carbon::parse($selectedEvent['start'])->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY') }} · {{ __('calendar.all_day') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($selectedEvent['start'])->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY') }}<br>
                                        {{ \Carbon\Carbon::parse($selectedEvent['start'])->format('H:i') }} – {{ \Carbon\Carbon::parse($selectedEvent['end'])->format('H:i') }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($selectedEvent['calendar'])
                            <div class="calendar-detail-item">
                                <div class="calendar-detail-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div class="calendar-detail-content">
                                    <div class="calendar-detail-title">{{ __('calendar.modal.event.calendar') }}</div>
                                    <div class="calendar-detail-text">{{ $selectedEvent['calendar'] }}</div>
                                </div>
                            </div>
                        @endif


                        @if(!empty($selectedEvent['description']))
                            <div class="calendar-detail-item">
                                <div class="calendar-detail-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                    </svg>
                                </div>
                                <div class="calendar-detail-content">
                                    <div class="calendar-detail-title">{{ __('calendar.modal.event.description') }}</div>
                                    <div class="calendar-detail-text whitespace-pre-wrap">{{ $selectedEvent['description'] }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $selectedEvent['color'] }}"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('calendar.event_type_label', ['type' => $selectedEvent['allDay'] ? __('calendar.event_type.all_day') : __('calendar.event_type.timed')]) }}
                            </span>
                        </div>
                        <button wire:click="closeEventModal"
                                class="px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                            {{ __('calendar.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Créer Événement Style Google --}}
    @if($showCreateModal)
        <div class="modal-backdrop" wire:click="closeCreateModal">
            <div class="modal-dialog" role="dialog" aria-modal="true" wire:click.stop>
                <div class="modal-content">
                    {{-- show currently selected color as the accent --}}
                    <div class="modal-accent" style="background-color: {{ $color ?? 'var(--calendar-primary)' }};"></div>

                    <div class="modal-header">
                        <h3 class="modal-title">{{ __('calendar.modal.create.title') }}</h3>
                        <button type="button" class="btn-close" wire:click="closeCreateModal" aria-label="{{ __('calendar.close') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="createEvent">
                        <div class="modal-body">
                            <div class="space-y-5">
                                <div>
                                    <label class="calendar-field-label">{{ __('calendar.modal.create.fields.title') }}</label>
                                    <input type="text" wire:model="title" required class="calendar-field-input">
                                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="calendar-form-grid">
                                    <div>
                                        <label class="calendar-field-label">{{ __('calendar.modal.create.fields.start') }}</label>
                                        <input type="datetime-local" wire:model="start_at" required class="calendar-field-input">
                                        @error('start_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="calendar-field-label">{{ __('calendar.modal.create.fields.end') }}</label>
                                        <input type="datetime-local" wire:model="end_at" class="calendar-field-input">
                                        @error('end_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="calendar-field-label">{{ __('calendar.modal.create.fields.pharmacy') }}</label>
                                    <select wire:model="user_id" class="calendar-field-select">
                                        <option value="">{{ __('calendar.modal.create.fields.pharmacy_placeholder') }}</option>
                                        @foreach($clientUsers as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="calendar-field-label">{{ __('calendar.modal.create.fields.description') }}</label>
                                    <textarea wire:model="description" class="calendar-field-textarea" placeholder="{{ __('calendar.modal.create.fields.description_placeholder') }}"></textarea>
                                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Color palette: allow choosing a color for the event --}}
                                <div>
                                    <label class="calendar-field-label">{{ __('calendar.modal.create.fields.color') ?? 'Couleur' }}</label>
                                    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                        @foreach($palette as $c)
                                            <button type="button"
                                                    wire:click="$set('color','{{ $c }}')"
                                                    title="{{ $c }}"
                                                    style="width:32px;height:32px;border-radius:999px;border:3px solid {{ isset($color) && $color === $c ? 'rgba(0,0,0,.12)' : 'transparent' }};background: {{ $c }};cursor:pointer;padding:0;"
                                                    aria-pressed="{{ isset($color) && $color === $c ? 'true' : 'false' }}">
                                            </button>
                                        @endforeach
                                        {{-- show custom color preview if set --}}
                                        <div style="margin-left:6px;display:inline-flex;align-items:center;gap:0.5rem">
                                            <div style="width:18px;height:18px;border-radius:999px;background: {{ $color ?? 'transparent' }};border:1px solid rgba(0,0,0,.06)"></div>
                                            <small style="color:var(--calendar-muted);">
                                                {{ $color ?? __('calendar.modal.create.fields.color_none') ?? 'Aucune' }}
                                            </small>
                                        </div>
                                    </div>
                                    <input type="hidden" wire:model="color">
                                    @error('color') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" wire:click="closeCreateModal"
                                    class="px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                                {{ __('calendar.modal.create.actions.cancel') }}
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 text-sm font-medium text-[#4f6ba3] bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
                                {{ __('calendar.modal.create.actions.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Liste des événements d'une journée --}}
    @if($showDayModal && $dayModalDate)
        <div class="modal-backdrop" wire:click="closeDayModal">
            <div class="modal-dialog modal-lg" role="dialog" aria-modal="true" wire:click.stop>
                <div class="modal-content">
                    <div class="modal-accent" style="background-color: var(--calendar-primary);"></div>

                    <div class="modal-header align-items-start">
                        <div class="d-flex flex-column gap-2">
                            <h4 class="modal-title mb-0">
                                {{ $dayModalDate->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY') }}
                            </h4>
                            <span class="text-sm text-gray-500 dark:text-gray-300">
                                {{ trans_choice('calendar.events_summary', count($dayModalEvents), ['count' => count($dayModalEvents)]) }}
                            </span>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeDayModal" aria-label="{{ __('calendar.close') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="calendar-day-modal__timeline">
                        @forelse($dayModalEvents as $event)
                            @php
                                $eventColor = $event['color'];
                                $eventSurface = calendar_tint_color($eventColor, 0.9);
                                $eventSurfaceAlt = calendar_tint_color($eventColor, 0.82);
                                $eventBorder = calendar_tint_color($eventColor, 0.6);
                            @endphp
                            <div class="calendar-day-modal__item"
                                 style="--event-color: {{ $eventColor }}; background: linear-gradient(180deg, {{ $eventSurface }} 0%, {{ $eventSurfaceAlt }} 100%); border-color: {{ $eventBorder }};"
                                 wire:click="selectEvent({{ $event['id'] }})">
                                <span class="calendar-day-modal__bullet"></span>
                                <div class="calendar-day-modal__time">
                                    @if($event['allDay'])
                                        {{ __('calendar.all_day') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($event['start'])->format('H:i') }}
                                        @if($event['end'])
                                            &nbsp;–&nbsp;{{ \Carbon\Carbon::parse($event['end'])->format('H:i') }}
                                        @endif
                                    @endif
                                </div>
                                <div class="calendar-day-modal__title">
                                    {{ $event['title'] }}
                                </div>
                                @if(!empty($event['calendar']))
                                    <div class="calendar-day-modal__meta">
                                        {{ $event['calendar'] }}
                                    </div>
                                @endif
                                @if(!empty($event['description']))
                                    <div class="mt-2 text-sm" style="color: var(--calendar-text); opacity: .75;">
                                        {{ \Illuminate\Support\Str::limit($event['description'], 140) }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="calendar-day-modal__empty">
                                {{ __('calendar.day_modal.empty') }}
                            </div>
                        @endforelse
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button wire:click="closeDayModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                            {{ __('calendar.close') }}
                        </button>
                        @if($isSuperAdmin)
                            <button wire:click="openCreateModalForDate('{{ $dayModalDate->format('Y-m-d') }}')"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
                                {{ __('calendar.day_modal.add_event') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>




@once
    @push('styles')
        <style>
            .calendar-pro {
                --calendar-primary: #4f6ba3;
                --calendar-surface: #f3f8fb;
                --calendar-panel: #ffffff;
                --calendar-border: #e5ecf4;
                --calendar-border-strong: #d0dfed;
                --calendar-shadow: 0 30px 60px rgba(79, 107, 163, .14);
                --calendar-shadow-sm: 0 18px 36px rgba(79, 107, 163, .12);
                --calendar-text: #1f2937;
                --calendar-muted: #556080;
                --calendar-soft: rgba(79, 107, 163, .1);
            }

            .dark .calendar-pro {
                --calendar-surface: #0b1220;
                --calendar-panel: #0f172a;
                --calendar-border: #1f2937;
                --calendar-border-strong: #22304b;
                --calendar-shadow: 0 36px 70px rgba(8, 15, 31, .55);
                --calendar-shadow-sm: 0 24px 50px rgba(8, 15, 31, .45);
                --calendar-text: #e5e7eb;
                --calendar-muted: #9ca3af;
                --calendar-soft: rgba(79, 107, 163, .18);
            }

            .calendar-pro {
                background: var(--calendar-surface);
                border-radius: 20px;
                padding: 1.75rem;
                border: 1px solid var(--calendar-border);
                box-shadow: var(--calendar-shadow);
            }

            .d-flex {
                display: flex;
            }

            .flex-wrap {
                flex-wrap: wrap;
            }

            .flex-column {
                flex-direction: column;
            }

            .align-items-center {
                align-items: center;
            }

            .align-items-start {
                align-items: flex-start;
            }

            .justify-content-between {
                justify-content: space-between;
            }

            .gap-2 {
                gap: 0.5rem;
            }

            .gap-3 {
                gap: 0.75rem;
            }

            .mb-0 {
                margin-bottom: 0;
            }

            .space-y-5 > * + * {
                margin-top: 1.25rem;
            }

            .calendar-toolbar {
                background: var(--calendar-panel);
                border: 1px solid var(--calendar-border);
                border-radius: 16px;
                padding: 0.85rem 1.1rem;
                box-shadow: var(--calendar-shadow-sm);
                width: 100%;
                margin-bottom: 1.5rem;
                gap: 1rem;
            }

            .calendar-card {
                background: var(--calendar-panel);
                border: 1px solid var(--calendar-border);
                border-radius: 18px;
                box-shadow: var(--calendar-shadow-sm);
                width: 100%;
            }

            .calendar-month-grid {
                display: grid;
                grid-template-columns: repeat(7, minmax(0, 1fr));
            }

            .calendar-month-table thead th {
                text-transform: uppercase;
                font-size: 0.82rem;
                letter-spacing: .08em;
                font-weight: 700;
                color: var(--calendar-muted);
                background: rgba(79, 107, 163, .08);
                padding: 0.85rem 0.5rem;
            }

            .calendar-month-table tbody td {
                vertical-align: top;
                padding: 0.85rem;
                min-height: 110px;
                background: var(--calendar-panel);
            }

            .calendar-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.45rem;
                font-size: 0.85rem;
                font-weight: 500;
                border-radius: 10px;
                padding: 0.55rem 1rem;
                border: 1px solid var(--calendar-border);
                background: var(--calendar-panel);
                color: var(--calendar-muted);
                transition: all .18s ease;
            }

            .calendar-button:hover {
                border-color: var(--calendar-primary);
                color: var(--calendar-primary);
                box-shadow: 0 12px 20px rgba(79, 107, 163, .18);
            }

            .calendar-button.is-primary {
                background: var(--calendar-primary);
                color: #fff;
                border-color: var(--calendar-primary);
                box-shadow: 0 14px 26px rgba(79, 107, 163, .25);
            }

            .calendar-button.is-primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 18px 32px rgba(79, 107, 163, .3);
            }

            .calendar-button.is-active {
                background: var(--calendar-soft);
                color: var(--calendar-primary);
                border-color: rgba(79, 107, 163, .35);
                box-shadow: none;
            }

            .calendar-nav {
                display: inline-flex;
                align-items: center;
                border-radius: 12px;
                border: 1px solid var(--calendar-border);
                overflow: hidden;
                background: var(--calendar-panel);
            }

            .calendar-nav button {
                width: 2.5rem;
                height: 2.5rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: var(--calendar-muted);
                transition: all .18s ease;
            }

            .calendar-nav button:hover {
                background: var(--calendar-soft);
                color: var(--calendar-primary);
            }

            .calendar-view-switch {
                display: inline-flex;
                border-radius: 12px;
                overflow: hidden;
                border: 1px solid var(--calendar-border);
                background: var(--calendar-panel);
                box-shadow: var(--calendar-shadow-sm);
            }

            .calendar-view-switch button {
                border: none;
                padding: 0.55rem 1.1rem;
                font-weight: 500;
                color: var(--calendar-muted);
                background: transparent;
                transition: all .18s ease;
            }

            .calendar-view-switch button:hover {
                background: var(--calendar-soft);
                color: var(--calendar-primary);
            }

            .calendar-view-switch button.is-active {
                background: var(--calendar-primary);
                color: #fff;
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .18);
            }

            .calendar-header {
                border-bottom: 1px solid var(--calendar-border-strong);
                background: linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(255, 255, 255, 0));
                font-weight: 700;
                letter-spacing: .08em;
                text-transform: uppercase;
                color: var(--calendar-muted);
                font-size: 0.82rem;
            }

            .calendar-weekday-label {
                font-size: 0.85rem;
                font-weight: 700;
                letter-spacing: .08em;
                text-transform: uppercase;
                color: var(--calendar-muted);
                margin-bottom: 0.35rem;
            }

            .calendar-time-label-cell {
                width: 4.25rem;
                padding: 0.5rem 0.75rem;
                text-align: center;
                vertical-align: middle;
                color: var(--calendar-muted);
            }

            .calendar-time-label-cell.is-header {
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }

            .calendar-time-label {
                font-size: 0.82rem;
                font-weight: 600;
                letter-spacing: .04em;
            }

            .calendar-day-cell {
                position: relative;
                background: var(--calendar-panel);
                transition: background .18s ease, box-shadow .18s ease;
                border: none;
            }

            .calendar-day-cell:hover {
                background: rgba(79, 107, 163, .05);
            }

            .calendar-day-cell.is-outside {
                background: rgba(244, 246, 249, .45);
                color: rgba(31, 41, 55, .55);
            }

            .dark .calendar-day-cell.is-outside {
                background: rgba(17, 24, 39, .45);
                color: rgba(209, 213, 219, .55);
            }

            .calendar-day-cell.is-today {
                background: rgba(79, 107, 163, .08);
                box-shadow: inset 0 0 0 1px rgba(79, 107, 163, .25);
            }

            .calendar-date-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.2rem;
                height: 2.2rem;
                border-radius: 999px;
                font-weight: 600;
                font-size: 0.9rem;
                color: var(--calendar-muted);
            }

            .calendar-day-cell.is-today .calendar-date-badge {
                background: var(--calendar-primary);
                color: #fff;
            }

            .calendar-event {
                position: relative;
                border-radius: 12px;
                padding: 0.4rem 0.65rem;
                font-size: 0.7rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 0.4rem;
                border: 1px solid rgba(79, 107, 163, .24);
                background: var(--calendar-soft);
                color: var(--calendar-text);
                box-shadow: 0 12px 22px rgba(10, 16, 31, .16);
                cursor: pointer;
                transition: transform .12s ease, box-shadow .18s ease;
            }

            .calendar-event:hover {
                transform: translateY(-1px);
                box-shadow: 0 18px 28px rgba(10, 16, 31, .22);
            }

            .calendar-event-dot {
                width: 0.45rem;
                height: 0.45rem;
                border-radius: 999px;
                background: var(--event-color, rgba(79, 107, 163, .8));
                flex-shrink: 0;
            }

            .calendar-event-title {
                flex: 1;
                min-width: 0;
            }

            .calendar-event-time {
                color: var(--event-color, var(--calendar-primary));
                font-weight: 700;
                font-size: 0.72rem;
            }

            .calendar-event--block {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .calendar-event--block .calendar-event-dot {
                display: none;
            }

            .calendar-event--block .calendar-event-time {
                font-size: 0.75rem;
            }

            .calendar-event-meta {
                font-size: 0.65rem;
                color: var(--calendar-muted);
                font-weight: 500;
            }

            .calendar-event--compact {
                padding: 0.35rem 0.55rem;
                font-size: 0.68rem;
            }

            .calendar-event--pill {
                padding: 0.4rem 0.6rem;
                font-size: 0.7rem;
            }

            .calendar-day-count {
                position: absolute;
                top: 0.65rem;
                right: 0.65rem;
                font-size: 0.6rem;
                font-weight: 600;
                letter-spacing: 0.05em;
                color: var(--calendar-muted);
            }

            .calendar-day-count button {
                display: inline-flex;
                align-items: center;
                gap: 0.2rem;
                padding: 0.15rem 0.45rem;
                border-radius: 999px;
                border: 1px solid var(--calendar-border);
                background: rgba(79, 107, 163, .08);
                color: var(--calendar-primary);
                transition: all .18s ease;
            }

            .calendar-day-count button:hover {
                background: rgba(79, 107, 163, .12);
                border-color: rgba(79, 107, 163, .4);
            }

            .calendar-day-count strong {
                font-weight: 700;
            }

            .calendar-day-cell__inner {
                position: relative;
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                min-height: 100px;
            }

            .calendar-day-cell__header {
                display: flex;
                justify-content: flex-start;
            }

            .calendar-day-cell__events {
                display: flex;
                flex-direction: column;
                gap: 0.3rem;
            }

            .calendar-day-cell__more button {
                font-size: 0.68rem;
                font-weight: 600;
                background: none;
                border: none;
                padding: 0;
                color: var(--calendar-muted);
                text-decoration: underline;
                text-decoration-color: rgba(79, 107, 163, .3);
                cursor: pointer;
            }

            .calendar-day-cell__more button:hover {
                color: var(--calendar-primary);
            }

            .calendar-day-modal__header {
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
            }

            .calendar-day-modal__header h4 {
                font-size: 1.35rem;
                font-weight: 600;
                margin: 0;
                color: var(--calendar-text);
            }

            .calendar-day-modal__header span {
                font-size: 0.9rem;
                color: var(--calendar-muted);
            }

            .calendar-day-modal__timeline {
                margin-top: 1.5rem;
                position: relative;
                padding-left: 1.75rem;
            }

            .calendar-day-modal__timeline::before {
                content: '';
                position: absolute;
                top: 0.3rem;
                bottom: 0.5rem;
                left: 0.55rem;
                width: 2px;
                background: rgba(79, 107, 163, .2);
            }

            .calendar-day-modal__item {
                position: relative;
                padding: 0.65rem 0.85rem 0.65rem 0.75rem;
                border-radius: 14px;
                border: 1px solid var(--calendar-border);
                background: var(--calendar-panel);
                box-shadow: var(--calendar-shadow-sm);
                margin-bottom: 0.9rem;
                transition: transform .18s ease, box-shadow .18s ease;
            }

            .calendar-day-modal__item:hover {
                transform: translateX(4px);
                box-shadow: 0 18px 26px rgba(10, 16, 31, .18);
            }

            .calendar-day-modal__bullet {
                position: absolute;
                top: 1rem;
                left: -1.2rem;
                width: 0.75rem;
                height: 0.75rem;
                border-radius: 999px;
                background: var(--event-color, var(--calendar-primary));
                box-shadow: 0 0 0 4px rgba(79, 107, 163, .18);
            }

            .calendar-day-modal__time {
                font-size: 0.85rem;
                font-weight: 700;
                color: var(--event-color, var(--calendar-primary));
            }

            .calendar-day-modal__title {
                font-size: 1rem;
                font-weight: 600;
                color: var(--calendar-text);
                margin-top: 0.3rem;
            }

            .calendar-day-modal__meta {
                font-size: 0.8rem;
                color: var(--calendar-muted);
                margin-top: 0.2rem;
            }

            .calendar-day-modal__empty {
                font-size: 0.9rem;
                color: var(--calendar-muted);
                text-align: center;
                padding: 1.5rem 1rem;
            }

            .calendar-field-label {
                display: block;
                font-size: 0.82rem;
                text-transform: uppercase;
                letter-spacing: .08em;
                font-weight: 600;
                color: var(--calendar-muted);
                margin-bottom: 0.45rem;
            }

            .calendar-field-input,
            .calendar-field-select,
            .calendar-field-textarea {
                width: 100%;
                border-radius: 12px;
                border: 1px solid var(--calendar-border);
                background: var(--calendar-panel);
                padding: 0.75rem 1rem;
                font-size: 0.95rem;
                color: var(--calendar-text);
                transition: border-color .18s ease, box-shadow .18s ease;
            }

            .calendar-field-input:focus,
            .calendar-field-select:focus,
            .calendar-field-textarea:focus {
                outline: none;
                border-color: var(--calendar-primary);
                box-shadow: 0 0 0 3px rgba(79, 107, 163, .18);
            }

            .calendar-field-textarea {
                min-height: 120px;
                resize: vertical;
            }

            .calendar-form-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 1rem;
            }

            .calendar-form-actions {
                display: flex;
                justify-content: flex-end;
                gap: 0.75rem;
                margin-top: 1.5rem;
            }

            .calendar-detail-list {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .calendar-detail-item {
                display: flex;
                gap: 0.9rem;
                align-items: flex-start;
            }

            .calendar-detail-icon {
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 16px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: rgba(79, 107, 163, .12);
                color: var(--calendar-primary);
                flex-shrink: 0;
            }

            .calendar-detail-content {
                flex: 1;
            }

            .calendar-detail-title {
                font-size: 0.88rem;
                font-weight: 600;
                color: var(--calendar-muted);
                text-transform: uppercase;
                letter-spacing: .06em;
                margin-bottom: 0.3rem;
            }

            .calendar-detail-text {
                font-size: 0.98rem;
                color: var(--calendar-text);
            }

            .calendar-time-grid table {
                border-collapse: collapse;
                width: 100%;
            }

            .table {
                width: 100%;
                margin-bottom: 0;
                background-color: transparent;
            }

            .table-bordered th,
            .table-bordered td,
            .calendar-time-grid th,
            .calendar-time-grid td {
                border: 1px solid var(--calendar-border);
            }

            .align-middle th,
            .align-middle td {
                vertical-align: middle;
            }

            .calendar-time-grid td {
                background: var(--calendar-panel);
                transition: background .18s ease;
            }

            .calendar-time-grid td:hover {
                background: rgba(79, 107, 163, .05);
            }

            .calendar-time-grid td.is-today {
                background: rgba(79, 107, 163, .08);
            }

            .calendar-card--scrollable {
                overflow-x: auto;
                overscroll-behavior-x: contain;
                -webkit-overflow-scrolling: touch;
            }

            .calendar-card--scrollable table {
                min-width: 720px;
            }

            .calendar-time-grid table {
                min-width: 820px;
            }

            @media (max-width: 1200px) {
                .calendar-card--scrollable table {
                    min-width: 660px;
                }

                .calendar-time-grid table {
                    min-width: 760px;
                }
            }

            @media (max-width: 1023px) {
                .calendar-pro {
                    padding: 1.25rem;
                }

                .calendar-toolbar {
                    flex-direction: column;
                    align-items: stretch;
                    text-align: center;
                }

                .calendar-toolbar > * {
                    width: 100%;
                }

                .calendar-toolbar > div:first-child,
                .calendar-toolbar > div:last-child {
                    justify-content: center;
                }

                .calendar-toolbar h2 {
                    order: 2;
                    margin-top: 0.5rem;
                    margin-bottom: 0.75rem;
                }

                .calendar-view-switch {
                    width: 100%;
                }

                .calendar-view-switch button {
                    flex: 1;
                }

                .calendar-day-cell__inner {
                    min-height: 90px;
                }

                .calendar-day-count {
                    top: 0.5rem;
                    right: 0.5rem;
                }
            }

            @media (max-width: 768px) {
                .calendar-toolbar {
                    gap: 0.75rem;
                }

                .calendar-nav {
                    width: 100%;
                    justify-content: center;
                }

                .calendar-nav button {
                    width: 2.25rem;
                    height: 2.25rem;
                }

                .calendar-view-switch {
                    flex-direction: column;
                }

                .calendar-view-switch button {
                    width: 100%;
                }

                .calendar-month-table tbody td {
                    padding: 0.65rem;
                    min-height: 90px;
                }

                .calendar-day-cell__inner {
                    gap: 0.4rem;
                }

                .calendar-card--scrollable {
                    border-radius: 16px;
                }
            }

            @media (max-width: 640px) {
                .calendar-pro {
                    padding: 1rem;
                }

                .calendar-toolbar {
                    padding: 0.75rem;
                }

                .calendar-button {
                    width: 100%;
                }

                .calendar-button.is-primary {
                    min-height: 2.75rem;
                }

                .calendar-day-count button {
                    padding: 0.15rem 0.35rem;
                }

                .calendar-day-cell__inner {
                    min-height: 80px;
                }

                .calendar-day-cell__events {
                    gap: 0.25rem;
                }
            }

            .modal-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(6px);
                -webkit-backdrop-filter: blur(6px);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                z-index: 50;
            }

            @media (min-width: 768px) {
                .modal-backdrop {
                    padding: 1.5rem;
                }
            }

            .modal-dialog {
                width: 100%;
                max-width: 32rem;
                margin: auto;
                display: flex;
                align-items: center;
                justify-content: center;
                pointer-events: none;
            }

            .modal-dialog.modal-lg {
                max-width: 44rem;
            }

            .modal-content {
                background: var(--calendar-panel);
                border: 1px solid var(--calendar-border);
                border-radius: 22px;
                box-shadow: var(--calendar-shadow);
                display: flex;
                flex-direction: column;
                max-height: 92vh;
                overflow: hidden;
                pointer-events: auto;
            }

            .modal-content form {
                display: flex;
                flex-direction: column;
                height: 100%;
                min-height: 0;
            }

            .modal-accent {
                height: 0.35rem;
                width: 100%;
            }

            .modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1.25rem 1.5rem;
                border-bottom: 1px solid var(--calendar-border);
            }

            .modal-title {
                margin: 0;
                font-size: 1.4rem;
                font-weight: 600;
                color: var(--calendar-text);
            }

            .btn-close {
                border: none;
                background: transparent;
                color: var(--calendar-muted);
                border-radius: 999px;
                padding: 0.4rem;
                line-height: 1;
                transition: background .18s ease, color .18s ease;
            }

            .btn-close:hover {
                background: rgba(79, 107, 163, .1);
                color: var(--calendar-text);
            }

            .btn-close:focus {
                outline: none;
                box-shadow: 0 0 0 3px rgba(79, 107, 163, .25);
            }

            .modal-body {
                padding: 1.5rem;
                overflow-y: auto;
                flex: 1 1 auto;
                min-height: 0;
            }

            .modal-footer {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 0.75rem;
                padding: 1rem 1.5rem;
                border-top: 1px solid var(--calendar-border);
                background: rgba(79, 107, 163, .04);
            }

            @media (max-width: 640px) {
                .modal-backdrop {
                    align-items: flex-start;
                }

                .modal-dialog {
                    max-width: calc(100% - 1rem);
                    height: 100%;
                    align-items: flex-start;
                }

                .modal-content {
                    border-radius: 18px;
                    max-height: calc(100vh - 2rem);
                    margin-top: 1rem;
                }
            }
        </style>
    @endpush
@endonce




