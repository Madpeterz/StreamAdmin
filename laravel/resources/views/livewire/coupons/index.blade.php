<div class="mx-auto flex w-full max-w-7xl flex-col gap-6" wire:poll.60s>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Coupons</flux:heading>
            <flux:text class="mt-1">Second Life Marketplace listings that add credit to an avatar's account when bought.</flux:text>
        </div>
        @can('owner')
            <flux:modal.trigger name="coupon-form">
                <flux:button variant="primary" icon="plus" wire:click="resetForm">Add coupon</flux:button>
            </flux:modal.trigger>
        @endcan
    </div>

    @unless ($enabled)
        <flux:callout icon="exclamation-triangle" color="amber">
            <flux:callout.heading>Marketplace coupons are switched off</flux:callout.heading>
            <flux:callout.text>Purchases are ignored until coupons are enabled in System settings.</flux:callout.text>
        </flux:callout>
    @endunless

    <flux:callout icon="link" color="zinc">
        <flux:callout.text>
            In your Marketplace store settings, set the <strong>Automatic Notification Service URL</strong> to
            <span class="font-mono">{{ $ansUrl }}</span> and use the ANS salt from System settings.
            Credits can be spent at vendors owned by the system owner.
        </flux:callout.text>
    </flux:callout>

    <flux:card class="p-0">
        <div class="px-6 py-2">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Listing</flux:table.column>
                    <flux:table.column align="end">Price</flux:table.column>
                    <flux:table.column align="end">Credit given</flux:table.column>
                    <flux:table.column align="end">Bonus</flux:table.column>
                    <flux:table.column align="end">Sold</flux:table.column>
                    <flux:table.column>Last sale</flux:table.column>
                    @can('owner')
                        <flux:table.column></flux:table.column>
                    @endcan
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($coupons as $coupon)
                        <flux:table.row :key="$coupon->id">
                            <flux:table.cell>
                                <flux:link :href="'https://marketplace.secondlife.com/p/listing/'.$coupon->listing_id" external class="font-mono">{{ $coupon->listing_id }}</flux:link>
                            </flux:table.cell>
                            <flux:table.cell align="end" class="tabular-nums">L$ {{ number_format($coupon->cost) }}</flux:table.cell>
                            <flux:table.cell align="end" class="font-medium tabular-nums">L$ {{ number_format($coupon->credit) }}</flux:table.cell>
                            <flux:table.cell align="end">
                                @php($bonus = $coupon->cost > 0 ? round(($coupon->credit - $coupon->cost) / $coupon->cost * 100) : 0)
                                @if ($bonus > 0)
                                    <flux:badge size="sm" color="emerald">+{{ $bonus }}%</flux:badge>
                                @elseif ($bonus < 0)
                                    <flux:badge size="sm" color="red">{{ $bonus }}%</flux:badge>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end" class="tabular-nums">{{ number_format($coupon->claims) }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $coupon->last_claim?->diffForHumans() ?? 'Never' }}</flux:table.cell>
                            @can('owner')
                                <flux:table.cell align="end">
                                    <flux:dropdown align="end">
                                        <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            <flux:menu.item icon="pencil-square" wire:click="edit({{ $coupon->id }})">Edit</flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $coupon->id }})">Remove</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            @endcan
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7" class="py-8 text-center text-zinc-500">No coupons yet.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

    @can('owner')
        <flux:modal name="coupon-form" class="md:w-md">
            <form wire:submit="save" class="flex flex-col gap-5">
                <flux:heading size="lg">{{ $editingId ? 'Edit coupon' : 'Add coupon' }}</flux:heading>
                <flux:input wire:model="listing_id" type="number" min="1" label="Marketplace listing ID" description:trailing="The number in the listing URL." />
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="cost" type="number" min="1" label="Listing price (L$)" />
                    <flux:input wire:model="credit" type="number" min="1" label="Credit given (L$)" />
                </div>
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="primary">{{ $editingId ? 'Save' : 'Add coupon' }}</flux:button>
                </div>
            </form>
        </flux:modal>

        <flux:modal name="coupon-delete" class="md:w-md">
            <div class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">Remove this coupon?</flux:heading>
                    <flux:text class="mt-2">Purchases of this listing will no longer add credit. Remove or edit the Marketplace listing too.</flux:text>
                </div>
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                    <flux:button variant="danger" wire:click="delete">Remove coupon</flux:button>
                </div>
            </div>
        </flux:modal>
    @endcan
</div>
