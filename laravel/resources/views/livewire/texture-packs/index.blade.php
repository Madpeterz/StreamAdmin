<div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Texture packs</flux:heading>
            <flux:text class="mt-1">The textures your vendors and servers show in each state. Objects pick a pack by its ID.</flux:text>
        </div>
        <flux:button :href="route('texture-packs.create')" variant="primary" icon="plus" wire:navigate>New texture pack</flux:button>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($packs as $pack)
            <a href="{{ route('texture-packs.edit', $pack) }}" wire:navigate wire:key="pack-{{ $pack->id }}" class="group">
                <flux:card class="flex h-full flex-col gap-4 transition group-hover:border-zinc-300 dark:group-hover:border-zinc-600">
                    <div class="flex items-start justify-between gap-3">
                        <flux:heading size="lg">{{ $pack->name }}</flux:heading>
                        <flux:badge size="sm" color="zinc">ID {{ $pack->id }}</flux:badge>
                    </div>
                    <div class="grid grid-cols-5 gap-1.5">
                        @foreach (array_slice(array_keys(\App\Livewire\TexturePacks\Edit::TEXTURES), 0, 5) as $column)
                            <div class="relative aspect-4/3 overflow-hidden rounded-md border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800" title="{{ \App\Livewire\TexturePacks\Edit::TEXTURES[$column][0] }}">
                                <img src="https://picture-service.secondlife.com/{{ $pack->{$column} }}/256x192.jpg" alt="" loading="lazy" class="absolute inset-0 size-full object-cover" onerror="this.remove()">
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            </a>
        @empty
            <flux:card class="py-10 text-center text-zinc-500 md:col-span-2 xl:col-span-3">No texture packs yet.</flux:card>
        @endforelse
    </div>
</div>
