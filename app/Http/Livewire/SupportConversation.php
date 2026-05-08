<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Contact;
use App\Models\User;
use App\Models\SupportMessage;
use App\Mail\ContactReplyMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action as FilamentAction;

class SupportConversation extends Component
{
    public $contacts = [];
    public $selectedContactId = null;
    public $replySubject = 'Réponse à votre demande de support';
    public $editingMessage = false;
    public $editedMessage = '';
    // thread message edit tracking
    public $editingThreadMessageId = null;
    public $editedThreadMessageBody = '';
    public $threadMessages = [];
    public $adminReplyBody = '';
    public $clientReplyBody = '';
    public $contactRequests = [];
    public $timelineEntries = [];
    public $mailStatusMessage = null;
    public $mailStatusIsError = false;

    public function mount()
    {
        $this->loadContacts();

        // If the current user is a client and has no existing contact thread,
        // create a new Contact so they can start a conversation immediately.
        $user = auth()->user();
        if ($user && method_exists($user, 'isClient') && $user->isClient()) {
            $exists = Contact::where('user_type', 'client')->where('email', $user->email)->exists();
            if (! $exists) {
                $contact = Contact::create([
                    'name' => $user->pharmacy_name ?: $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'user_type' => 'client',
                    'message' => '',
                ]);

                // reload contacts and preselect the created one
                $this->loadContacts();
                $this->selectedContactId = $contact->id;
                $this->loadMessages();
            }
        }
    }

