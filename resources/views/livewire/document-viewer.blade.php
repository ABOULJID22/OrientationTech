<div>
    <div class="document-grid">
        @foreach ($items as $i => $item)
            @php
                $path = is_string($item) ? $item : ($item['path'] ?? ($item[0] ?? null));
                $name = is_string($item) ? basename($path) : ($item['name'] ?? basename($path ?? ''));
                $isFullUrl = is_string($path) && preg_match('#^https?://#i', $path);
                $url = $path ? ($isFullUrl ? $path : \Illuminate\Support\Facades\Storage::disk('public')->url($path)) : '#';
                $isImage = in_array(strtolower(pathinfo($path ?? '', PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp','bmp','svg'], true);
            @endphp
            <div class="document-card">
                <div class="document-card-thumbnail">
                    @if ($isImage && $url !== '#')
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="thumbnail-link">
                            <img src="{{ $url }}" alt="{{ $name }}" loading="lazy" class="thumbnail-image" />
                        </a>
                    @else
                        <x-heroicon-o-document-text class="document-icon" />
                    @endif
                </div>
                <div class="document-card-details">
                    <p class="document-card-name">{{ $name }}</p>
                    <div class="document-card-actions">
                        @unless($isImage)
                            <x-filament::button tag="a" :href="$url" target="_blank" rel="noopener noreferrer" icon="heroicon-o-arrow-top-right-on-square" size="sm">{{ __('documentViewer.open') }}</x-filament::button>
                        @endunless
                        <x-filament::button tag="a" :href="$url" download="{{ $name }}" icon="heroicon-o-arrow-down-tray" size="sm">{{ __('documentViewer.download') }}</x-filament::button>
                        @if($this->canDeleteEntry($item))
                            <x-filament::button color="danger" size="sm" icon="heroicon-o-trash" wire:click="deleteItem({{ $i }})">{{ __('documentViewer.delete') }}</x-filament::button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
