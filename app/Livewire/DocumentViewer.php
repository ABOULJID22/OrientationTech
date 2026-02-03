<?php

namespace App\Livewire;

use App\Models\Purchase;
use App\Models\TradeOperation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Filament\Notifications\Notification;

class DocumentViewer extends Component
{
    public Collection|array $items = [];
    public ?string $context = null; // 'purchase' | 'trade'
    public ?int $recordId = null;
    public bool $canDeleteAll = false; // Super/admin/staff can delete everything in this context
    public ?int $userId = null; // current user id for client ownership checks

    public function mount($items = [], ?string $context = null, ?int $recordId = null): void
    {
        // Normalize to array for internal use
        if ($items instanceof Collection) {
            $items = $items->all();
        }
        $this->items = array_values(Arr::wrap($items));
        $this->context = $context;
        $this->recordId = $recordId;

        // Precompute who can delete everything vs. only own uploads
        $user = auth()->user();
        $this->userId = $user?->id;
        $isSuper = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        // Only superadmin can delete all; everyone else limited to their own uploads
        $this->canDeleteAll = $isSuper;
    }

    public function deleteItem(int $index): void
    {
        if (!isset($this->items[$index])) {
            throw ValidationException::withMessages(['item' => __("documentViewer.errors.not_found")]);
        }
        $entry = $this->items[$index];
        $path = is_string($entry) ? $entry : ($entry['path'] ?? ($entry[0] ?? null));
        if (!$path) {
            throw ValidationException::withMessages(['path' => __("documentViewer.errors.invalid_path")]);
        }

        if ($this->context === 'purchase') {
            $this->deleteFromPurchase($path);
        } elseif ($this->context === 'trade') {
            $this->deleteFromTrade($path);
        } else {
            throw ValidationException::withMessages(['context' => __("documentViewer.errors.unknown_context")]);
        }

        // Remove locally for instant UI
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    protected function normalize(string $value): string
    {
        $p = explode('?', $value, 2)[0];
        if (preg_match('#^https?://#i', $p)) {
            $parts = parse_url($p);
            $p = $parts['path'] ?? $p;
        }
        $p = ltrim(str_replace('\\', '/', $p), '/');
        if (Str::startsWith($p, 'storage/')) {
            $p = substr($p, strlen('storage/'));
        }
        if (($pos = strpos($p, '/storage/')) !== false) {
            $p = substr($p, $pos + 9);
        }
        return $p;
    }

    protected function deleteFromPurchase(string $rawPath): void
    {
        $purchase = Purchase::findOrFail($this->recordId);
        $user = auth()->user();
    $isSuper = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

        $path = $this->normalize($rawPath);
        $attachments = (array)($purchase->attachments ?? []);

        $matchIndex = null;
        foreach ($attachments as $i => $entry) {
            $p = is_string($entry) ? $entry : ($entry['path'] ?? ($entry[0] ?? null));
            $pn = $p ? $this->normalize($p) : null;
            if ($pn && $pn === $path) { $matchIndex = $i; break; }
        }
        if (is_null($matchIndex)) {
            // Try photos as well
            $photos = (array)($purchase->photos ?? []);
            foreach ($photos as $i => $entry) {
                $p = is_string($entry) ? $entry : ($entry['path'] ?? ($entry[0] ?? null));
                $pn = $p ? $this->normalize($p) : null;
                if ($pn && $pn === $path) { $matchIndex = ['photos', $i]; break; }
            }
        }
        if (is_null($matchIndex)) {
            throw ValidationException::withMessages(['path' => __("documentViewer.errors.not_found")]);
        }

        $allowed = false;
        $entry = is_array($matchIndex) ? $purchase->photos[$matchIndex[1]] : $attachments[$matchIndex];
    $uploader = is_string($entry) ? null : ($entry['uploaded_by'] ?? ($entry['user_id'] ?? null));
    if ($isSuper) { $allowed = true; }
        elseif ($user && $uploader && (int)$user->id === (int)$uploader) { $allowed = true; }
        elseif (!$uploader) {
            $base = basename(is_array($matchIndex) ? (is_string($entry) ? $entry : ($entry['path'] ?? ($entry[0] ?? ''))) : (is_string($entry) ? $entry : ($entry['path'] ?? ($entry[0] ?? ''))));
            if (preg_match('/^uid(\d+)__*/i', $base, $m)) {
                $ownerId = (int)($m[1] ?? 0);
                if ($ownerId && $user && (int)$user->id === $ownerId) { $allowed = true; }
            }
        }
        if (!$allowed) {
            throw ValidationException::withMessages(['auth' => __("documentViewer.errors.unauthorized")]);
        }

        try {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Throwable $e) {
            // ignore storage errors
        }

        if (is_array($matchIndex)) {
            $photos = (array)($purchase->photos ?? []);
            unset($photos[$matchIndex[1]]);
            $purchase->photos = array_values($photos);
        } else {
            unset($attachments[$matchIndex]);
            $purchase->attachments = array_values($attachments);
        }
        $purchase->save();

        Notification::make()
            ->title(__("documentViewer.deleted"))
            ->success()
            ->send();
    }

    protected function deleteFromTrade(string $rawPath): void
    {
        $trade = TradeOperation::findOrFail($this->recordId);
        $user = auth()->user();
    $isSuper = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

        $path = $this->normalize($rawPath);
        $attachments = (array)($trade->attachments ?? []);
        $photos = (array)($trade->photos ?? []);

        $match = null; $group = 'attachments';
        foreach ($attachments as $i => $entry) {
            $p = is_string($entry) ? $entry : ($entry['path'] ?? ($entry[0] ?? null));
            $pn = $p ? $this->normalize($p) : null;
            if ($pn && $pn === $path) { $match = $i; break; }
        }
        if (is_null($match)) {
            foreach ($photos as $i => $entry) {
                $p = is_string($entry) ? $entry : ($entry['path'] ?? ($entry[0] ?? null));
                $pn = $p ? $this->normalize($p) : null;
                if ($pn && $pn === $path) { $match = $i; $group = 'photos'; break; }
            }
        }
        if (is_null($match)) {
            throw ValidationException::withMessages(['path' => __("documentViewer.errors.not_found")]);
        }

        $allowed = false;
        $entry = $group === 'attachments' ? $attachments[$match] : $photos[$match];
    $uploader = is_string($entry) ? null : ($entry['uploaded_by'] ?? ($entry['user_id'] ?? null));
    if ($isSuper) { $allowed = true; }
        elseif ($user && $uploader && (int)$user->id === (int)$uploader) { $allowed = true; }
        elseif (!$uploader) {
            $base = basename(is_string($entry) ? $entry : ($entry['path'] ?? ($entry[0] ?? '')));
            if (preg_match('/^uid(\d+)__*/i', $base, $m)) {
                $ownerId = (int)($m[1] ?? 0);
                if ($ownerId && $user && (int)$user->id === $ownerId) { $allowed = true; }
            }
        }
        if (!$allowed) {
            throw ValidationException::withMessages(['auth' => __("documentViewer.errors.unauthorized")]);
        }

        try {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Throwable $e) {
            // ignore storage errors
        }

        if ($group === 'attachments') {
            unset($attachments[$match]);
            $trade->attachments = array_values($attachments);
        } else {
            unset($photos[$match]);
            $trade->photos = array_values($photos);
        }
        $trade->save();

        Notification::make()
            ->title(__("documentViewer.deleted"))
            ->success()
            ->send();
    }

    /**
     * Whether the current user can delete this specific entry (for showing the button).
     * Super/admin/staff with update permission can delete all; clients can delete only their own uploads
     * determined by 'uploaded_by' or filename prefix uid{userId}__.
     */
    public function canDeleteEntry($entry): bool
    {
        if ($this->canDeleteAll) {
            return true;
        }
        return $this->ownsEntry($entry);
    }

    protected function extractPath($entry): ?string
    {
        if (is_string($entry)) {
            return $entry;
        }
        if (is_array($entry)) {
            return $entry['path'] ?? ($entry[0] ?? null);
        }
        return null;
    }

    protected function ownsEntry($entry): bool
    {
        $uid = (int)($this->userId ?? 0);
        if (!$uid) {
            return false;
        }
        // uploaded_by or user_id metadata
        if (is_array($entry)) {
            $uploader = $entry['uploaded_by'] ?? ($entry['user_id'] ?? null);
            if ($uploader !== null && (int)$uploader === $uid) {
                return true;
            }
        }
        // legacy filename uid{userId}__*
        $path = $this->extractPath($entry) ?? '';
        $base = basename($path);
        if (preg_match('/^uid(\d+)__/', $base, $m)) {
            $ownerId = (int)($m[1] ?? 0);
            return $ownerId === $uid && $ownerId > 0;
        }
        return false;
    }

    public function render()
    {
        return view('livewire.document-viewer');
    }
}
