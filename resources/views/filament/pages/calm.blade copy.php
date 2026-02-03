@php
    $eventsJson = json_encode($events ?? []);
    $calendarsJson = json_encode($calendars ?? []);
    $user = auth()->user();
    $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
    // Fetch client users for the inline form select (only id + name)
    $clientUsers = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'client'))
        ->orderBy('name')
        ->get(['id', 'name']);
@endphp

<x-filament::page>
    <div style="display:flex; flex-direction:column; gap:12px;">
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button id="btnCreateEvent" style="padding:8px 12px; background:#4f6ba3; color:white; border:none; border-radius:6px; cursor:pointer; display:{{ $isSuperAdmin ? 'inline-flex' : 'none' }};">{{ __('filament.pages.calendar.create_event') }}</button>
        </div>
        <div id="calendar"></div>
    </div>

    <!-- Modal inline: create event (no iframe) -->
    <div id="eventModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color:#fff; margin:5% auto; padding:12px; border-radius:10px; width:95%; max-width:720px; position:relative; overflow:auto;">
            <div style="display:flex; align-items:center; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #eee; margin-bottom:8px;">
                <h3 style="margin:0; font-size:16px;">Nouvel événement</h3>
                <span class="close" style="color:#6b7280; font-size:24px; font-weight:bold; cursor:pointer;">&times;</span>
            </div>
            <form id="eventForm">
                <div style="display:flex; gap:8px; margin-bottom:8px;">
                    <input name="title" placeholder="Titre" style="flex:1; padding:8px; border:1px solid #e5e7eb; border-radius:6px;" />
                    <select name="user_id" style="width:260px; padding:8px; border:1px solid #e5e7eb; border-radius:6px;">
                        <option value="">Choisis une pharmacie</option>
                        @foreach($clientUsers as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; gap:8px; margin-bottom:8px;">
                    <input name="start_at" type="datetime-local" step="1" style="padding:8px; border:1px solid #e5e7eb; border-radius:6px;" />
                    <input name="end_at" type="datetime-local" step="1" style="padding:8px; border:1px solid #e5e7eb; border-radius:6px;" />
                </div>
                <div style="margin-bottom:8px;"><textarea name="description" placeholder="Description" rows="4" style="width:100%; padding:8px; border:1px solid #e5e7eb; border-radius:6px;"></textarea></div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button type="button" id="eventSubmit" style="padding:8px 12px; background:#4f6ba3; color:white; border:none; border-radius:6px;">Créer</button>
                    <button type="button" id="eventCancel" style="padding:8px 12px; background:#ef4444; color:white; border:none; border-radius:6px;">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal inline: create note -->
    <div id="noteModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color:#fff; margin:5% auto; padding:12px; border-radius:10px; width:95%; max-width:720px; position:relative; overflow:auto;">
            <div style="display:flex; align-items:center; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #eee; margin-bottom:8px;">
                <h3 style="margin:0; font-size:16px;">Ajouter note</h3>
                <span class="close-note" style="color:#6b7280; font-size:24px; font-weight:bold; cursor:pointer;">&times;</span>
            </div>
            <form id="noteForm">
                <div style="margin-bottom:8px;"><input name="title" placeholder="Titre" style="width:100%; padding:8px; border:1px solid #e5e7eb; border-radius:6px;" /></div>
                <div style="margin-bottom:8px;"><textarea name="content" placeholder="Contenu" rows="4" style="width:100%; padding:8px; border:1px solid #e5e7eb; border-radius:6px;"></textarea></div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button type="button" id="noteSubmit" style="padding:8px 12px; background:#4f6ba3; color:white; border:none; border-radius:6px;">Ajouter</button>
                    <button type="button" id="noteCancel" style="padding:8px 12px; background:#ef4444; color:white; border:none; border-radius:6px;">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</x-filament::page>

@push('styles')
<!-- FullCalendar v3 CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
<style>
    #calendar { min-height: 720px; background: #fff; padding: 16px; border-radius: 8px; }

    /* Dark mode adjustments */
    .dark #calendar { background: #111827; color: #e5e7eb; }
    .dark .tippy-box[data-theme~='light-border'] { background-color: #1f2937; color: #e5e7eb; border: 1px solid #374151; }
    .dark .modal-content { background-color: #1f2937; color: #e5e7eb; }
    
    /* FullCalendar v3 custom styles */
    .fc-event { font-size: 13px; border-radius: 4px; cursor: pointer; }
    .fc-toolbar h2 { font-size: 1.5em; }
    
    /* Modal styles */
    .modal { font-family: Arial, sans-serif; }
    .modal-content { box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
    .close:hover { color: #000; text-decoration: none; }

    /* Filters / Legend chips */
    .cal-chip { display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border:1px solid #e5e7eb; border-radius:999px; background:#f9fafb; cursor:pointer; user-select:none; }
    .cal-chip.active { border-color:#4f6ba3; background:#eef2fb; }
        .cal-dot { width:10px; height:10px; border-radius:999px; display:inline-block; }
        .cal-filters { padding:6px 4px; }

    /* Highlight only the 'today' cell in month/day views using project primary color */
    td.fc-day.fc-today,
    td.fc-day.fc-widget-content.fc-today {
        color: #4f6ba3 !important; /* text color */
        border: 1px solid rgba(79,107,163,0.45) !important; /* subtle border using primary */
        background: #88a2d7ff !important; /* faint background tint */
    }
    /* Dark mode tweak */
    .dark td.fc-day.fc-today,
    .dark td.fc-day.fc-widget-content.fc-today {
        color: #dbe8ff !important;
        border-color: rgba(79,107,163,0.25) !important;
        background: rgba(79,107,163,0.08) !important;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .modal-content { margin: 10% auto; width: 95%; }
    }

    /* Make agenda day/week time grid scrollable */
    .fc-agendaWeek-view .fc-time-grid .fc-scroller,
    .fc-agendaDay-view .fc-time-grid .fc-scroller {
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        max-height: 650px; /* matches contentHeight below */
    }
</style>
@endpush

@push('scripts')
<!-- jQuery et dépendances pour FullCalendar v3 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<!-- FullCalendar v3 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>
<!-- Locale français pour FullCalendar v3 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/locale/fr.js"></script>
<!-- Tippy.js pour tooltips -->
<script src="https://cdn.jsdelivr.net/npm/tippy.js@6.3.7/dist/tippy-bundle.umd.min.js"></script>

<script>
$(document).ready(function() {
    // CSRF
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}' }
    });

    const events = @json($events ?? []); // initial, but we'll fetch dynamically
    const calendars = @json($calendars ?? []);
    const btnCreateEvent = $('#btnCreateEvent');
    const btnCreateNote = $('#btnCreateNote');

    // Modal refs (create)
    const modal = $('#eventModal');
    const eventIframe = $('#eventIframe');
    const closeBtn = $('.close');

    // Couleurs cohérentes avec le backend (project primary color first)
    const palette = ['#4f6ba3', '#8b5cf6', '#ef4444', '#10b981', '#f59e0b', '#06b6d4', '#ec4899', '#84cc16'];
        function getEventColor(userId) {
            if (userId === null || userId === undefined) return palette[0];
            const idx = Math.abs(parseInt(userId, 10)) % palette.length;
            return palette[idx];
        }

        // No filters: show all events for all users by default

        function escapeHtml(s) {
            return String(s || '').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
        }

    // Details modal (read-only)
    const detailsHtml = `
    <div id="eventDetailsModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color:#fff; margin:12% auto; padding:18px; border-radius:8px; width:90%; max-width:460px; position:relative;">
            <span class="close details-close" style="color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer;">&times;</span>
            <h3 id="detailsTitle" style="margin:0 0 10px;"></h3>
            <div id="detailsDate" style="color:#6b7280; margin-bottom:6px;"></div>
            <div id="detailsCalendar" style="color:#6b7280; margin-bottom:6px;"></div>
            <div id="detailsCreator" style="color:#6b7280; margin-bottom:10px;"></div>
            <div id="detailsDescription" style="white-space:pre-wrap;"></div>
        </div>
    </div>`;
    $('body').append(detailsHtml);
    const detailsModal = $('#eventDetailsModal');
    const detailsClose = $('.details-close');
    const detailsTitle = $('#detailsTitle');
    const detailsDate = $('#detailsDate');
    const detailsCalendar = $('#detailsCalendar');
    const detailsCreator = $('#detailsCreator');
    const detailsDescription = $('#detailsDescription');

    // Le formulaire est désormais dans l'iframe Filament

    function openCreateModalWithDate(start, end) {
        const s = (start || moment()).clone().format('YYYY-MM-DD');
        const e = (end || start || moment()).clone().format('YYYY-MM-DD');
        const url = `/admin/iframe/calendar/events/create?start_at=${s}T00:00&end_at=${e}T23:59&all_day=1&in_iframe=1`;
        eventIframe.attr('src', url);
        modal.show();
    }
    function openCreateNoteModal() {
        const url = `/admin/iframe/calendar/notes/create?in_iframe=1`;
        eventIframe.attr('src', url);
        modal.show();
    }
    function closeModal() { modal.hide(); eventIframe.attr('src', 'about:blank'); }
    closeBtn.on('click', closeModal);
    $(window).on('click', function(e) { if (e.target === modal[0]) closeModal(); });

    // Wire create buttons to open inline modals
    try {
        btnCreateEvent.on('click', function() { openEventInline(); });
        btnCreateNote.on('click', function() { openNoteInline(); });
    } catch (_) {}

    // Inline modal functions
    const eventModalEl = $('#eventModal');
    const noteModalEl = $('#noteModal');
    const eventCancel = $('#eventCancel');
    const noteCancel = $('#noteCancel');

    function openEventInline(start, end) {
        // prefill dates if provided
        if (start) eventModalEl.find('input[name="start_at"]').val(start);
        if (end) eventModalEl.find('input[name="end_at"]').val(end);
        eventModalEl.show();
    }
    function openNoteInline() { noteModalEl.show(); }

    eventCancel.on('click', function() { eventModalEl.hide(); eventModalEl.find('form')[0].reset(); });
    noteCancel.on('click', function() { noteModalEl.hide(); noteModalEl.find('form')[0].reset(); });

    // Submit event form via AJAX
    $('#eventSubmit').on('click', function() {
        const $form = $('#eventForm');
        let startVal = $form.find('input[name="start_at"]').val();
        let endVal = $form.find('input[name="end_at"]').val();

        // If browser provided datetime-local, value is like 'YYYY-MM-DDTHH:MM:SS'
        // Ensure seconds exist; if not, append ':00'
        function normalizeDt(v) {
            if (!v) return v;
            // if contains 'T' but no seconds, add ':00'
            const parts = v.split('T');
            if (parts.length === 2 && parts[1].split(':').length === 2) {
                return v + ':00';
            }
            return v;
        }

        startVal = normalizeDt(startVal);
        endVal = normalizeDt(endVal);

        const data = {
            title: $form.find('input[name="title"]').val(),
            // The select in the modal uses name="user_id" — send it as calendar_id
            calendar_id: $form.find('select[name="user_id"]').val(),
            start_at: startVal,
            end_at: endVal,
            description: $form.find('textarea[name="description"]').val(),
        };
        $.ajax({
            url: urlStore,
            method: 'POST',
            dataType: 'json',
            data: data,
            success: function(res) {
                eventModalEl.hide();
                $form[0].reset();
                // refresh calendar
                $('#calendar').fullCalendar('removeEvents');
                $('#calendar').fullCalendar('addEventSource', res);
                $('#calendar').fullCalendar('rerenderEvents');
            },
            error: function(xhr) {
                alert('Erreur: ' + (xhr.responseJSON?.message || 'Échec création'));
            }
        });
    });

    // Submit note form via AJAX
    $('#noteSubmit').on('click', function() {
        const $form = $('#noteForm');
        const data = {
            title: $form.find('input[name="title"]').val(),
            content: $form.find('textarea[name="content"]').val(),
        };
        $.ajax({
            url: '/calendar/notes',
            method: 'POST',
            dataType: 'json',
            data: data,
            success: function(res) {
                noteModalEl.hide();
                $form[0].reset();
                // optionally reload or show confirmation
                alert('Note créée');
            },
            error: function(xhr) {
                alert('Erreur: ' + (xhr.responseJSON?.message || 'Échec création'));
            }
        });
    });

   

    // Details modal close achat trade calendar support contact 
    function closeDetails() { detailsModal.hide(); }
    detailsClose.on('click', closeDetails);
    $(window).on('click', function(e) { if (e.target === detailsModal[0]) closeDetails(); });

    // Routes
    const urlStore = `/calendar/events`;
    const urlUpdate = (id) => `/calendar/events/${id}`;
    const urlDestroy = (id) => `/calendar/events/${id}`;

    // Init FullCalendar (vue simple: mois)
    // Use server-provided `events` variable when available to avoid an extra
    // controller endpoint. The calendar will still allow refetching via AJAX
    // when navigating to other ranges if needed.
    const initialEvents = @json($events ?? []);

    $('#calendar').fullCalendar({
        locale: '{{ app()->getLocale() }}',
        header: { left: 'prev,next today', center: 'title', right: 'month,agendaWeek,agendaDay' },
        defaultView: 'month',
        height: 'auto',
        contentHeight: 650,
        scrollTime: '08:00:00',
        lazyFetching: false,
        events: initialEvents,
            eventRender: function(event, element) {
                // Pastille de couleur + titre raccourci
                const $title = element.find('.fc-title');
                if ($title.length) {
                    $title.prepend(`<span class="cal-dot" style="background:${event.color || getEventColor(event.calendar_id)}; margin-right:6px;"></span>`);
                }
                // Tooltip détaillé
                const start = event.start ? event.start.clone() : null;
                const end = event.end ? event.end.clone() : start;
                const sameDay = start && end ? start.isSame(end, 'day') : true;
                const dateText = start ? (sameDay ? start.format('dddd D MMMM YYYY') : `${start.format('dddd D MMMM YYYY')} → ${end.format('dddd D MMMM YYYY')}`) : '';
                const tip = `<div><strong>${escapeHtml(event.title || '(Sans titre)')}</strong><div style="color:#6b7280;">${escapeHtml(dateText)}</div><div style="color:#6b7280;">${escapeHtml(event.calendar ? event.calendar : 'Global')}</div>${event.description ? `<hr style="border:none;border-top:1px solid #e5e7eb;"/><div style="white-space:pre-wrap;">${escapeHtml(event.description)}</div>` : ''}</div>`;
                try { tippy(element[0], { content: tip, allowHTML: true, theme: 'light-border', placement: 'top', maxWidth: 380 }); } catch (_) {}
            },
    selectable: false,
    selectHelper: false,
        eventLimit: true,
        select: null,
  
        eventClick: function(event) {
            const start = event.start ? event.start.clone() : null;
            const end = event.end ? event.end.clone() : start;
            const sameDay = start && end ? start.isSame(end, 'day') : true;
            const isAllDay = !!event.allDay;

            let dateText = '';
            if (start) {
                if (sameDay) {
                    if (!isAllDay) {
                        const startStr = start.format('dddd D MMMM YYYY HH:mm');
                        const endStr = end ? end.format('HH:mm') : '';
                        dateText = end ? `${startStr} → ${endStr}` : startStr;
                    } else {
                        dateText = start.format('dddd D MMMM YYYY');
                    }
                } else {
                    if (!isAllDay) {
                        const startStr = start.format('dddd D MMMM YYYY HH:mm');
                        const endStr = end ? end.format('dddd D MMMM YYYY HH:mm') : '';
                        dateText = end ? `${startStr} → ${endStr}` : startStr;
                    } else {
                        dateText = `${start.format('dddd D MMMM YYYY')} → ${end ? end.format('dddd D MMMM YYYY') : ''}`;
                    }
                }
            }

            detailsTitle.text(event.title || '(Sans titre)');
            detailsDate.text(dateText);
            detailsCalendar.text(`Calendrier: ${event.calendar ? event.calendar : 'Global'}`);
            //detailsCreator.text(event.creator ? `Créé par: ${event.creator}` : '');
            detailsDescription.text(event.description || '');
            detailsModal.show();
        },
    });

    // Rafraîchir le calendrier à la création depuis l'iframe
    eventIframe.on('load', function() {
        try {
            const href = this.contentWindow.location.href;
            const url = new URL(href);
            if (url.searchParams.get('created') === '1' && url.searchParams.get('in_iframe') === '1') {
                closeModal();
                // If initialEvents was used we need to refetch via AJAX to pick
                // up newly created events. FullCalendar v3 supports refetchEvents
                // which will call the `events` source if it's a function. Since
                // we set a static array above, call a manual reload via AJAX.
                $.ajax({
                    url: '/calendar/events',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        start: moment().startOf('month').format('YYYY-MM-DD'),
                        end: moment().endOf('month').format('YYYY-MM-DD')
                    },
                    success: function(res) {
                        $('#calendar').fullCalendar('removeEvents');
                        $('#calendar').fullCalendar('addEventSource', res);
                        $('#calendar').fullCalendar('rerenderEvents');
                    },
                    error: function() {
                        // ignore
                    }
                });
            }
        } catch (_) {
            // Ignore cross-origin issues (ne devrait pas arriver si même domaine)
        }
    });
});
</script>
@endpush


<!-- <style>
    /* VARIABLES (pour une gestion facile des couleurs et tailles) */
    :root {
        --primary-color: #4f6ba3; /* Bleu Filament/principal */
        --primary-light-color: #eef2fb; /* Fond clair pour les éléments actifs */
        --text-color: #374151; /* Gris foncé pour le texte général */
        --light-text-color: #6b7280; /* Gris plus clair */
        --border-color: #e5e7eb; /* Bordure générale */
        --bg-light: #f9fafb; /* Fond très clair */
        --bg-white: #ffffff; /* Fond blanc */
        --danger-color: #ef4444; /* Rouge pour annuler/supprimer */
        --radius-sm: 6px;
        --radius-md: 10px;
        --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
        --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
    }

    /* DARK MODE VARIABLES */
    .dark {
        --primary-color: #5c7ebc; /* Teinte légèrement différente pour le primaire en dark */
        --primary-light-color: #2b3d5b;
        --text-color: #e5e7eb;
        --light-text-color: #9ca3af;
        --border-color: #374151;
        --bg-light: #1f2937;
        --bg-white: #111827;
        --danger-color: #dc2626;
        --shadow-md: 0 4px 12px rgba(0,0,0,0.3);
        --shadow-lg: 0 8px 24px rgba(0,0,0,0.4);
    }

    /* -------------------------------------------------------------------------- */
    /* GLOBAL RESET & TYPOGRAPHY (minimal) */
    body {
        font-family: 'Inter', sans-serif; /* Assurez-vous d'importer Inter si vous l'utilisez */
        color: var(--text-color);
    }

    /* -------------------------------------------------------------------------- */
    /* LAYOUT GENERAL */
    .calendar-header-controls {
        display: flex;
        justify-content: flex-end; /* Aligné à droite pour le bouton de création */
        padding-bottom: 12px;
    }

    /* -------------------------------------------------------------------------- */
    /* CALENDAR CONTAINER */
    #calendar {
        min-height: 720px;
        background: var(--bg-white);
        padding: 16px;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-md); /* Légère ombre pour le conteneur */
    }

    /* -------------------------------------------------------------------------- */
    /* FULLCALENDAR V3 OVERRIDES */

    /* Header (prev, next, today, title, views) */
    .fc-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px !important; /* Plus d'espace sous l'en-tête */
    }

    .fc-toolbar h2 {
        font-size: 1.8em; /* Plus grand pour le titre du mois */
        font-weight: 600; /* Plus de poids */
        color: var(--text-color);
        margin: 0;
    }

    /* Boutons de navigation (prev, next, today) */
    .fc-button-group .fc-button {
        background-color: transparent !important; /* Boutons transparents */
        border: 1px solid var(--border-color) !important;
        color: var(--light-text-color) !important;
        text-shadow: none !important;
        box-shadow: none !important;
        border-radius: var(--radius-sm) !important;
        padding: 8px 12px;
        font-size: 0.9em;
        transition: all 0.2s ease;
    }

    .fc-button-group .fc-button:hover {
        background-color: var(--bg-light) !important;
        border-color: var(--primary-color) !important;
        color: var(--primary-color) !important;
    }

    .fc-button-group .fc-state-active,
    .fc-button-group .fc-state-down {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: var(--bg-white) !important;
    }

    /* Boutons de vue (month, agendaWeek, agendaDay) */
    .fc-button-group.fc-button-right {
        display: flex;
        gap: 6px; /* Espace entre les boutons de vue */
    }

    /* Grille du calendrier (jours, semaines) */
    .fc-widget-header {
        background-color: var(--bg-light) !important; /* Fond pour les jours de la semaine */
        border: 1px solid var(--border-color) !important;
    }

    .fc-widget-header th {
        padding: 8px 0;
        font-weight: 500;
        color: var(--light-text-color);
        font-size: 0.9em;
    }

    .fc-widget-content {
        border: 1px solid var(--border-color) !important;
    }

    .fc-day-number {
        font-size: 1.1em; /* Taille des chiffres des jours */
        font-weight: 600;
        color: var(--text-color);
        padding: 6px;
    }

    /* Jours du mois précédent/suivant (discrets) */
    .fc-other-month .fc-day-number {
        color: var(--light-text-color);
        opacity: 0.6;
    }

    /* Jours du week-end (optionnel) */
    .fc-day.fc-sat, .fc-day.fc-sun {
        /* background-color: rgba(var(--primary-color-rgb), 0.03); */
    }

    /* Cellule "Aujourd'hui" */
    td.fc-day.fc-today,
    td.fc-day.fc-widget-content.fc-today {
        background-color: var(--primary-light-color) !important; /* Fond coloré */
        border-color: var(--primary-color) !important; /* Bordure d'accentuation */
        border-width: 1px !important;
        position: relative;
        overflow: hidden; /* Pour le cercle */
    }

    td.fc-day.fc-today .fc-day-number,
    td.fc-day.fc-widget-content.fc-today .fc-day-number {
        background-color: var(--primary-color);
        color: var(--bg-white);
        width: 30px; /* Taille du cercle */
        height: 30px;
        line-height: 30px; /* Centrer le texte verticalement */
        border-radius: 50%;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        position: absolute;
        top: 6px;
        left: 6px;
        z-index: 1; /* S'assurer qu'il est au-dessus des événements */
    }


    /* Événements */
    .fc-event {
        font-size: 0.85em;
        border-radius: var(--radius-sm);
        cursor: pointer;
        padding: 4px 6px;
        margin-bottom: 2px;
        border: none !important; /* Supprimer la bordure par défaut */
        background-color: var(--primary-color) !important; /* Utilisez la couleur de l'événement */
        color: var(--bg-white) !important;
        font-weight: 500;
        transition: transform 0.1s ease, box-shadow 0.1s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .fc-event:hover {
        transform: translateY(-2px); /* Léger effet de survol */
        box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    }

    .fc-event .fc-content {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .fc-event .fc-title {
        white-space: nowrap; /* Empêche le titre de passer à la ligne */
        overflow: hidden;
        text-overflow: ellipsis; /* Ajoute des points de suspension si le texte est trop long */
        flex-grow: 1;
    }

    /* Pastille de couleur dans l'événement */
    .fc-event .cal-dot {
        width: 8px; /* Plus petite */
        height: 8px;
        min-width: 8px; /* Empêche le rétrécissement */
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0; /* Empêche la pastille de rétrécir */
    }

    /* Make agenda day/week time grid scrollable */
    .fc-agendaWeek-view .fc-time-grid .fc-scroller,
    .fc-agendaDay-view .fc-time-grid .fc-scroller {
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        max-height: 650px; /* matches contentHeight below */
    }

    /* Dark mode adjustments for FullCalendar */
    .dark .fc-toolbar h2 { color: var(--text-color); }
    .dark .fc-button-group .fc-button {
        border-color: var(--border-color) !important;
        color: var(--light-text-color) !important;
    }
    .dark .fc-button-group .fc-button:hover {
        background-color: var(--bg-light) !important;
        border-color: var(--primary-color) !important;
        color: var(--primary-color) !important;
    }
    .dark .fc-button-group .fc-state-active,
    .dark .fc-button-group .fc-state-down {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: var(--bg-white) !important;
    }
    .dark .fc-widget-header { background-color: var(--bg-light) !important; }
    .dark .fc-widget-header th { color: var(--light-text-color); }
    .dark .fc-widget-content { border-color: var(--border-color) !important; }
    .dark .fc-day-number { color: var(--text-color); }
    .dark .fc-other-month .fc-day-number { color: var(--light-text-color); }
    .dark td.fc-day.fc-today,
    .dark td.fc-day.fc-widget-content.fc-today {
        background-color: var(--primary-light-color) !important;
        border-color: var(--primary-color) !important;
    }
    .dark td.fc-day.fc-today .fc-day-number,
    .dark td.fc-day.fc-widget-content.fc-today .fc-day-number {
        background-color: var(--primary-color);
        color: var(--bg-white);
    }
    .dark .fc-event { color: var(--bg-white) !important; }

    /* -------------------------------------------------------------------------- */
    /* MODERN CREATE BUTTON */
    .modern-create-button {
        padding: 10px 18px;
        background: linear-gradient(135deg, var(--primary-color) 0%, #6e94e2 100%); /* Dégradé */
        color: var(--bg-white);
        border: none;
        border-radius: 999px; /* Pill shape */
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 1.0em;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(79, 107, 163, 0.3); /* Ombre cohérente avec le dégradé */
        transition: all 0.2s ease;
    }
    .modern-create-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(79, 107, 163, 0.4);
    }
    .modern-create-button .icon-plus {
        font-size: 1.2em; /* Taille de l'icône + */
        font-weight: 700;
        line-height: 1; /* Assure un bon alignement */
    }
    .modern-create-button.hidden {
        display: none;
    }

    /* -------------------------------------------------------------------------- */
    /* CALENDAR FILTERS (Chips) */
    .calendar-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
        padding: 6px 0;
    }
    .cal-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border: 1px solid var(--border-color);
        border-radius: 999px;
        background: var(--bg-white);
        color: var(--text-color);
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease;
        font-size: 0.9em;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .cal-chip:hover {
        border-color: var(--primary-color);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .cal-chip.active {
        border-color: var(--primary-color);
        background: var(--primary-light-color);
        color: var(--primary-color);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .cal-chip .cal-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .dark .cal-chip {
        background: var(--bg-light);
        border-color: var(--border-color);
        color: var(--text-color);
    }
    .dark .cal-chip.active {
        background: var(--primary-light-color);
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    /* -------------------------------------------------------------------------- */
    /* MODAL STYLES (Création / Détails) */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4); /* Fond semi-transparent */
        display: flex; /* Utilisation de flexbox pour centrer */
        justify-content: center;
        align-items: center;
        font-family: 'Inter', sans-serif;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .modal.show { /* Classe à ajouter/retirer avec JS pour l'animation */
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        background-color: var(--bg-white);
        margin: auto; /* Centré via flexbox */
        padding: 20px 25px;
        border-radius: var(--radius-md);
        width: 95%;
        max-width: 600px; /* Plus large pour la création */
        position: relative;
        box-shadow: var(--shadow-lg); /* Ombre plus prononcée */
        transform: translateY(-20px); /* Animation d'entrée */
        transition: transform 0.3s ease;
        opacity: 0;
    }
    .modal.show .modal-content {
        transform: translateY(0);
        opacity: 1;
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 15px;
    }
    .modal-header h3 {
        font-size: 1.3em;
        font-weight: 600;
        color: var(--text-color);
    }

    .close, .close-note, .details-close {
        color: var(--light-text-color);
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.2s ease;
        line-height: 1; /* Empêche le décalage vertical */
    }
    .close:hover, .close-note:hover, .details-close:hover {
        color: var(--text-color);
        text-decoration: none;
    }

    /* Formulaire dans les modals */
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        font-size: 1em;
        color: var(--text-color);
        background-color: var(--bg-white);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(79, 107, 163, 0.2); /* Léger halo de focus */
    }
    .dark .form-input, .dark .form-select, .dark .form-textarea {
        background-color: var(--bg-light);
        border-color: var(--border-color);
        color: var(--text-color);
    }

    /* Boutons des modals */
    .modern-button {
        padding: 10px 18px;
        border: none;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-size: 1em;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .modern-button.primary {
        background-color: var(--primary-color);
        color: var(--bg-white);
    }
    .modern-button.primary:hover {
        background-color: #6e94e2; /* Teinte plus claire au survol */
        box-shadow: 0 2px 8px rgba(79, 107, 163, 0.2);
    }
    .modern-button.secondary {
        background-color: var(--danger-color);
        color: var(--bg-white);
    }
    .modern-button.secondary:hover {
        background-color: #f87171; /* Teinte plus claire au survol */
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }

    /* Modal de détails (ajuster pour un max-width plus petit) */
    #eventDetailsModal .modal-content {
        max-width: 480px;
        padding: 20px 25px;
    }
    #detailsTitle {
        font-size: 1.5em;
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--text-color);
    }
    #detailsDate, #detailsCalendar, #detailsCreator {
        font-size: 0.95em;
        color: var(--light-text-color);
        margin-bottom: 5px;
    }
    #detailsDescription {
        font-size: 1em;
        line-height: 1.5;
        color: var(--text-color);
        padding-top: 10px;
        margin-top: 10px;
        border-top: 1px solid var(--border-color);
        white-space: pre-wrap; /* Pour conserver le formatage du texte */
    }
    .dark #detailsDescription {
        border-color: var(--border-color);
    }

    /* Tippy.js Tooltips */
    .tippy-box[data-theme~='light-border'] {
        background-color: var(--bg-white);
        color: var(--text-color);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        box-shadow: var(--shadow-md);
    }
    .tippy-box[data-theme~='light-border'] > .tippy-arrow {
        color: var(--border-color); /* Couleur de la flèche */
    }
    .tippy-box[data-theme~='light-border'] > .tippy-backdrop {
        background-color: var(--bg-white);
    }
    .dark .tippy-box[data-theme~='light-border'] {
        background-color: var(--bg-light);
        color: var(--text-color);
        border-color: var(--border-color);
    }
    .dark .tippy-box[data-theme~='light-border'] > .tippy-arrow {
        color: var(--border-color);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .fc-toolbar {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        .fc-toolbar h2 {
            margin-bottom: 10px;
        }
        .fc-toolbar .fc-center, .fc-toolbar .fc-left, .fc-toolbar .fc-right {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .fc-button-group {
            width: 100%;
            justify-content: center;
        }
        .fc-button-group .fc-button {
            flex-grow: 1;
        }
        .calendar-header-controls {
            justify-content: center;
        }
    }
    @media (max-width: 600px) {
        .modal-content {
            margin: 5% auto;
            width: 95%;
        }
        .modern-create-button {
            width: 100%;
            justify-content: center;
            padding: 10px;
            font-size: 0.95em;
        }
    }

</style> -->