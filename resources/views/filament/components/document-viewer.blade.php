@php
    /** @var \Illuminate\Support\Collection $items */
    $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
@endphp

@php
    $ctx = isset($purchase) ? 'purchase' : (isset($trade) ? 'trade' : null);
    $recordId = isset($purchase) ? $purchase->id : (isset($trade) ? $trade->id : null);
@endphp

<div class="p-0">
    <livewire:document-viewer :items="$items" :context="$ctx" :record-id="$recordId" />
</div>

{{-- Inline styles for demonstration. In production, move to a separate CSS file. --}}
<style>
    /* Basic Reset/Normalisation (Filament usually provides this) */
    .document-viewer-container * {
        box-sizing: border-box;
    }

    /* Container for the entire viewer */
    .document-viewer-container {
        padding: 1rem; /* Adjust padding as needed */
    }

    .document-viewer-empty-message {
        text-align: center;
        padding: 2rem 0;
        color: #6b7280; /* gray-500 */
        font-size: 1rem;
    }

    /* Document Grid (Responsive) */
    .document-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* Adaptative columns, min 250px wide */
        gap: 1rem; /* Space between grid items */
    }

    /* Document Card */
    .document-card {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 0.75rem; /* rounded-xl */
        background-color: #f9fafb; /* gray-50 */
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
        border: 1px solid rgba(0, 0, 0, 0.05); /* ring-1 ring-gray-950/5 */
        transition: transform 0.2s ease-in-out;
    }
    .document-card:hover {
        transform: translateY(-2px); /* Slight lift on hover */
    }

    /* Dark Mode (Example - integrate with Filament's dark mode toggle) */
    .dark .document-card {
        background-color: rgba(68, 68, 68, 0.1); /* dark:bg-gray-400/10 */
        border-color: rgba(255, 255, 255, 0.1); /* dark:ring-white/10 */
    }
    .dark .document-viewer-empty-message,
    .dark .document-card-name,
    .dark .document-card-metadata .metadata-text {
        color: #d1d5db; /* gray-300 */
    }


    /* Thumbnail / Icon Area */
    .document-card-thumbnail {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem; /* p-3 */
        height: 10rem; /* h-36 */
        background-color: #f3f4f6; /* gray-100 */
        flex-shrink: 0;
    }
    .dark .document-card-thumbnail {
        background-color: #1f2937; /* dark:bg-gray-800 */
    }

    .thumbnail-link {
        display: block;
        width: 100%;
        height: 100%;
    }

    .thumbnail-image {
        width: 100%;
        height: 100%;
        object-fit: contain; /* object-contain */
        border-radius: 0.375rem; /* rounded-md */
        transition: transform 0.2s ease-in-out;
    }
    .document-card:hover .thumbnail-image {
        transform: scale(1.05); /* group-hover:scale-105 */
    }

    .document-icon {
        height: 5rem; /* h-20 */
        width: 5rem; /* w-20 */
        color: #9ca3af; /* gray-400 */
        flex-shrink: 0;
    }
    .dark .document-icon {
        color: #4b5563; /* dark:text-gray-600 */
    }


    /* Thumbnail Overlay (for images) */
    .thumbnail-overlay {
        position: absolute;
        inset: 0; /* top:0, right:0, bottom:0, left:0 */
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.4); /* bg-black/40 */
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    }
    .document-card:hover .thumbnail-overlay {
        opacity: 1; /* group-hover:opacity-100 */
    }

    .overlay-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem; /* gap-1.5 */
        padding: 0.5rem 0.75rem; /* px-3 py-2 */
        border-radius: 0.5rem; /* rounded-lg */
        font-size: 0.875rem; /* text-sm */
        font-weight: 600; /* font-semibold */
        color: #ffffff; /* text-white */
        background-color: #10b981; /* primary-600 (example) */
        outline: none;
        transition: background-color 0.075s ease-in-out;
    }
    .overlay-button:hover, .overlay-button:focus {
        background-color: #059669; /* primary-700 (darker on hover) */
    }
    .dark .overlay-button {
        background-color: #34d399; /* primary-400 */
    }
    .dark .overlay-button:hover, .dark .overlay-button:focus {
        background-color: #10b981; /* primary-600 */
    }


    /* File Details Area */
    .document-card-details {
        padding: 1rem; /* p-4 */
        display: flex;
        flex-direction: column;
        flex-grow: 1; /* flex-grow */
    }

    .document-card-name {
        font-size: 0.875rem; /* text-sm */
        font-weight: 500; /* font-medium */
        color: #111827; /* gray-950 */
        display: -webkit-box;
        -webkit-line-clamp: 2; /* line-clamp-2 */
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.5rem; /* approx 2 lines height */
    }

    .document-card-metadata {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem 0.75rem; /* gap-x-3 gap-y-1 */
        font-size: 0.75rem; /* text-xs */
        color: #6b7280; /* gray-500 */
        margin-top: 0.5rem; /* mt-2 */
    }
    .dark .document-card-metadata {
        color: #9ca3af; /* dark:text-gray-400 */
    }

    .metadata-item {
        display: flex;
        align-items: center;
        gap: 0.375rem; /* gap-1.5 */
        white-space: nowrap; /* whitespace-nowrap */
    }
    .metadata-icon {
        height: 0.75rem; /* h-3 */
        width: 0.75rem; /* w-3 */
        flex-shrink: 0;
    }
    .metadata-text {
        overflow: hidden;
        text-overflow: ellipsis; /* For long names in metadata */
    }

    /* Action Buttons */
    .document-card-actions {
        margin-top: 1rem; /* mt-4 */
        display: flex;
        flex-wrap: wrap; /* Allow buttons to wrap */
        gap: 0.5rem; /* gap-2 */
        justify-content: flex-end; /* justify-end */
    }

    .action-button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem; /* gap-1.5 */
        padding: 0.375rem 0.625rem; /* px-2.5 py-1.5 */
        border-radius: 0.5rem; /* rounded-lg */
        font-size: 0.875rem; /* text-sm */
        font-weight: 600; /* font-semibold */
        color: #4b5563; /* gray-700 */
        border: 1px solid #e5e7eb; /* border border-gray-200 */
        background-color: transparent;
        outline: none;
        transition: background-color 0.075s ease-in-out, border-color 0.075s ease-in-out, color 0.075s ease-in-out;
        flex: 1 1 auto; /* Allow buttons to grow and shrink, but prefer auto width */
        max-width: 100%; /* Ensure it doesn't overflow */
    }
     .action-button:hover, .action-button:focus {
        background-color: #f3f4f6; /* hover:bg-gray-100 */
        border-color: #d1d5db; /* A slightly darker border on hover */
    }
    .dark .action-button {
        color: #d1d5db; /* dark:text-gray-200 */
        border-color: rgba(255, 255, 255, 0.1);
    }
    .dark .action-button:hover, .dark .action-button:focus {
        background-color: #374151; /* dark:hover:bg-gray-700 */
    }

    .button-icon {
        height: 1rem; /* h-4 */
        width: 1rem; /* w-4 */
        flex-shrink: 0;
    }

    /* Responsive adjustments for buttons: stack on small screens */
    @media (max-width: 639px) { /* Equivalent to sm breakpoint - 1px */
        .document-card-actions {
            flex-direction: column; /* Stack buttons vertically */
            align-items: stretch; /* Make them full width */
        }
        .action-button {
            width: 100%; /* Full width */
        }
    }

    /* Media queries for grid columns */
    @media (min-width: 640px) { /* sm breakpoint */
        .document-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 768px) { /* md breakpoint */
        .document-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 1024px) { /* lg breakpoint */
        .document-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
</style>

{{-- No hidden forms needed; use robust fetch() below to avoid nested-form issues in Filament modals. --}}

<script>
    (function(){
        const CSRF = '{{ csrf_token() }}';
        function removeCard(el) {
            try {
                const card = el.closest('.document-card');
                if (!card) return;
                card.style.transition = 'opacity 150ms ease';
                card.style.opacity = '0';
                setTimeout(function(){
                    card.remove();
                }, 160);
            } catch (_) { /* ignore */ }
        }
        // Global handler used by inline onclick to ensure reliability
        window.__docDeleteLink = function(a, e) {
            if (e) { e.preventDefault(); e.stopPropagation(); }
            const url = a.getAttribute('data-signed-url');
            if (!url) return false;
            if (!confirm('Supprimer ce document ? Cette action est irréversible.')) return false;
            fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json,text/html' },
                credentials: 'same-origin'
            }).then(function(res){
                if (res.ok) {
                    removeCard(a);
                } else {
                    const ctx = a.getAttribute('data-delete-context');
                    if (ctx) {
                        submitDelete(a);
                        return;
                    }
                    return res.text().then(function(t){
                        alert('Suppression échouée (' + res.status + ').');
                        console.error('Signed delete error:', t);
                    });
                }
            }).catch(function(err){
                try { submitDelete(a); } catch (_) {
                    alert('Erreur réseau pendant la suppression.');
                    console.error(err);
                }
            });
            return false;
        };
        function submitDelete(btn) {
            const ctx = btn.getAttribute('data-delete-context');
            const url = btn.getAttribute('data-delete-url');
            const path = btn.getAttribute('data-path');
            if (!url || !path) return;
            if (!confirm('Supprimer ce document ? Cette action est irréversible.')) return;
            const params = new URLSearchParams();
            params.append('path', path);
            if (ctx === 'trade') {
                const tradeId = btn.getAttribute('data-trade-id');
                if (!tradeId) return;
                params.append('trade_id', tradeId);
            } else {
                const purchaseId = btn.getAttribute('data-purchase-id');
                if (!purchaseId) return;
                params.append('purchase_id', purchaseId);
            }
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'text/html,application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: params.toString(),
                credentials: 'same-origin'
            }).then(function(res){
                // On success (200/302), remove the card in-place
                if (res.ok || res.status === 302) {
                    removeCard(btn);
                } else {
                    return res.text().then(function(t){
                        alert('Suppression échouée (' + res.status + ').');
                        console.error('Delete error:', t);
                    });
                }
            }).catch(function(err){
                alert('Erreur réseau pendant la suppression.');
                console.error(err);
            });
        }
        document.addEventListener('click', function(e){
            const btn = e.target.closest('button[data-delete-context]');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            submitDelete(btn);
        }, { capture: true });
    })();
</script>