@php
    $avatar = $rental->avatar;
    $stream = $rental->stream;
@endphp

<div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('clients.index')" wire:navigate>Clients</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $rental->rental_uid }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            <flux:avatar size="lg" :name="$avatar->avatar_name ?? '?'" />
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <flux:heading size="xl" level="1">{{ $avatar->avatar_name ?? 'Unknown avatar' }}</flux:heading>
                    <x-ui.rental-status :rental="$rental" />
                    @if ($avatar?->ban)
                        <flux:badge color="red" size="sm" icon="no-symbol">Banned</flux:badge>
                    @endif
                </div>
                <flux:text class="mt-1">
                    {{ $rental->package->name ?? 'No package' }} ·
                    <span class="font-mono">{{ $rental->rental_uid }}</span>
                </flux:text>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button icon="chat-bubble-left-ellipsis" :href="route('outbox.compose', ['source' => 'rental', 'id' => $rental->id])" wire:navigate>Message</flux:button>
            @if ($detailsText !== null)
                <flux:modal.trigger name="details">
                    <flux:button icon="document-text">Details</flux:button>
                </flux:modal.trigger>
            @endif
            <flux:modal.trigger name="adjust-time">
                <flux:button icon="clock">Adjust time</flux:button>
            </flux:modal.trigger>
            <flux:modal.trigger name="transfer">
                <flux:button icon="arrows-right-left">Transfer</flux:button>
            </flux:modal.trigger>
            <flux:modal.trigger name="revoke">
                <flux:button variant="danger" icon="x-circle">Revoke</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            <flux:card>
                <flux:heading size="lg">Rental</flux:heading>
                <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm text-zinc-500">Expires</dt>
                        <dd class="mt-1 font-medium">{{ $rental->expire_unixtime->diffForHumans() }}</dd>
                        <dd class="text-xs text-zinc-500">{{ $rental->expire_unixtime->toDayDateTimeString() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500">Started</dt>
                        <dd class="mt-1 font-medium">{{ $rental->start_unixtime?->toFormattedDateString() ?? 'Unknown' }}</dd>
                        <dd class="text-xs text-zinc-500">{{ $rental->start_unixtime?->diffForHumans() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500">Notice level</dt>
                        <dd class="mt-1 font-medium">{{ $rental->notice->name ?? 'None' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500">Renewals</dt>
                        <dd class="mt-1 font-medium tabular-nums">{{ $rental->renewals }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500">Total paid</dt>
                        <dd class="mt-1 font-medium tabular-nums">L$ {{ number_format($rental->total_amount) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500">Package</dt>
                        <dd class="mt-1 font-medium">{{ $rental->package->name ?? '—' }}</dd>
                        @if ($rental->package)
                            <dd class="text-xs text-zinc-500">L$ {{ number_format($rental->package->cost) }} / {{ $rental->package->days }} days · {{ $rental->package->listeners }} listeners · {{ $rental->package->bitrate }} kbps</dd>
                        @endif
                    </div>
                </dl>

                <flux:separator class="my-6" />

                <form wire:submit="saveNote" class="flex flex-col gap-3">
                    <flux:textarea wire:model="note" label="Notes" rows="4" placeholder="Staff notes, time adjustments and transfers are recorded here" />
                    <div class="flex justify-end">
                        <flux:button type="submit" size="sm" variant="primary">Save notes</flux:button>
                    </div>
                </form>
            </flux:card>

            @if ($otherRentals->isNotEmpty())
            <flux:card class="p-0">
                <div class="px-6 pt-5 pb-3">
                    <flux:heading size="lg">Other rentals</flux:heading>
                    <flux:text class="text-sm">Everything else this avatar rents</flux:text>
                </div>
                <div class="px-6 pb-2">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Rental</flux:table.column>
                            <flux:table.column>Stream</flux:table.column>
                            <flux:table.column>Expires</flux:table.column>
                            <flux:table.column></flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($otherRentals as $other)
                                <flux:table.row :key="$other->id">
                                    <flux:table.cell>
                                        <a href="{{ route('clients.show', $other) }}" wire:navigate class="font-mono text-sm hover:underline">{{ $other->rental_uid }}</a>
                                        <div class="text-xs text-zinc-500">{{ $other->package->name ?? '' }}</div>
                                    </flux:table.cell>
                                    <flux:table.cell class="tabular-nums">{{ $other->stream?->server?->domain }}<span class="text-zinc-400">:{{ $other->stream?->port }}</span></flux:table.cell>
                                    <flux:table.cell>{{ $other->expire_unixtime->diffForHumans() }}</flux:table.cell>
                                    <flux:table.cell align="end"><x-ui.rental-status :rental="$other" /></flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
            @endif

            <flux:card class="p-0">
                <div class="px-6 pt-5 pb-3">
                    <flux:heading size="lg">Payments</flux:heading>
                    <flux:text class="text-sm">Latest transactions by this avatar</flux:text>
                </div>
                <div class="px-6 pb-2">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>When</flux:table.column>
                            <flux:table.column>Package</flux:table.column>
                            <flux:table.column>Type</flux:table.column>
                            <flux:table.column align="end">Amount</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($transactions as $transaction)
                                <flux:table.row :key="$transaction->id">
                                    <flux:table.cell>{{ $transaction->unixtime?->toFormattedDateString() }}</flux:table.cell>
                                    <flux:table.cell>{{ $transaction->package->name ?? '—' }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" inset="top bottom" :color="$transaction->renew ? 'sky' : 'emerald'">{{ $transaction->renew ? 'Renewal' : 'New rental' }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell align="end" class="tabular-nums">L$ {{ number_format($transaction->amount) }}</flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="py-6 text-center text-zinc-500">No payments recorded.</flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        </div>

        <div class="flex flex-col gap-6">
            <flux:card>
                <flux:heading size="lg">Stream</flux:heading>
                @if ($stream)
                    <dl class="mt-4 flex flex-col gap-4">
                        <div>
                            <dt class="text-sm text-zinc-500">Server</dt>
                            <dd class="mt-1 font-medium">{{ $stream->server->domain ?? '—' }}<span class="text-zinc-400">:{{ $stream->port }}</span></dd>
                            <dd class="text-xs text-zinc-500">{{ $rental->package->serverType->name ?? '' }} · mount {{ $stream->mountpoint }}</dd>
                        </div>
                        @if ($stream->server?->control_panel_url)
                            <div>
                                <dt class="text-sm text-zinc-500">Control panel</dt>
                                <dd class="mt-1"><flux:link :href="$stream->server->control_panel_url" external>{{ $stream->server->control_panel_url }}</flux:link></dd>
                            </div>
                        @endif
                        <x-ui.secret label="Admin login" :username="$stream->admin_username" :secret="$stream->admin_password" />
                        <x-ui.secret label="DJ password" :secret="$stream->dj_password" />
                    </dl>
                @else
                    <flux:text class="mt-3">This rental has no stream attached.</flux:text>
                @endif
            </flux:card>

            <flux:card>
                <flux:heading size="lg">Client</flux:heading>
                @if ($avatar)
                    <dl class="mt-4 flex flex-col gap-4">
                        <div>
                            <dt class="text-sm text-zinc-500">Avatar</dt>
                            <dd class="mt-1">
                                <a href="{{ route('search', ['q' => $avatar->avatar_name]) }}" wire:navigate class="text-sm font-medium hover:underline">{{ $avatar->avatar_name }}</a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-zinc-500">Credits</dt>
                            <dd class="mt-1 font-medium tabular-nums">L$ {{ number_format($avatar->credits) }}</dd>
                        </div>
                        <div>
                            <dt class="flex items-center justify-between text-sm text-zinc-500">
                                Notice opt-outs
                                <flux:modal.trigger name="notice-opt-outs">
                                    <flux:button size="xs" variant="ghost" icon="pencil-square">Change</flux:button>
                                </flux:modal.trigger>
                            </dt>
                            <dd class="mt-1 flex flex-wrap gap-1">
                                @forelse ($rental->noticeOptOuts as $optOut)
                                    <flux:badge size="sm" icon="bell-slash">{{ $optOut->notice->name ?? 'Notice '.$optOut->notice_id }}</flux:badge>
                                @empty
                                    <span class="text-sm text-zinc-500">Receives all notices</span>
                                @endforelse
                            </dd>
                        </div>
                    </dl>
                @endif
            </flux:card>
        </div>
    </div>

    <flux:modal name="notice-opt-outs" class="md:w-md">
        <form wire:submit="saveOptOuts" class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Notice opt-outs</flux:heading>
                <flux:text class="mt-2">Tick the notices this rental should skip. Other rentals of this client are not affected.</flux:text>
            </div>
            <flux:checkbox.group wire:model="optOuts" class="flex flex-col gap-3">
                @foreach ($noticeLevels as $level)
                    <flux:checkbox wire:key="optout-{{ $level->id }}" :value="(string) $level->id"
                        :label="'Skip '.$level->name"
                        :description="\App\Livewire\Notices\Index::when($level)" />
                @endforeach
            </flux:checkbox.group>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Save</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="adjust-time" class="md:w-md">
        <form wire:submit="adjustTime" class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Adjust time left</flux:heading>
                <flux:text class="mt-1">Currently expires {{ $rental->expire_unixtime->diffForHumans() }}. The change is noted on the rental.</flux:text>
            </div>
            <flux:radio.group wire:model="adjustDirection" variant="segmented">
                <flux:radio value="add" label="Add time" icon="plus" />
                <flux:radio value="remove" label="Remove time" icon="minus" />
            </flux:radio.group>
            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="adjustDays" type="number" min="0" label="Days" />
                <flux:input wire:model="adjustHours" type="number" min="0" max="23" label="Hours" />
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Apply</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="transfer" class="md:w-md">
        <form wire:submit="transfer" class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Transfer rental</flux:heading>
                <flux:text class="mt-1">Move this rental to another avatar. Search by name, avatar UID or SL UUID.</flux:text>
            </div>
            <x-ui.avatar-picker label="New owner" :selected="$selectedAvatar" :matches="$matches" :error="$errors->first('transferUid')" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Transfer</flux:button>
            </div>
        </form>
    </flux:modal>

    @if ($detailsText !== null)
        <flux:modal name="details" class="md:w-xl">
            <div class="flex flex-col gap-4">
                <div>
                    <flux:heading size="lg">Stream details</flux:heading>
                    <flux:text class="mt-1">What this client gets for {{ $stream->server->domain ?? 'their stream' }}:{{ $stream->port ?? '' }}, from the package's details template. Passwords are shown here.</flux:text>
                </div>
                <flux:textarea readonly rows="8" class="font-mono text-xs">{{ $detailsText }}</flux:textarea>
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">Close</flux:button></flux:modal.close>
                    <flux:button variant="primary" icon="paper-airplane" wire:click="resendDetails">Send to client again</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    <flux:modal name="revoke" class="md:w-md">
        <div class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Revoke this rental?</flux:heading>
                <flux:text class="mt-2">
                    The rental ends now with <strong>no refund</strong> (time left is worth about L$ {{ number_format($refundEstimate) }}).
                    The stream is released and marked as needing work.
                </flux:text>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button wire:click="revoke" variant="danger">Revoke rental</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