    public function loadContacts()
    {
        // Build a merged conversations list based on:
        // - Contact records (user_type = client)
        // - Support messages (grouped by contact email)
        // - Client users (so SuperAdmin can see clients even without a Contact)

        $u = auth()->user();

        // Base contacts query (clients)
        $contactsQuery = Contact::where('user_type', 'client');

        if (! $u) {
            // guest: nothing to show
            $contactsQuery->whereRaw('1=0');
        } else {
            if (method_exists($u, 'isSuperAdmin') && $u->isSuperAdmin()) {
                // SuperAdmin: no additional constraint, we'll merge users below
            } elseif (method_exists($u, 'isClient') && $u->isClient()) {
                // clients only see their own threads
                $contactsQuery->where('email', $u->email);
            } else {
                // other roles: nothing
                $contactsQuery->whereRaw('1=0');
            }
        }

        $contacts = $contactsQuery->with(['user:id,name,email,avatar_url'])->orderBy('created_at')->get();

        // All support messages that relate to client contacts (we'll group them by contact email)
        $supportMessagesQuery = SupportMessage::with(['contact:id,email', 'user:id,name,email,avatar_url'])
            ->orderByDesc('created_at');

        // If current user is a client, limit support messages to their email only
        if ($u && method_exists($u, 'isClient') && $u->isClient()) {
            $supportMessagesQuery->whereHas('contact', function ($q) use ($u) {
                $q->where('email', $u->email);
            });
        }

        $supportMessages = $supportMessagesQuery->get();

        $supportMessagesByEmail = $supportMessages->groupBy(function (SupportMessage $message) {
            return $message->contact?->email;
        });

        // If SuperAdmin, include all users with client role even if they don't have a Contact
        $clientUsers = collect();
        if ($u && method_exists($u, 'isSuperAdmin') && $u->isSuperAdmin()) {
            $clientUsers = \App\Models\User::whereHas('roles', function ($q) {
                $q->where('name', \App\Models\User::ROLE_STUDENT);
            })->get(['id', 'name', 'email', 'avatar_url']);
        } elseif ($u && method_exists($u, 'isClient') && $u->isClient()) {
            $clientUsers = \App\Models\User::where('email', $u->email)->get(['id', 'name', 'email', 'avatar_url']);
        }

        // Collect all candidate emails from contacts, support messages and client users
        $emails = collect()
            ->merge($contacts->pluck('email'))
            ->merge($supportMessagesByEmail->keys())
            ->merge($clientUsers->pluck('email'))
            ->filter()
            ->unique()
            ->values();

    $conversations = collect();
    $syntheticId = -1; // negative ids for synthetic entries

    foreach ($emails as $email) {
            // Existing contact(s) for this email
            $items = $contacts->where('email', $email)->values();

            // Find related user (if any)
            $userModel = $clientUsers->firstWhere('email', $email) ?: ($items->first()?->user ?? null);

            // Latest contact request/message
            $latestContact = $items->sortByDesc('created_at')->first();

            // Latest support message for this email
            $latestSupportMessage = optional($supportMessagesByEmail->get($email))->first();

            // Build a Contact-like object: reuse existing Contact model when available, otherwise make a lightweight instance
            if ($latestContact) {
                $conv = $latestContact;
            } else {
                $conv = new Contact();
                // assign a synthetic negative id so Livewire actions can reference it
                $conv->id = $syntheticId--;
                $conv->email = $email;
                $conv->name = $userModel->name ?? null;
                $conv->message = '';
                $conv->created_at = $latestSupportMessage?->created_at ?? null;
            }

            // Attach user relation if we found a user
            if ($userModel) {
                $conv->setRelation('user', $userModel);
            }

            // Attach all requests (may be empty)
            $conv->setRelation('all_requests', $items);
            $conv->setAttribute('requests_count', $items->count());
            $conv->setRelation('latest_request', $latestContact ?: null);
            $conv->setAttribute('latest_created_at', $latestContact?->created_at ?? $conv->created_at ?? null);
            $conv->setAttribute('oldest_created_at', $items->last()?->created_at ?? $conv->created_at ?? null);

            // Determine last activity between contact and support message
            $candidates = collect();
            if ($latestContact && $latestContact->created_at) {
                $candidates->push([
                    'timestamp' => $latestContact->created_at,
                    'body' => $latestContact->message,
                    'sender_type' => 'contact',
                ]);
            }
            if ($latestSupportMessage) {
                $candidates->push([
                    'timestamp' => $latestSupportMessage->created_at,
                    'body' => $latestSupportMessage->body,
                    'sender_type' => $latestSupportMessage->sender_type,
                ]);
            }

            $last = $candidates->sortByDesc('timestamp')->first();
            if ($last) {
                $conv->setAttribute('last_message_body', $last['body']);
                $conv->setAttribute('last_message_at', $last['timestamp']);
                $conv->setAttribute('last_message_sender_type', $last['sender_type']);
            } else {
                // fallback to created_at
                $conv->setAttribute('last_message_at', $conv->created_at ?? null);
                $conv->setAttribute('last_message_body', $conv->message ?? '');
            }

            if ($items->count() > 1) {
                $conv->setAttribute('aggregated_messages', $items->pluck('message')->filter()->values());
            }

            $conversations->push($conv);
        }

        // Always sort by most recent activity (last_message_at) desc, fallback to latest_created_at
        $sorted = $conversations->sortByDesc(function ($c) {
            return $c->last_message_at?->getTimestamp() ?? ($c->latest_created_at?->getTimestamp() ?? 0);
        })->values();

        // limit to 200 for UI performance
        $this->contacts = $sorted->take(200);

        // If selected contact no longer exists in list, try to remap to the conversation with same email
        if ($this->selectedContactId) {
            $selected = Contact::find($this->selectedContactId);
            if ($selected) {
                $replacement = $this->contacts->firstWhere('email', $selected->email);
                if ($replacement) {
                    $this->selectedContactId = $replacement->id ?? $this->selectedContactId;
                }
            }
        }
    }

