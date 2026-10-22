<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('texture-packs.index')" wire:navigate>Texture packs</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $pack?->name ?? 'New texture pack' }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div>
        <flux:heading size="xl" level="1">{{ $pack?->name ?? 'New texture pack' }}</flux:heading>
        <flux:text class="mt-1">
            @if ($pack)
                Texture pack ID <span class="font-mono">{{ $pack->id }}</span> · set this on your in-world vendors and servers.
            @else
                The textures your vendors and servers show in each state, as Second Life texture UUIDs.
            @endif
        </flux:text>
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:card class="flex flex-col gap-5">
            <flux:input wire:model="name" label="Name" class="max-w-sm" />
        </flux:card>

        <flux:card class="grid gap-x-8 gap-y-5 md:grid-cols-2">
            @foreach (\App\Livewire\TexturePacks\Edit::TEXTURES as $column => [$label, $when])
                <div wire:key="texture-{{ $column }}">
                    <x-ui.texture-input :model="'textures.'.$column" :label="$label" :description="$when" />
                    <flux:error :name="'textures.'.$column" />
                </div>
            @endforeach
        </flux:card>

        <div class="flex flex-wrap items-center justify-between gap-3">
            @if ($pack)
                <flux:button variant="ghost" icon="trash" class="text-red-600!" wire:click="delete" wire:confirm="Remove {{ $pack->name }}? Objects set to texture pack {{ $pack->id }} will need a new one.">Remove texture pack</flux:button>
            @else
                <span></span>
            @endif
            <div class="flex gap-2">
                <flux:button :href="route('texture-packs.index')" variant="ghost" wire:navigate>Back</flux:button>
                <flux:button type="submit" variant="primary">{{ $pack ? 'Save texture pack' : 'Create texture pack' }}</flux:button>
            </div>
        </div>
    </form>
</div>
