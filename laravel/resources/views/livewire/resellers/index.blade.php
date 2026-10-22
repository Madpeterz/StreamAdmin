<div class="mx-auto flex w-full max-w-7xl flex-col gap-6" wire:poll.60s>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Resellers</flux:heading>
            <flux:text class="mt-1">Avatars who run StreamAdmin vendors, and the share of each sale they keep.</flux:text>
        </div>
        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search avatar" />
        </div>
    </div>

    <flux:callout icon="information-circle" color="sky">
        <flux:callout.text>
            New resellers appear here the first time they rez and use any StreamAdmin object. If new resellers are
            not accepted automatically (System settings), allow them here and set their rate.
            The system owner's own entry is ignored by vendors.
        </flux:callout.text>
    </flux:callout>

    <flux:card class="p-0">
        <div class="px-6 py-2">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Reseller</flux:table.column>
                    <flux:table.column>Allowed</flux:table.column>
                    <flux:table.column align="end">Rate</flux:table.column>
                    <flux:table.column align="end">Sales</flux:table.column>
                    <flux:table.column>Vendors</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($resellers as $reseller)
                        @php($sale = $sales->get($reseller->id))
                        @php($vendor = $vendors->get($reseller->avatar_id))
                        <flux:table.row :key="$reseller->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$reseller->avatar->avatar_name ?? '?'" />
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $reseller->avatar->avatar_name ?? 'Unknown avatar' }}</div>
                                        @if ($reseller->avatar_id === $ownerAvatarId)
                                            <div class="text-xs text-zinc-500">System owner</div>
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:switch :checked="$reseller->allowed" wire:click="toggleAllowed({{ $reseller->id }})" />
                            </flux:table.cell>
                            <flux:table.cell align="end" class="tabular-nums">{{ $reseller->rate }}%</flux:table.cell>
                            <flux:table.cell align="end" class="tabular-nums">
                                <div class="font-medium">L$ {{ number_format($sale->total ?? 0) }}</div>
                                <div class="text-xs text-zinc-500">{{ $sale->count ?? 0 }} sales{{ $sale?->last ? ' · last '.\Carbon\Carbon::createFromTimestamp($sale->last)->diffForHumans() : '' }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($vendor)
                                    <div class="text-sm">{{ $vendor->count }} {{ \Illuminate\Support\Str::plural('object', $vendor->count) }}</div>
                                    <x-ui.last-seen :at="\Carbon\CarbonImmutable::createFromTimestamp($vendor->last_seen)" :warn-after="120" :fail-after="3600" />
                                @else
                                    <span class="text-sm text-zinc-400">None yet</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:dropdown align="end">
                                    <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil-square" wire:click="edit({{ $reseller->id }})">Edit rate</flux:menu.item>
                                        <flux:menu.item icon="banknotes" :href="route('transactions.index', ['avatar' => $reseller->avatar?->avatar_name])" wire:navigate>Transactions</flux:menu.item>
                                        @if ($reseller->avatar_id !== $ownerAvatarId)
                                            <flux:menu.separator />
                                            <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $reseller->id }})">Remove</flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="py-8 text-center text-zinc-500">No resellers{{ $search !== '' ? ' match that search' : ' yet' }}.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

    <flux:modal name="reseller-form" class="md:w-md">
        <form wire:submit="save" class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">{{ $editing?->avatar?->avatar_name ?? 'Reseller' }}</flux:heading>
                <flux:text class="mt-1">The rate is the percentage of each sale the reseller keeps; the rest is paid to the system owner.</flux:text>
            </div>
            <flux:field variant="inline">
                <flux:switch wire:model="allowed" />
                <flux:label>Allowed to use vendors</flux:label>
            </flux:field>
            <flux:input wire:model="rate" type="number" min="1" max="100" label="Rate (%)" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Save</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="reseller-delete" class="md:w-md">
        <div class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Remove this reseller?</flux:heading>
                <flux:text class="mt-2">Their past sales are moved to the system owner. If they use a vendor again they are added back as a new reseller.</flux:text>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button variant="danger" wire:click="delete">Remove reseller</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