    public function selectContact($id)
    {
        // Support synthetic conversation ids for entries that didn't have a Contact record
        // If $id corresponds to a real Contact id, keep behavior. Otherwise try to resolve by email
        $this->mailStatusMessage = null;
        $this->mailStatusIsError = false;

        $contact = Contact::find($id);

        if (! $contact) {
            // look for the conversation entry in the prepared contacts collection
            $conv = collect($this->contacts)->firstWhere('id', $id);

            // Livewire may serialize model objects into arrays; attempt to normalize
            if (! $conv) {
                $conv = collect($this->contacts)->first(function ($entry) use ($id) {
                    // If entry is an array with an inner model representation
                    if (is_array($entry)) {
                        // try to find an 'id' key in the array or nested structures
                        return array_key_exists('id', $entry) && $entry['id'] === $id;
                    }
                    return false;
                });
            }

            // If we have a conversation entry with an email, resolve or create a Contact record
            if ($conv) {
                // Normalize to object-like access
                $email = is_object($conv) ? ($conv->email ?? null) : (is_array($conv) ? ($conv['email'] ?? null) : null);
                $name = is_object($conv) ? ($conv->name ?? null) : (is_array($conv) ? ($conv['name'] ?? null) : null);
                $phone = is_object($conv) ? ($conv->phone ?? null) : (is_array($conv) ? ($conv['phone'] ?? null) : null);
                $existing = Contact::where('email', $email)->first();
                if ($existing) {
                    $contact = $existing;
                } else {
                    // create a new contact for this email so threads/replies work as expected
                    $contact = Contact::create([
                        'name' => $name ?? $email,
                        'email' => $email,
                        'phone' => $phone ?? null,
                        'user_type' => 'client',
                        'message' => is_object($conv) ? ($conv->message ?? '') : (is_array($conv) ? ($conv['message'] ?? '') : ''),
                    ]);
                }
                // update the selectedContactId to the real id
                $this->selectedContactId = $contact->id;
            } else {
                // no conversation context available; just set and bail
                $this->selectedContactId = $id;
                $this->adminReplyBody = '';
                $this->clientReplyBody = '';
                $this->editedMessage = '';
                $this->editingMessage = false;
                $this->loadMessages();
                return;
            }
        } else {
            $this->selectedContactId = $contact->id;
        }

        $this->adminReplyBody = $contact?->reply_message ?? '';
        $this->clientReplyBody = '';
        $this->editedMessage = $contact?->message ?? '';
        $this->editingMessage = false;
        $this->loadMessages();
    }

    public function loadMessages()
    {
        if (! $this->selectedContactId) {
            $this->threadMessages = [];
            $this->contactRequests = [];
            $this->timelineEntries = collect();
            return;
        }

        $contact = Contact::find($this->selectedContactId);
        if (! $contact) {
            $this->threadMessages = [];
            $this->contactRequests = [];
            $this->timelineEntries = collect();
            return;
        }

        $this->contactRequests = Contact::where('email', $contact->email)
            ->with(['user:id,name,email,avatar_url'])
            ->orderBy('created_at')
            ->get();

        $threadQuery = SupportMessage::with('user')->whereHas('contact', function ($query) use ($contact) {
            $query->where('email', $contact->email);
        })->orderBy('created_at');

        $user = auth()->user();

        // SuperAdmin can see the full thread; Client can see the thread only if email matches
        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            $this->threadMessages = $threadQuery->get();
        } elseif ($user && method_exists($user, 'isClient') && $user->isClient() && $contact->email === $user->email) {
            $this->threadMessages = $threadQuery->get();
        } else {
            $this->threadMessages = collect();
        }

        $requestsCollection = $this->contactRequests instanceof Collection ? $this->contactRequests : collect($this->contactRequests);
        $messagesCollection = $this->threadMessages instanceof Collection ? $this->threadMessages : collect($this->threadMessages);

        $timelineEntries = collect();

        if ($requestsCollection->isNotEmpty()) {
            $timelineEntries = $timelineEntries->merge(
                $requestsCollection->map(function (Contact $request) {
                    return [
                        'type' => 'contact',
                        'model' => $request,
                        'timestamp' => $request->created_at,
                    ];
                })
            );
        }

