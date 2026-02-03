@php
    $darkModeEnabled = isset($darkMode) ? $darkMode : false;
@endphp

<div class="support-conversation-root">
  

    <div class="conv-container" wire:poll.5s="loadContacts">
        <!-- Sidebar Contacts -->
        <div class="conv-sidebar">
            <div class="conv-sidebar-head">
                <h3 class="conv-sidebar-title">💬 {{ __('support.conversations') }}</h3>
            </div>

            <div class="conv-contacts-list {{ count($contacts) > 5 ? 'conv-contacts-list--scrollable' : '' }}"
                 @if(count($contacts) > 6) style="max-height: calc(100vh - 160px);" @endif>
                @foreach($contacts as $c)
                    @php
                        $currentUser = auth()->user();
                        // If the current user is a client, show the conversation partner as "Support"
                        if ($currentUser && method_exists($currentUser, 'isClient') && $currentUser->isClient()) {
                            $contactName = __('support.support') ?: 'Support';
                            // show a neutral support avatar
                            $rawAvatar = null;
                            $contactAvatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($contactName) . '&background=4f6ba3&color=ffffff';
                        } else {
                            $contactName = $c->name ?: (optional($c->user)->name ?? __('support.anonymous'));
                            $rawAvatar = optional($c->user)->avatar_url ?? optional($c->user)->avatar ?? $c->avatar_url ?? $c->avatar ?? null;
                            $contactAvatarUrl = $rawAvatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($contactName) . '&background=4f6ba3&color=ffffff';
                        }
                        $lastMessageBody = $c->last_message_body ?? $c->message;
                        $lastMessageAt = $c->last_message_at ?? $c->created_at;
                        if($lastMessageAt && ! $lastMessageAt instanceof \Carbon\Carbon) {
                            $lastMessageAt = \Carbon\Carbon::parse($lastMessageAt);
                        }
                        $previewPrefix = ($c->last_message_sender_type ?? null) === 'admin' ? 'Support · ' : '';
                        $contactPreview = Str::limit($previewPrefix . (string) $lastMessageBody, 80);
                    @endphp
                    <div 
                        wire:click="selectContact({{ $c->id }})"
                        class="conv-contact-card {{ $selectedContactId == $c->id ? 'selected' : '' }}"
                    >
                        <div class="conv-contact-card-inner">
                            <img src="{{ $contactAvatarUrl }}" alt="{{ $contactName }}" class="conv-contact-avatar" loading="lazy">
                            <div class="conv-contact-info">
                                <div class="conv-contact-name">
                                    <span>{{ $contactName }}</span>
                                    <span class="conv-contact-date">{{ $lastMessageAt ? $lastMessageAt->format('d/m/Y H:i') : '' }}</span>
                                </div>
                                <div class="conv-contact-message">
                                    {{ $contactPreview }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Conversation Area -->
        <div class="conv-main" wire:poll.5s="loadMessages">
            @if($mailStatusMessage)
                <div class="conv-alert {{ $mailStatusIsError ? 'error' : 'success' }}">
                    {{ $mailStatusMessage }}
                </div>
            @endif
            @if(!$selectedContactId)
                <div class="conv-empty">{{ __('support.select_conversation') }} 💭</div>
            @else
                @php($contact = \App\Models\Contact::find($selectedContactId))
                @php($currentUser = auth()->user())
                @php($contactsCollection = collect($contacts))
                @php($overview = $contactsCollection->firstWhere('id', $selectedContactId))
                @php($candidateName = optional(optional($overview)->user)->name ?? optional($overview)->name ?? optional($contact)->name ?? optional($contact)->email)
                @php($contactDisplayName = ($currentUser && method_exists($currentUser, 'isClient') && $currentUser->isClient()) ? (__('support.support') ?: 'Support') : ($candidateName ?: 'Client'))
                @php($contactInitials = collect(explode(' ', trim((string) $contactDisplayName)))->filter()->map(fn ($segment) => strtoupper(mb_substr($segment, 0, 1)))->take(2)->implode('') ?: 'C')
                @php(
                    $userRel = optional(optional($overview)->user) ?: optional($contact)->user
                )
                @php($rawContactAvatar = ($userRel->avatar_url ?? $userRel->avatar ?? optional($overview)->avatar_url ?? optional($overview)->avatar ?? optional($contact)->avatar_url ?? optional($contact)->avatar) ?? null)
                @php($contactAvatarUrl = ($currentUser && method_exists($currentUser, 'isClient') && $currentUser->isClient()) ? ('https://ui-avatars.com/api/?name=' . urlencode((__('support.support') ?: 'Support')) . '&background=4f6ba3&color=ffffff') : ($rawContactAvatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($contactDisplayName) . '&background=4f6ba3&color=ffffff'))
                @php($contactsCollection = collect($contacts))
                @php($overview = $contactsCollection->firstWhere('id', $selectedContactId))
                @php($overviewLastAt = optional($overview)->last_message_at)
                @php($lastActivityAt = $overviewLastAt instanceof \Carbon\Carbon ? $overviewLastAt : (optional($contact)->updated_at ?? optional($contact)->created_at))
                @php($headerSnippet = Str::limit(optional($overview)->last_message_body ?? optional($contact)->message, 110))

                <div class="conv-conversation-header">
                    <div class="conv-conversation-identity">
                        <div class="conv-avatar">
                            @if($contactAvatarUrl)
                                <img src="{{ $contactAvatarUrl }}" alt="{{ $contactDisplayName }}" loading="lazy">
                            @else
                                <span class="conv-avatar-initials">{{ $contactInitials }}</span>
                            @endif
                        </div>
                        <div class="conv-identity-text">
                            <p class="conv-identity-name">{{ $contactDisplayName }}</p>
                            <span class="conv-identity-meta">{{ $lastActivityAt ? $lastActivityAt->format('d/m/Y H:i') : '' }}</span>
                            <p class="conv-identity-snippet">{{ $headerSnippet }}</p>
                        </div>
                    </div>
                    <div class="conv-header-meta">
                        @if($lastActivityAt)
                            <span class="conv-status-badge">Dernière activité : {{ $lastActivityAt->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>
                </div>

                <!-- Thread -->
                <h4 class="conv-section-title conv-thread-title">💭 {{ __('support.conversation') }}</h4>
                <div class="conv-messages-area">
                    @php($u = auth()->user())
                    @php($entries = collect($timelineEntries ?? []))
                    @php($latestTimelineEntry = $entries->last())

                    @forelse($entries as $entry)
                        @if($entry['type'] === 'contact')
                            @php($contactRequest = $entry['model'])
                            @php($requestDisplayName = $contactRequest->name ?: ($contactRequest->email ?: $contactDisplayName))
                            @php($requestInitials = collect(explode(' ', trim((string) $requestDisplayName)))->filter()->map(fn ($segment) => strtoupper(mb_substr($segment, 0, 1)))->take(2)->implode('') ?: 'C')
                            @php($requestAvatarRaw = optional($contactRequest->user)->avatar_url ?? optional($contactRequest->user)->avatar ?? $contactRequest->avatar_url ?? $contactRequest->avatar ?? null)
                            @php($requestAvatarUrl = $requestAvatarRaw ?: 'https://ui-avatars.com/api/?name=' . urlencode($requestDisplayName) . '&background=4f6ba3&color=ffffff')
                            @php($isActiveRequest = $contactRequest->id === optional($contact)->id)

                            <div class="conv-message-wrapper start">
                                <div class="conv-message-avatar client">
                                    @if($requestAvatarUrl)
                                        <img src="{{ $requestAvatarUrl }}" alt="{{ $requestDisplayName }}" loading="lazy" class="w-10 h-10 rounded-full border-2 border-transparent hover:border-[#4f6ba3] transition object-cover shadow-md">
                                    @else
                                        <span class="conv-avatar-initials">{{ mb_substr($requestInitials, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="conv-message-bubble client">
                                    <div class="conv-message-meta">
                                        <div>{{ $requestDisplayName }} — {{ optional($contactRequest->created_at)->format('d/m/Y H:i') }}</div>
                                        @if(!$editingMessage && $isActiveRequest && $u && method_exists($u, 'isClient') && $u->isClient() && $contactRequest->email === $u->email)
                                            <div class="conv-edit-actions">
                                                <button wire:click="startEditMessage" class="conv-btn-edit">✏️</button>
                                            </div>
                                        @endif
                                    </div>

                                    @if($editingMessage && $isActiveRequest)
                                        <textarea wire:model.defer="editedMessage" rows="5" class="conv-textarea"></textarea>
                                        <div class="conv-main-edit">
                                            <button wire:click="saveMessage" class="conv-btn conv-btn-save">💾 Enregistrer</button>
                                            <button wire:click="cancelEditMessage" class="conv-btn conv-btn-cancel">❌ Annuler</button>
                                        </div>
                                    @else
                                        <div class="conv-message-body">{{ $contactRequest->message }}</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            @php($m = $entry['model'])
                            @php($isAdmin = $m->sender_type === 'admin')
                            @php($authorName = $isAdmin ? 'Support' : ($m->user?->name ?: $contactDisplayName))
                            @php($initials = collect(explode(' ', trim((string) $authorName)))->filter()->map(fn ($segment) => strtoupper(mb_substr($segment, 0, 1)))->take(2)->implode(''))
                            @php($initials = $initials ?: ($isAdmin ? 'S' : 'C'))
                            @php($rawAuthorAvatar = $isAdmin ? null : ($m->user?->avatar_url ?? $m->user?->avatar ?? null))
                            @php($authorAvatarUrl = $rawAuthorAvatar ?: ($isAdmin ? null : 'https://ui-avatars.com/api/?name=' . urlencode($authorName) . '&background=4f6ba3&color=ffffff'))

                            <div class="conv-message-wrapper {{ $isAdmin ? 'end' : 'start' }}">
                                <div class="conv-message-avatar {{ $isAdmin ? 'admin' : 'client' }}">
                                    @if($authorAvatarUrl)
                                        <img src="{{ $authorAvatarUrl }}" alt="{{ $authorName }}" loading="lazy">
                                    @else
                                        <span class="conv-avatar-initials">{{ $initials }}</span>
                                    @endif
                                </div>
                                <div class="conv-message-bubble {{ $isAdmin ? 'admin' : 'client' }}">
                                    <div class="conv-message-meta">
                                        <div>{{ $isAdmin ? '👨‍💼 Support' : ($m->user?->name ?? $contactDisplayName) }} — {{ $m->created_at->format('d/m/Y H:i') }}</div>
                                        @if($u && $m->user_id === $u->id)
                                            <div class="conv-edit-actions">
                                                @if($editingThreadMessageId === $m->id)
                                                    <button wire:click="saveThreadMessage" class="conv-btn conv-btn-primary">💾</button>
                                                    <button wire:click="cancelEditThreadMessage" class="conv-btn conv-btn-cancel">❌</button>
                                                @else
                                                    <button wire:click.prevent="startEditThreadMessage({{ $m->id }})" class="conv-btn-edit">✏️</button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    @if($editingThreadMessageId === $m->id)
                                        <div style="margin-top: 6px;">
                                            <textarea wire:model.defer="editedThreadMessageBody" rows="3" class="conv-textarea"></textarea>
                                        </div>
                                    @else
                                        <div class="conv-message-body">{{ $m->body }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="conv-empty">{{ __('support.no_messages_yet') ?? 'Aucun message disponible.' }}</div>
                    @endforelse
                </div>

                <hr class="conv-divider" />

                <!-- Admin Reply -->
                @php($u = auth()->user())
                @if($u && method_exists($u, 'isSuperAdmin') && $u->isSuperAdmin())
                    <div class="conv-reply-area">
                        <div class="conv-reply-flex">
                            <textarea wire:model.defer="adminReplyBody" rows="3"
                                placeholder="{{ __('support.write_reply') }}"
                                class="conv-textarea"></textarea>
                        </div>
                        <button wire:click="sendReply" wire:loading.attr="disabled" wire:target="sendReply"
                            class="conv-reply-btn">
                            <span wire:loading.remove wire:target="sendReply">📨 {{ __('support.send') }}</span>
                            <span wire:loading wire:target="sendReply">⏳ {{ __('support.sending') }}</span>
                        </button>
                    </div>
                @endif

                <!-- Client Reply -->
                @if($u && method_exists($u, 'isClient') && $u->isClient())
                    <div class="conv-reply-area">
                        <div class="conv-reply-flex">
                            <textarea wire:model.defer="clientReplyBody" rows="3"
                                placeholder="{{ __('support.write_message') }}"
                                class="conv-textarea"></textarea>
                        </div>
                        <button wire:click="postMessageAsClient" wire:loading.attr="disabled" wire:target="postMessageAsClient"
                            class="conv-reply-btn conv-reply-btn-client">
                            <span wire:loading.remove wire:target="postMessageAsClient">💬 {{ __('support.add') }}</span>
                            <span wire:loading wire:target="postMessageAsClient">⏳ {{ __('support.adding') }}</span>
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </div> 

      <style>
        /* 🌓 Variables pour Light Mode */
        :root {
            --conv-text-primary: #1f2933;
            --conv-text-secondary: #52606d;
            --conv-text-muted: #8290a4;
            --conv-bg-primary: #ffffff;
            --conv-bg-secondary: #f4f7fb;
            --conv-bg-hover: #eef3fb;
            --conv-border: #d6deeb;
            --conv-border-light: #e5ecf7;
            --conv-selected-bg: rgba(79, 107, 163, 0.16);
            --conv-selected-border: #4f6ba3;
            --conv-admin-bubble: rgba(110, 148, 195, 0.18);
            --conv-client-bubble: #ffffff;
            --conv-shadow: rgba(79, 107, 163, 0.08);
            --conv-btn-primary: #4f6ba3;
            --conv-btn-primary-hover: #5b7db5;
            --conv-btn-primary-active: #3f5b85;
            --conv-btn-secondary: #edf2fb;
            --conv-btn-secondary-text: #35527b;
            --conv-btn-secondary-hover: #dbe6f8;
            --conv-input-border: #c5d3ec;
            --conv-input-bg: #ffffff;
        }

        /* 🌙 Variables pour Dark Mode */
        .dark {
            --conv-text-primary: #f3f4f6;
            --conv-text-secondary: #d1d5db;
            --conv-text-muted: #9ca3af;
            --conv-bg-primary: #1f2937;
            --conv-bg-secondary: #111827;
            --conv-bg-hover: #374151;
            --conv-border: #374151;
            --conv-border-light: #4b5563;
            --conv-selected-bg: rgba(79, 107, 163, 0.22);
            --conv-selected-border: #7fa4cf;
            --conv-admin-bubble: rgba(110, 148, 195, 0.32);
            --conv-client-bubble: #111827;
            --conv-shadow: rgba(15, 23, 42, 0.5);
            --conv-btn-primary: #7795c4;
            --conv-btn-primary-hover: #8aaed0;
            --conv-btn-primary-active: #5a79ac;
            --conv-btn-secondary: #1f2a3d;
            --conv-btn-secondary-text: #d1def5;
            --conv-btn-secondary-hover: #273550;
            --conv-input-border: #4b5563;
            --conv-input-bg: #1f2937;
        }

        .dark .conv-conversation-header {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(45, 212, 191, 0.08));
            border-color: rgba(59, 130, 246, 0.25);
            box-shadow: 0 18px 32px rgba(2, 6, 23, 0.45);
        }

        .dark .conv-avatar {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(16, 185, 129, 0.25));
            color: #e0f2fe;
        }

        .dark .conv-main-message {
            box-shadow: 0 18px 36px rgba(2, 6, 23, 0.55);
        }

        .dark .conv-channel-badge {
            background: rgba(125, 160, 210, 0.4);
            color: #e6efff;
        }

        .dark .conv-status-badge {
            background: rgba(129, 140, 248, 0.2);
            color: #c7d2fe;
        }

        .dark .conv-messages-area {
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.05);
        }

        .dark .conv-message-avatar.admin {
            background: rgba(124, 155, 201, 0.45);
            color: #d9e6ff;
        }

        .dark .conv-message-avatar.client {
            background: rgba(97, 134, 184, 0.42);
            color: #d9e6ff;
        }
            .conv-alert {
                margin-bottom: 1rem;
                padding: 0.75rem 1rem;
                border-radius: 10px;
                font-size: 0.9rem;
                display: flex;
                gap: 0.5rem;
                align-items: center;
                border: 1px solid;
            }

            .conv-alert.success {
                background: rgba(16, 185, 129, 0.12);
                border-color: rgba(16, 185, 129, 0.35);
                color: #047857;
            }

            .conv-alert.error {
                background: rgba(248, 113, 113, 0.12);
                border-color: rgba(248, 113, 113, 0.35);
                color: #b91c1c;
            }

        .dark .conv-contact-card {
            background: var(--conv-bg-primary);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.5);
        }

        .dark .conv-contact-card:hover {
            border-color: rgba(125, 160, 210, 0.35);
        }

        .dark .conv-contact-card.selected {
            border-color: var(--conv-selected-border) !important;
        }

        .dark .conv-contact-card:hover .conv-contact-avatar,
        .dark .conv-contact-card.selected .conv-contact-avatar {
            border-color: var(--conv-selected-border);
        }

        /* 📦 Container */
        .conv-container {
            display: flex;
            gap: 1.5rem;
            font-family: Inter, sans-serif;
            color: var(--conv-text-primary);
            transition: color 0.3s ease;
            align-items: flex-start;
            position: relative;
            flex-wrap: wrap;
        }

        /* 📱 Sidebar */
        .conv-sidebar {
            width: 320px;
            border-right: 1px solid var(--conv-border);
            padding-right: 1rem;
            transition: border-color 0.3s ease;
            position: sticky;
            top: 1.5rem;
            align-self: flex-start;
            max-height: calc(100vh - 4rem);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .conv-sidebar-title {
            margin: 0 0 0.75rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--conv-text-primary);
            transition: color 0.3s ease;
        }

        .conv-contacts-list {
            max-height: calc(100% - 2.5rem);
            overflow-y: auto;
            padding-right: 0.35rem;
        }

        /* 💬 Contact Cards */
        .conv-contact-card {
            padding: 10px 12px;
            border-radius: 14px;
            margin-bottom: 8px;
            cursor: pointer;
            background: var(--conv-bg-primary);
            border: 1px solid transparent;
            transition: all 0.25s ease;
            box-shadow: 0 8px 18px rgba(79, 107, 163, 0.06);
        }

        .conv-contact-card:hover {
            background: var(--conv-bg-hover) !important;
            border-color: rgba(79, 107, 163, 0.22);
            box-shadow: 0 14px 26px rgba(79, 107, 163, 0.12);
        }

        .conv-contact-card.selected {
            background: var(--conv-selected-bg) !important;
            border-color: var(--conv-selected-border) !important;
            box-shadow: 0 18px 32px rgba(79, 107, 163, 0.16);
        }

        .conv-contact-card-inner {
            display: flex;
            gap: 0.85rem;
            align-items: flex-start;
            position: relative;
        }

        .conv-contact-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            background: linear-gradient(135deg, #4f6ba3, #6e94c3);
            border: 2px solid transparent;
            box-shadow: 0 10px 18px rgba(79, 107, 163, 0.18);
            flex-shrink: 0;
            transition: border-color 0.25s ease;
        }

        .conv-contact-card:hover .conv-contact-avatar,
        .conv-contact-card.selected .conv-contact-avatar {
            border-color: #4f6ba3;
        }

        .conv-contact-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .conv-contact-name {
            font-weight: 600;
            color: var(--conv-text-primary);
            transition: color 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
        }

        .conv-contact-message {
            color: var(--conv-text-secondary);
            font-size: 13px;
            margin-top: 2px;
            transition: color 0.3s ease;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .conv-contact-date {
            color: var(--conv-text-muted);
            font-size: 12px;
            margin-top: 4px;
            transition: color 0.3s ease;
        }

        /* 📄 Main Area */
        .conv-main {
            flex: 1;
            padding-left: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .conv-empty {
            color: var(--conv-text-secondary);
            font-size: 15px;
            text-align: center;
            margin-top: 40px;
            transition: color 0.3s ease;
        }

        .conv-section-title {
            font-weight: 600;
            color: var(--conv-text-primary);
            margin: 0;
            transition: color 0.3s ease;
            font-size: 1rem;
        }

        /* 💭 Message Bubbles */
        .conv-messages-area {
            max-height: 40vh;
            overflow-y: auto;
            padding: 8px;
            border-radius: 12px;
            background: var(--conv-bg-secondary);
            border: 1px solid var(--conv-border-light);
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .conv-message-wrapper {
            display: flex;
            margin-bottom: 10px;
            align-items: flex-end;
            gap: 0.75rem;
        }

        .conv-message-wrapper.start {
            justify-content: flex-start;
        }

        .conv-message-wrapper.end {
            justify-content: flex-end;
            flex-direction: row-reverse;
        }

        .conv-message-avatar {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: rgba(99, 102, 241, 0.15);
            color: #4f46e5;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 14px rgba(79, 70, 229, 0.18);
            flex-shrink: 0;
            overflow: hidden;
        }

        .conv-message-avatar.admin {
            background: rgba(79, 107, 163, 0.28);
            color: #223d6a;
            box-shadow: 0 6px 14px rgba(79, 107, 163, 0.28);
        }

        .conv-message-avatar.client {
            background: rgba(138, 174, 208, 0.28);
            color: #2c4d7d;
        }

        .conv-message-wrapper.end .conv-message-meta {
            flex-direction: row-reverse;
            text-align: right;
            justify-content: flex-end;
            align-items: flex-end;
        }

        .conv-message-wrapper.end .conv-edit-actions {
            justify-content: flex-start;
        }

        .conv-message-bubble {
            max-width: 70%;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--conv-border);
            box-shadow: 0 1px 2px var(--conv-shadow);
            transition: all 0.3s ease;
            backdrop-filter: saturate(180%) blur(8px);
        }

        .conv-message-bubble.admin {
            background: var(--conv-admin-bubble);
        }

        .conv-message-bubble.client {
            background: var(--conv-client-bubble);
        }

        .conv-message-meta {
            font-size: 12px;
            color: var(--conv-text-secondary);
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            transition: color 0.3s ease;
        }

        .conv-message-body {
            white-space: pre-wrap;
            color: var(--conv-text-primary);
            transition: color 0.3s ease;
        }

        /* 🔘 Buttons */
        .conv-btn {
            border: none;
            padding: 4px 8px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .conv-btn:disabled {
            cursor: not-allowed;
            opacity: 0.65;
            transform: none;
        }

        .conv-btn-primary {
            background: linear-gradient(135deg, var(--conv-btn-primary), #6e94c3);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(79, 107, 163, 0.28);
        }

        .conv-btn-primary:hover {
            background: linear-gradient(135deg, var(--conv-btn-primary-hover), #8aaed0);
            transform: translateY(-1px);
        }

        .conv-btn-save {
            background: linear-gradient(135deg, var(--conv-btn-primary), #6e94c3);
            color: #ffffff;
            padding: 6px 12px;
        }

        .conv-btn-cancel {
            background: var(--conv-btn-secondary);
            color: var(--conv-btn-secondary-text);
            padding: 6px 12px;
            border: 1px solid rgba(79, 107, 163, 0.18);
        }

        .conv-btn-cancel:hover {
            background: var(--conv-btn-secondary-hover);
            transform: translateY(-1px);
        }

        .conv-btn-edit {
            background: var(--conv-bg-primary);
            border: 1px solid var(--conv-border);
            padding: 4px 8px;
            transition: all 0.2s ease;
        }

        .conv-btn-edit:hover {
            background: var(--conv-bg-hover);
        }

        /* 📝 Textareas */
        .conv-textarea {
            width: 100%;
            border-radius: 8px;
            border: 1px solid var(--conv-input-border);
            padding: 8px;
            font-size: 14px;
            background: var(--conv-input-bg);
            color: var(--conv-text-primary);
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .conv-textarea:focus {
            outline: none;
            border-color: var(--conv-btn-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        /* 💬 Reply Areas */
        .conv-reply-area {
            margin-top: 12px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .conv-reply-flex {
            flex: 1;
        }

        .conv-reply-btn {
            background: linear-gradient(135deg, var(--conv-btn-primary), #6e94c3);
            color: #ffffff;
            padding: 8px 18px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.22s ease;
            box-shadow: 0 14px 24px rgba(79, 107, 163, 0.28);
        }

        .conv-reply-btn:hover {
            background: linear-gradient(135deg, var(--conv-btn-primary-hover), #8aaed0);
            transform: translateY(-1px);
        }

        .conv-reply-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .conv-reply-btn-client {
            background: var(--conv-btn-secondary);
            color: var(--conv-btn-secondary-text);
            border: 1px solid rgba(79, 107, 163, 0.18);
        }

        .conv-reply-btn-client:hover {
            background: var(--conv-btn-secondary-hover);
            transform: translateY(-1px);
        }

        .conv-reply-btn-client:disabled {
            transform: none;
        }

        /* 📏 Divider */
        .conv-divider {
            margin: 1rem 0;
            border: none;
            border-top: 1px solid var(--conv-border);
            transition: border-color 0.3s ease;
        }

        /* 🛠️ Edit Actions */
        .conv-edit-actions {
            display: flex;
            gap: 6px;
        }

        .conv-main-edit {
            margin-top: 8px;
            display: flex;
            gap: 8px;
        }

        .conv-main-message {
            margin-top: 12px;
            border: 1px solid var(--conv-border-light);
            border-radius: 16px;
            background: var(--conv-bg-primary);
            padding: 1rem;
            box-shadow: 0 16px 28px rgba(15, 23, 42, 0.08);
        }

        .conv-conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(14, 165, 233, 0.08));
            border: 1px solid var(--conv-border-light);
            border-radius: 18px;
            padding: 1rem 1.25rem;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
        }

        .conv-conversation-identity {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .conv-avatar {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(79, 107, 163, 0.28), rgba(138, 174, 208, 0.22));
            color: var(--conv-btn-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            overflow: hidden;
            box-shadow: 0 14px 26px rgba(79, 107, 163, 0.18);
        }

        .conv-avatar img,
        .conv-message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: inherit;
            border: 2px solid transparent;
            transition: border-color 0.25s ease;
        }

        .conv-avatar img:hover,
        .conv-message-avatar img:hover {
            border-color: rgba(79, 107, 163, 0.6);
        }

        .conv-avatar-initials {
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
        }

        .conv-identity-text {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .conv-identity-name {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--conv-text-primary);
        }

        .conv-identity-meta {
            font-size: 0.82rem;
            color: var(--conv-text-secondary);
        }

        .conv-identity-snippet {
            font-size: 0.9rem;
            color: var(--conv-text-secondary);
            margin: 0;
            line-height: 1.4;
        }

        .conv-header-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .conv-channel-badge {
            background: rgba(79, 107, 163, 0.2);
            color: #4f6ba3;
            border-radius: 999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .conv-status-badge {
            background: rgba(99, 102, 241, 0.15);
            color: #4f46e5;
            border-radius: 999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .conv-thread-title {
            margin-bottom: 0.5rem;
        }

        .conv-messages-area::-webkit-scrollbar {
            width: 6px;
        }

        .conv-messages-area::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.35);
            border-radius: 3px;
        }

        .conv-contacts-list::-webkit-scrollbar {
            width: 6px;
        }

        .conv-contacts-list::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.4);
            border-radius: 3px;
        }

        /* Sticky header for sidebar so title remains visible while contacts scroll */
        .conv-sidebar-head {
            position: sticky;
            top: 0.75rem;
            z-index: 10;
            background: transparent; /* inherits parent's background */
            padding-bottom: 0.5rem;
        }

        /* When many contacts, allow the list to scroll while keeping header visible */
        .conv-contacts-list--scrollable {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding-right: 0.25rem; /* avoid content under scrollbar */
        }

        @media (max-width: 1200px) {
            .conv-sidebar {
                width: 280px;
            }

            .conv-main {
                padding-left: 0;
            }
        }

        @media (max-width: 1024px) {
            .conv-container {
                flex-direction: column;
            }

            .conv-sidebar {
                position: relative;
                top: 0;
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--conv-border);
                padding-right: 0;
                padding-bottom: 1rem;
                margin-bottom: 1.25rem;
                max-height: none;
            }

            .conv-contacts-list {
                max-height: 18rem;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 0.85rem;
                padding-right: 0;
            }

            .conv-contact-card {
                margin-bottom: 0;
            }

            .conv-main {
                width: 100%;
            }

            .conv-conversation-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .conv-header-meta {
                width: 100%;
                justify-content: flex-start;
            }
        }

        @media (max-width: 768px) {
            .conv-container {
                gap: 1.25rem;
            }

            .conv-sidebar-title {
                font-size: 1rem;
            }

            .conv-contacts-list {
                display: flex;
                overflow-x: auto;
                padding-bottom: 0.5rem;
                gap: 0.75rem;
            }

            .conv-contacts-list::-webkit-scrollbar {
                height: 6px;
            }

            .conv-contact-card {
                min-width: 240px;
            }

            .conv-conversation-header {
                padding: 0.9rem;
            }

            .conv-avatar {
                width: 42px;
                height: 42px;
            }

            .conv-messages-area {
                max-height: 50vh;
            }

            .conv-main-message {
                padding: 0.85rem;
            }

            .conv-reply-area {
                flex-direction: column;
            }

            .conv-reply-btn,
            .conv-reply-btn-client {
                width: 100%;
            }
        }

        @media (max-width: 560px) {
            .conv-contact-name {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .conv-channel-badge,
            .conv-status-badge {
                font-size: 0.7rem;
            }

            .conv-thread-title {
                font-size: 0.95rem;
            }

            .conv-message-bubble {
                max-width: 85%;
            }

            .conv-message-avatar {
                width: 32px;
                height: 32px;
                border-radius: 10px;
                font-size: 0.75rem;
            }
        }
    </style>
</div>

