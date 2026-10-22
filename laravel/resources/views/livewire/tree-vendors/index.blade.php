<div class="mx-auto flex w-full max-w-7xl flex-col gap-6" wire:poll.45s>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Tree vendors</flux:heading>
            <flux:text class="mt-1">Vendors that offer a choice of packages. Each one is linked in-world by its ID.</flux:text>
        </div>
        <flux:modal.trigger name="tree-create">
            <flux:button variant="primary" icon="plus">New tree vendor</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($vendors as $vendor)
            <a href="{{ route('tree-vendors.edit', $vendor) }}" wire:navigate wire:key="tree-{{ $vendor->id }}"
               class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-6 shadow-xs transition hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading>{{ $vendor->name }}</flux:heading>
                        <flux:text class="text-xs">Tree vendor ID <span class="font-mono">{{ $vendor->id }}</span>{{ $vendor->hide_soldout ? ' · hides sold out' : '' }}</flux:text>
                    </div>
                    <flux:badge size="sm">{{ $vendor->packages->count() }} {{ \Illuminate\Support\Str::plural('package', $vendor->packages->count()) }}</flux:badge>
                </div>
                <div class="flex flex-col gap-1.5">
                    @forelse ($vendor->packages->take(5) as $package)
                        <div class="flex items-center justify-between gap-2 text-sm">
                            <span class="truncate">{{ $package->name }}</span>
                            <flux:badge size="sm" :color="($ready[$package->id] ?? 0) > 0 ? 'emerald' : 'red'">{{ $ready[$package->id] ?? 0 }} ready</flux:badge>
                        </div>
                    @empty
                        <flux:text class="text-sm">No packages yet.</flux:text>
                    @endforelse
                    @if ($vendor->packages->count() > 5)
                        <flux:text class="text-xs">and {{ $vendor->packages->count() - 5 }} more</flux:text>
                    @endif
                </div>
            </a>
        @empty
            <flux:card class="col-span-full flex flex-col items-center gap-3 py-12 text-center">
                <flux:icon.squares-2x2 class="size-8 text-zinc-400" />
                <flux:heading>No tree vendors yet</flux:heading>
                <flux:text>A tree vendor lets clients pick from several packages at one vendor.</flux:text>
            </flux:card>
        @endforelse
    </div>

    <flux:modal name="tree-create" class="md:w-md">
        <form wire:submit="create" class="flex flex-col gap-5">
            <flux:heading size="lg">New tree vendor</flux:heading>
            <flux:input wire:model="name" label="Name" placeholder="Main store tree" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Create</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