        if ($messagesCollection->isNotEmpty()) {
            $timelineEntries = $timelineEntries->merge(
                $messagesCollection->map(function (SupportMessage $message) {
                    return [
                        'type' => 'thread',
                        'model' => $message,
                        'timestamp' => $message->created_at,
                    ];
                })
            );
        }

        $this->timelineEntries = $timelineEntries->sortBy('timestamp')->values();
    }

    public function sendReply()
    {
        $this->validate([
            'selectedContactId' => 'required|integer',
            'adminReplyBody' => 'required|string',
        ]);
        $user = auth()->user();
        // only SuperAdmin acts as support
        if (! $user || ! (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            $this->addError('adminReplyBody', 'Non autorisé');
            return;
        }

        $contact = Contact::find($this->selectedContactId);
        if (! $contact) {
            $this->addError('selectedContactId', 'Message introuvable');
            return;
        }

        // create a threaded support message as admin
        \App\Models\SupportMessage::create([
            'contact_id' => $contact->id,
            'user_id' => $user->id,
            'body' => $this->adminReplyBody,
            'sender_type' => 'admin',
        ]);

        // Notify the contact user (if exists) that support replied (database notification)
        try {
            $recipients = User::where('email', $contact->email)->get();
            if ($recipients->isNotEmpty()) {
                $notification = FilamentNotification::make()
                    ->title(trans('support.notification.admin_reply_title'))
                    ->body(trans('support.notification.admin_reply_body', ['who' => $user->name ?? 'Support']))
                    ->actions([
                        FilamentAction::make('view')
                            ->label('Voir la conversation')
                            ->url(route('filament.admin.pages.support-conversations'), true),
                    ]);

                $notification->sendToDatabase($recipients, true);
            }
        } catch (\Throwable $e) {
            Log::warning('SupportConversation: failed to send database notification to client', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
        }

        $mailSent = true;

        try {
            Mail::to($contact->email)->send(new ContactReplyMail($contact, $this->replySubject, $this->adminReplyBody));
            $this->mailStatusIsError = false;
            $message = trans('support.mail_sent_success');
            $this->mailStatusMessage = $message !== 'support.mail_sent_success'
                ? $message
                : 'E-mail envoyé avec succès.';
        } catch (\Throwable $e) {
            $mailSent = false;
            // Suppress any mail error message in the UI. Only log the exception for troubleshooting.
            $this->mailStatusIsError = false;
            $this->mailStatusMessage = null;

            Log::error('SupportConversation mail send failed (silenced in UI)', [
                'contact_id' => $contact->id,
                'exception' => $e,
            ]);
        }

        $contact->update([
            'replied_at' => now(),
            'reply_message' => $this->adminReplyBody,
            'replied_by' => $user->id,
        ]);

        // Always clear the admin reply input after sending (do not keep the draft in the UI).
        $this->adminReplyBody = '';
        $this->mailStatusMessage = null;
        $this->mailStatusIsError = false;

        $this->loadContacts();
        $this->loadMessages();
    }

    public function postMessageAsClient()
    {
        $this->validate([
            'selectedContactId' => 'required|integer',
            'clientReplyBody' => 'required|string',
        ]);

        $contact = Contact::find($this->selectedContactId);
        if (! $contact) {
            $this->addError('selectedContactId', 'Message introuvable');
            return;
        }

        $user = auth()->user();
        if (! $user || !(method_exists($user, 'isClient') && $user->isClient())) {
            $this->addError('clientReplyBody', 'Non autorisé');
            return;
        }

        // ensure the client is posting to their own contact (email match)
        if ($contact->email !== $user->email) {
            $this->addError('clientReplyBody', 'Non autorisé');
            return;
        }

        \App\Models\SupportMessage::create([
            'contact_id' => $contact->id,
            'user_id' => $user->id,
            'body' => $this->clientReplyBody,
            'sender_type' => 'client',
        ]);

        // Notify all super admins in Filament (database notification)
        try {
            $admins = User::role(User::ROLE_SUPER_ADMIN)->get();
            if ($admins->isNotEmpty()) {
                $notification = FilamentNotification::make()
                    ->title(trans('support.notification.new_message_title'))
                    ->body(trans('support.notification.new_message_body', ['name' => $user->name ?? $contact->email, 'email' => $contact->email]))
                    ->actions([
                        FilamentAction::make('view')
                            ->label('Voir les conversations')
                            ->url(route('filament.admin.pages.support-conversations'), true),
                    ]);

                $notification->sendToDatabase($admins, true);
            }
        } catch (\Throwable $e) {
            Log::warning('SupportConversation: failed to send admin database notification', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
        }

        $contact->update(['message' => $this->clientReplyBody]);

        $this->clientReplyBody = '';

        $this->loadContacts();
        $this->loadMessages();
        $this->mailStatusMessage = null;
        $this->mailStatusIsError = false;
    }

    public function startEditMessage()
    {
        $user = auth()->user();
        $contact = Contact::find($this->selectedContactId);
        // only the client owner can edit the original contact message
        if (! $user || !(method_exists($user, 'isClient') && $user->isClient()) || ! $contact || $contact->email !== $user->email) {
            $this->addError('editedMessage', 'Non autorisé');
            return;
        }

        $this->editingMessage = true;
    }

    public function cancelEditMessage()
    {
        $this->editingMessage = false;
        $this->editedMessage = Contact::find($this->selectedContactId)?->message ?? '';
    }

    public function saveMessage()
    {
        $this->validate([
            'selectedContactId' => 'required|integer',
            'editedMessage' => 'required|string',
        ]);
        $contact = Contact::find($this->selectedContactId);
        if (! $contact) {
            $this->addError('selectedContactId', 'Message introuvable');
            return;
        }
        // ensure only the original client who created the contact can change the main message
        $user = auth()->user();
        if (! $user || ! (method_exists($user, 'isClient') && $user->isClient()) || $contact->email !== $user->email) {
            $this->addError('editedMessage', 'Non autorisé');
            return;
        }

        $contact->update(['message' => $this->editedMessage]);
    $this->editingMessage = false;
    $this->loadContacts();
    $this->dispatch('filament-notify', ['message' => 'Message mis à jour']);
    }

    // Thread message editing: only allow the message owner to edit their message
    public function startEditThreadMessage($messageId)
    {
        $msg = \App\Models\SupportMessage::find($messageId);
        if (! $msg) {
            $this->addError('thread', 'Message introuvable');
            return;
        }

        $user = auth()->user();
        if (! $user || $msg->user_id !== $user->id) {
            $this->addError('thread', 'Non autorisé');
            return;
        }

        $this->editingThreadMessageId = $msg->id;
        $this->editedThreadMessageBody = $msg->body;
    }

    public function cancelEditThreadMessage()
    {
        $this->editingThreadMessageId = null;
        $this->editedThreadMessageBody = '';
        $this->loadMessages();
    }

    public function saveThreadMessage()
    {
        $this->validate([
            'selectedContactId' => 'required|integer',
            'editingThreadMessageId' => 'required|integer',
            'editedThreadMessageBody' => 'required|string',
        ]);

        $msg = \App\Models\SupportMessage::find($this->editingThreadMessageId);
        if (! $msg) {
            $this->addError('thread', 'Message introuvable');
            return;
        }

        $user = auth()->user();
        if (! $user || $msg->user_id !== $user->id) {
            $this->addError('thread', 'Non autorisé');
            return;
        }

        $msg->update(['body' => $this->editedThreadMessageBody]);
        $this->editingThreadMessageId = null;
        $this->editedThreadMessageBody = '';
        $this->loadMessages();
        $this->dispatch('filament-notify', ['message' => 'Message mis à jour']);
    }

    public function render()
    {
        return view('livewire.support-conversation');
    }
}
