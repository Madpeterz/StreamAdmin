{{-- $actions: list of ['label', 'href', 'icon'?, 'variant'?, 'badge'?] --}}
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <flux:heading size="xl" level="1">{{ $title }}</flux:heading>
        @if ($description)
            <flux:text class="mt-1">{{ $description }}</flux:text>
        @endif
    </div>
    @if (! empty($actions))
        <div class="flex flex-wrap gap-2">
            @foreach ($actions as $action)
                <flux:button :variant="$action['variant'] ?? 'primary'" :icon="$action['icon'] ?? null" :href="$action['href']" wire:navigate>
                    {{ $action['label'] }}
                    @if (! empty($action['badge']))
                        <flux:badge size="sm" color="amber" class="ml-1">{{ $action['badge'] }}</flux:badge>
                    @endif
                </flux:button>
            @endforeach
        </div>
    @endif
</div>
