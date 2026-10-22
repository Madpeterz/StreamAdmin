<div class="mx-auto flex w-full max-w-6xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('avatars.index')" wire:navigate>Avatars</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $avatar?->avatar_name ?? 'Add avatar' }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    @if ($avatar)
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <flux:avatar size="lg" :name="$avatar->avatar_name" />
                <div>
                    <flux:heading size="xl" level="1">{{ $avatar->avatar_name }}</flux:heading>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
                        <span class="font-mono">{{ $avatar->avatar_uid }}</span>
                        <span>·</span>
                        <span>last seen {{ $avatar->last_used?->diffForHumans() ?? 'never' }}</span>
                        @foreach ($roles as [$label, $color])
                            <flux:badge size="sm" :color="$color">{{ $label }}</flux:badge>
                        @endforeach
                        @if ($avatar->ban)
                            <flux:badge size="sm" color="red" icon="no-symbol">Banned</flux:badge>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($rentals->isNotEmpty())
                    <flux:button icon="chat-bubble-left-ellipsis" :href="route('outbox.compose', ['source' => 'rental', 'id' => $rentals->first()->id])" wire:navigate>Message</flux:button>
                @endif
                @can('owner')
                    @if ($avatar->ban)
                        <flux:button icon="check-circle" wire:click="unban">Lift ban</flux:button>
                    @else
                        <flux:button icon="no-symbol" wire:click="ban" wire:confirm="Ban {{ $avatar->avatar_name }}? They will not be able to rent or renew.">Ban</flux:button>
                    @endif
                @endcan
            </div>
        </div>
    @else
        <div>
            <flux:heading size="xl" level="1">Add avatar</flux:heading>
            <flux:text class="mt-1">Avatars are added automatically when they use a vendor or HUD; add one here to ban them or make them staff first.</flux:text>
        </div>
    @endif

    <div @class(['grid gap-6', 'lg:grid-cols-[22rem_minmax(0,1fr)]' => $avatar])>
        <form wire:submit="save" class="flex flex-col gap-6">
            <flux:card class="flex flex-col gap-5">
                <flux:heading size="lg">Details</flux:heading>
                <flux:input wire:model="avatar_name" label="Name" description="You can leave out Resident." />
                <flux:input wire:model="avatar_uuid" label="SL UUID" description="From their Second Life profile." class="font-mono" />
                @if ($avatar)
                    @can('owner')
                        <flux:input wire:model="credits" type="number" min="0" max="999999" label="Credits (L$)" description="Credit held on their account (from coupons)." />
                    @else
                        <flux:input :value="'L$ '.number_format($avatar->credits)" label="Credits" disabled />
                    @endcan
                @endif
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">{{ $avatar ? 'Save' : 'Add avatar' }}</flux:button>
                </div>
            </flux:card>

            @if ($avatar)
                <flux:card class="flex flex-col gap-3">
                    <flux:heading size="lg">Remove avatar</flux:heading>
                    @if ($inUse)
                        <flux:text class="text-sm">This avatar cannot be removed. {{ $inUse }}</flux:text>
                    @else
                        <flux:text class="text-sm">Their payments and audit log entries move to the system owner. Their ban, objects and unsent mail are removed.</flux:text>
                        <div>
                            <flux:button variant="danger" icon="trash" wire:click="delete" wire:confirm="Remove {{ $avatar->avatar_name }}?">Remove</flux:button>
                        </div>
                    @endif
                </flux:card>
            @endif
        </form>

        @if ($avatar)
            <div class="flex flex-col gap-6">
                <flux:card class="p-0">
                    <div class="px-6 pt-5">
                        <flux:heading size="lg">Rentals</flux:heading>
                    </div>
                    <div class="px-6 py-2">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Rental</flux:table.column>
                                <flux:table.column>Stream</flux:table.column>
                                <flux:table.column>Expires</flux:table.column>
                                <flux:table.column></flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @forelse ($rentals as $rental)
                                    <flux:table.row :key="'rental-'.$rental->id">
                                        <flux:table.cell>
                                            <a href="{{ route('clients.show', $rental) }}" wire:navigate class="font-medium hover:underline">{{ $rental->package->name ?? 'Rental' }}</a>
                                            <div class="font-mono text-xs text-zinc-500">{{ $rental->rental_uid }}</div>
                                        </flux:table.cell>
                                        <flux:table.cell class="text-sm">{{ $rental->stream?->server?->domain }}<span class="text-zinc-400">:{{ $rental->stream?->port }}</span></flux:table.cell>
                                        <flux:table.cell class="text-sm">{{ $rental->expire_unixtime->diffForHumans() }}</flux:table.cell>
                                        <flux:table.cell align="end"><x-ui.rental-status :rental="$rental" /></flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="4" class="py-6 text-center text-zinc-500">No rentals.</flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </flux:card>

                <flux:card class="p-0">
                    <div class="flex items-center justify-between px-6 pt-5">
                        <flux:heading size="lg">Recent payments</flux:heading>
                        <flux:button size="sm" variant="ghost" icon-trailing="arrow-right" :href="route('transactions.index', ['avatar' => $avatar->avatar_name])" wire:navigate>All</flux:button>
                    </div>
                    <div class="px-6 py-2">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>When</flux:table.column>
                                <flux:table.column>Type</flux:table.column>
                                <flux:table.column align="end">Amount</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @forelse ($transactions as $transaction)
                                    <flux:table.row :key="'tx-'.$transaction->id">
                                        <flux:table.cell class="text-sm" title="{{ $transaction->unixtime->toDayDateTimeString() }}">{{ $transaction->unixtime->diffForHumans() }}</flux:table.cell>
                                        <flux:table.cell><x-ui.transaction-kind :transaction="$transaction" /></flux:table.cell>
                                        <flux:table.cell align="end" class="tabular-nums">L$ {{ number_format($transaction->amount) }}</flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="3" class="py-6 text-center text-zinc-500">No payments.</flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </flux:card>
            </div>
        @endif
    </div>
</div>
