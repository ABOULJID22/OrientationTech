@php
    $darkModeEnabled = isset($darkMode) ? $darkMode : false;
@endphp

<div class="support-conversation-root">
    <style>
        /* 🌓 Variables pour Light Mode */
        :root {
            --conv-text-primary: #111827;
            --conv-text-secondary: #6b7280;
            --conv-text-muted: #9ca3af;
            --conv-bg-primary: #ffffff;
            --conv-bg-secondary: #f9fafb;
            --conv-bg-hover: #f3f4f6;
            --conv-border: #e5e7eb;
            --conv-border-light: #f3f4f6;
            --conv-selected-bg: #eef2ff;
            --conv-selected-border: #6366f1;
            --conv-admin-bubble: #dcfce7;
            --conv-client-bubble: #ffffff;
            --conv-shadow: rgba(0,0,0,0.04);
            --conv-btn-primary: #6366f1;
            --conv-btn-primary-hover: #4f46e5;
            --conv-btn-secondary: #f3f4f6;
            --conv-btn-secondary-text: #374151;
            --conv-btn-secondary-hover: #e5e7eb;
            --conv-input-border: #d1d5db;
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
            --conv-selected-bg: #1e3a8a;
            --conv-selected-border: #3b82f6;
            --conv-admin-bubble: #064e3b;
            --conv-client-bubble: #1f2937;
            --conv-shadow: rgba(0,0,0,0.3);
            --conv-btn-primary: #3b82f6;
            --conv-btn-primary-hover: #2563eb;
            --conv-btn-secondary: #374151;
            --conv-btn-secondary-text: #e5e7eb;
            --conv-btn-secondary-hover: #4b5563;
            --conv-input-border: #4b5563;
            --conv-input-bg: #1f2937;
        }

        /* 📦 Container */
        .conv-container {
            display: flex;
            gap: 1.25rem;
            font-family: Inter, sans-serif;
            color: var(--conv-text-primary);
            transition: color 0.3s ease;
        }

        /* 📱 Sidebar */
        .conv-sidebar {
            width: 320px;
            border-right: 1px solid var(--conv-border);
            padding-right: 1rem;
            transition: border-color 0.3s ease;
        }

        .conv-sidebar-title {
            margin: 0 0 0.75rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--conv-text-primary);
            transition: color 0.3s ease;
        }

        .conv-contacts-list {
            max-height: 70vh;
            overflow-y: auto;
        }

        /* 💬 Contact Cards */
        .conv-contact-card {
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 6px;
            cursor: pointer;
            background: var(--conv-bg-primary);
            border: 1px solid var(--conv-border-light);
            transition: all 0.2s ease;
        }

        .conv-contact-card:hover {
            background: var(--conv-bg-hover) !important;
        }

        .conv-contact-card.selected {
            background: var(--conv-selected-bg) !important;
            border-color: var(--conv-selected-border) !important;
        }

        .conv-contact-name {
            font-weight: 600;
            color: var(--conv-text-primary);
            transition: color 0.3s ease;
        }

        .conv-contact-email {
            color: var(--conv-text-secondary);
            transition: color 0.3s ease;
        }

        .conv-contact-message {
            color: var(--conv-text-secondary);
            font-size: 13px;
            margin-top: 2px;
            transition: color 0.3s ease;
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
        }

        /* 💭 Message Bubbles */
        .conv-messages-area {
            max-height: 40vh;
            overflow-y: auto;
            padding: 8px;
        }

        .conv-message-wrapper {
            display: flex;
            margin-bottom: 10px;
        }

        .conv-message-wrapper.start {
            justify-content: flex-start;
        }

        .conv-message-wrapper.end {
            justify-content: flex-end;
        }

        .conv-message-bubble {
            max-width: 70%;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--conv-border);
            box-shadow: 0 1px 2px var(--conv-shadow);
            transition: all 0.3s ease;
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
        }

        .conv-btn-primary {
            background: var(--conv-btn-primary);
            color: white;
        }

        .conv-btn-primary:hover {
            background: var(--conv-btn-primary-hover);
        }

        .conv-btn-save {
            background: var(--conv-btn-primary);
            color: white;
            padding: 6px 12px;
        }

        .conv-btn-cancel {
            background: var(--conv-btn-secondary);
            color: var(--conv-btn-secondary-text);
            padding: 6px 12px;
        }

        .conv-btn-cancel:hover {
            background: var(--conv-btn-secondary-hover);
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
            background: var(--conv-btn-primary);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .conv-reply-btn:hover {
            background: var(--conv-btn-primary-hover);
        }

        .conv-reply-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .conv-reply-btn-client {
            background: var(--conv-btn-secondary);
            color: var(--conv-btn-secondary-text);
        }

        .conv-reply-btn-client:hover {
            background: var(--conv-btn-secondary-hover);
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
    </style>

    <div class="conv-container">
        <!-- Sidebar Contacts -->
        <div class="conv-sidebar">
            <h3 class="conv-sidebar-title">💬 {{ __('support.conversations') }}</h3>

            <div class="conv-contacts-list">
                @foreach($contacts as $c)
                    <div 
                        wire:click="selectContact({{ $c->id }})"
                        class="conv-contact-card {{ $selectedContactId == $c->id ? 'selected' : '' }}"
                    >
                        <div class="conv-contact-name">
                            {{ $c->name }}
                            <small class="conv-contact-email">— {{ $c->email }}</small>
                        </div>
                        <div class="conv-contact-message">
                            {{ Str::limit($c->message, 80) }}
                        </div>
                        <div class="conv-contact-date">
                            {{ $c->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Conversation Area -->
        <div class="conv-main">
            @if(!$selectedContactId)
                <div class="conv-empty">{{ __('support.select_conversation') }} 💭</div>
            @else
                @php($contact = \App\Models\Contact::find($selectedContactId))

                <!-- Header -->
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p class="conv-section-title">{{ __('support.main_message') }}</p>
                </div>

                <!-- Main Message -->
                <div style="margin-top: 12px;">
                    @if($editingMessage)
                        <textarea wire:model.defer="editedMessage" rows="6" class="conv-textarea"></textarea>
                        <div class="conv-main-edit">
                            <button wire:click="saveMessage" class="conv-btn conv-btn-save">💾 Enregistrer</button>
                            <button wire:click="cancelEditMessage" class="conv-btn conv-btn-cancel">❌ Annuler</button>
                        </div>
                    @else
                        <div class="conv-message-wrapper start">
                            <div class="conv-message-bubble client">
                                <div class="conv-message-meta">
                                    <div>{{ $contact->name ?? $contact->email }} — {{ $contact->created_at->format('d/m/Y H:i') }}</div>
                                    @php($u = auth()->user())
                                    @if($u && method_exists($u, 'isClient') && $u->isClient() && $contact->email === $u->email)
                                        <div class="conv-edit-actions">
                                            <button wire:click="startEditMessage" class="conv-btn-edit">✏️</button>
                                        </div>
                                    @endif
                                </div>
                                <div class="conv-message-body">{{ $contact->message }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                <hr class="conv-divider" />

                <!-- Thread -->
                <h4 class="conv-section-title" style="font-size: 1rem; margin-bottom: 0.5rem;">💭 {{ __('support.conversation') }}</h4>
                <div class="conv-messages-area">
                    @foreach($threadMessages as $m)
                        @php($isAdmin = $m->sender_type === 'admin')
                        <div class="conv-message-wrapper {{ $isAdmin ? 'end' : 'start' }}">
                            <div class="conv-message-bubble {{ $isAdmin ? 'admin' : 'client' }}">
                                <div class="conv-message-meta">
                                    <div>{{ $isAdmin ? '👨‍💼 Support' : ($m->user?->name ?? $contact->name) }} — {{ $m->created_at->format('d/m/Y H:i') }}</div>
                                    @php($u = auth()->user())
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
                    @endforeach
                </div>

                <hr class="conv-divider" />

                <!-- Admin Reply -->
                @php($u = auth()->user())
                @if($u && (method_exists($u, 'isSuperAdmin') && $u->isSuperAdmin() || method_exists($u, 'isAssistant') && $u->isAssistant()))
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
</div>

