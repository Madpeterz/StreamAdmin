<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('clients.index')" wire:navigate>Clients</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Remove expired</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div>
        <flux:heading size="xl" level="1">Remove expired clients</flux:heading>
        <flux:text class="mt-1">Rentals that ran out and got their last notice. Removing one frees its stream (marked as needing work) and ends the rental for good. Clients with a details request waiting are not listed.</flux:text>
    </div>

    @if ($rentals->isEmpty())
        <flux:card class="flex flex-col items-center gap-3 py-12 text-center">
            <flux:icon.check-circle class="size-8 text-emerald-500" />
            <flux:heading>No clients to remove right now</flux:heading>
        </flux:card>
    @else
        <flux:card class="p-0">
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 pt-5">
                <flux:heading size="lg">{{ $rentals->count() }} expired</flux:heading>
                <flux:button size="sm" variant="ghost" wire:click="selectAll">Tick all</flux:button>
            </div>
            <div class="px-6 py-3">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column></flux:table.column>
                        <flux:table.column>Client</flux:table.column>
                        <flux:table.column>Stream</flux:table.column>
                        <flux:table.column>Expired</flux:table.column>
                        <flux:table.column>Note</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($rentals as $rental)
                            <flux:table.row :key="$rental->id">
                                <flux:table.cell><flux:checkbox wire:model="selected" :value="$rental->rental_uid" :aria-label="'Remove '.($rental->avatar->avatar_name ?? $rental->rental_uid)" /></flux:table.cell>
                                <flux:table.cell>
                                    <a href="{{ route('clients.show', $rental) }}" wire:navigate class="font-medium hover:underline">{{ $rental->avatar->avatar_name ?? 'Unknown avatar' }}</a>
                                </flux:table.cell>
                                <flux:table.cell class="text-sm">{{ $rental->stream?->server?->domain }}<span class="text-zinc-400">:{{ $rental->stream?->port }}</span></flux:table.cell>
                                <flux:table.cell class="text-sm text-zinc-600 dark:text-zinc-300">{{ $rental->expire_unixtime->diffForHumans() }}</flux:table.cell>
                                <flux:table.cell class="max-w-64 truncate text-xs text-zinc-500" title="{{ $rental->message }}">{{ $rental->message }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
            <div class="flex justify-end px-6 pb-5">
                <flux:button variant="danger" icon="trash" wire:click="remove" wire:confirm="Remove the ticked clients? This cannot be undone.">Remove ticked clients</flux:button>
            </div>
        </flux:card>
    @endif
</div>
