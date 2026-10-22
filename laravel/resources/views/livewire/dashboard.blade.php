<div class="mx-auto flex w-full max-w-7xl flex-col gap-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Dashboard</flux:heading>
            <flux:text class="mt-1">Welcome back, {{ auth()->user()->username }}. Here is how your streams are doing.</flux:text>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @php
                $weeksLeft = $this->supportKey['expires'] ? ($this->supportKey['expires'] - time()) / 604800 : 0;
                $keyColor = ! $this->supportKey['ok'] ? 'red' : ($weeksLeft < 4 ? 'red' : ($weeksLeft < 8 ? 'amber' : 'zinc'));
                $keyText = $this->supportKey['ok'] ? 'Support key expires '.\Carbon\CarbonImmutable::createFromTimestamp($this->supportKey['expires'])->diffForHumans() : 'Support key: '.$this->supportKey['status'];
            @endphp
            @can('owner')
                <flux:badge as="a" :href="route('system-settings.index')" wire:navigate size="sm" :color="$keyColor" icon="key">{{ $keyText }}</flux:badge>
            @else
                <flux:badge size="sm" :color="$keyColor" icon="key">{{ $keyText }}</flux:badge>
            @endcan
            @if ($this->config?->db_version)
                <flux:badge size="sm" icon="information-circle">Database {{ $this->config->db_version }}</flux:badge>
            @endif
        </div>
    </div>

    @island(name: 'pills')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" wire:poll.60s>
        <x-ui.stat-card label="Active clients" :value="number_format($this->clients['active'])" icon="users" tone="emerald"
            :href="route('clients.index', ['status' => 'active'])" />
        <x-ui.stat-card label="Expiring in 24 hours" :value="number_format($this->clients['expiring'])" icon="clock" tone="amber"
            :href="route('clients.index', ['status' => 'expiring'])" />
        <x-ui.stat-card label="Expired" :value="number_format($this->clients['expired'])" icon="exclamation-triangle" tone="red"
            :href="route('clients.index', ['status' => 'expired'])" />
        <x-ui.stat-card label="Revenue, last 30 days" :value="'L$ '.number_format($this->revenue)" icon="banknotes" tone="sky"
            :href="route('transactions.index')" />
    </div>
    @endisland

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            @island(name: 'expiring')
            <div class="contents" wire:poll.300s>
            <flux:card class="p-0">
                <div class="flex items-center justify-between px-6 pt-5 pb-3">
                    <div>
                        <flux:heading size="lg">Next to expire</flux:heading>
                        <flux:text class="text-sm">Rentals closest to running out</flux:text>
                    </div>
                    <flux:button size="sm" variant="ghost" icon-trailing="arrow-right" :href="route('clients.index')" wire:navigate>All clients</flux:button>
                </div>
                <div class="px-6 pb-2">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Client</flux:table.column>
                            <flux:table.column>Stream</flux:table.column>
                            <flux:table.column>Expires</flux:table.column>
                            <flux:table.column></flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($this->expiringRentals as $rental)
                                <flux:table.row :key="$rental->id">
                                    <flux:table.cell>
                                        <a href="{{ route('clients.show', $rental) }}" wire:navigate class="font-medium text-zinc-900 hover:underline dark:text-white">
                                            {{ $rental->avatar->avatar_name ?? 'Unknown avatar' }}
                                        </a>
                                        <div class="text-xs text-zinc-500">{{ $rental->package->name ?? '' }}</div>
                                    </flux:table.cell>
                                    <flux:table.cell class="tabular-nums">
                                        {{ $rental->stream?->server?->domain }}<span class="text-zinc-400">:{{ $rental->stream?->port }}</span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span title="{{ $rental->expire_unixtime->toDayDateTimeString() }}">{{ $rental->expire_unixtime->diffForHumans() }}</span>
                                    </flux:table.cell>
                                    <flux:table.cell align="end"><x-ui.rental-status :rental="$rental" /></flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="py-8 text-center text-zinc-500">No active rentals.</flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
            </div>
            @endisland

            @island(name: 'transactions')
            <div class="contents" wire:poll.30s>
            <flux:card class="p-0">
                <div class="flex items-center justify-between px-6 pt-5 pb-3">
                    <div>
                        <flux:heading size="lg">Recent transactions</flux:heading>
                        <flux:text class="text-sm">Purchases, renewals and credit top-ups</flux:text>
                    </div>
                    <flux:button size="sm" variant="ghost" icon-trailing="arrow-right" :href="route('transactions.index')" wire:navigate>All transactions</flux:button>
                </div>
                <div class="px-6 pb-2">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Avatar</flux:table.column>
                            <flux:table.column>Type</flux:table.column>
                            <flux:table.column>When</flux:table.column>
                            <flux:table.column align="end">Amount</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($this->recentTransactions as $transaction)
                                <flux:table.row :key="$transaction->id">
                                    <flux:table.cell class="font-medium text-zinc-900 dark:text-white">{{ $transaction->avatar->avatar_name ?? 'Unknown avatar' }}</flux:table.cell>
                                    <flux:table.cell>
                                        @if ($transaction->via_marketplace)
                                            <flux:badge size="sm" color="violet" inset="top bottom">Marketplace</flux:badge>
                                        @elseif ($transaction->renew)
                                            <flux:badge size="sm" color="sky" inset="top bottom">Renewal</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="emerald" inset="top bottom">New rental</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $transaction->unixtime?->diffForHumans() }}</flux:table.cell>
                                    <flux:table.cell align="end" class="tabular-nums font-medium">L$ {{ number_format($transaction->amount) }}</flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="py-8 text-center text-zinc-500">No transactions yet.</flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
            </div>
            @endisland
        </div>

        <div class="flex flex-col gap-6">
            @island(name: 'capacity')
            <div class="contents" wire:poll.60s>
            <flux:card>
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Streams</flux:heading>
                    <flux:button size="sm" variant="ghost" icon-trailing="arrow-right" :href="route('streams.index')" wire:navigate>Manage</flux:button>
                </div>
                @php $totalStreams = max(1, array_sum($this->streams)); @endphp
                <div class="mt-4 flex h-2.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <div class="bg-sky-500" style="width: {{ $this->streams['sold'] / $totalStreams * 100 }}%"></div>
                    <div class="bg-emerald-500" style="width: {{ $this->streams['ready'] / $totalStreams * 100 }}%"></div>
                    <div class="bg-amber-500" style="width: {{ $this->streams['need_work'] / $totalStreams * 100 }}%"></div>
                </div>
                <dl class="mt-4 grid grid-cols-3 gap-2 text-center">
                    @foreach (['sold' => ['Sold', 'bg-sky-500'], 'ready' => ['Ready', 'bg-emerald-500'], 'need_work' => ['Needs work', 'bg-amber-500']] as $key => [$label, $dot])
                        <div class="rounded-lg bg-zinc-50 py-2 dark:bg-zinc-800/60">
                            <dt class="flex items-center justify-center gap-1.5 text-xs text-zinc-500"><span class="size-2 rounded-full {{ $dot }}"></span>{{ $label }}</dt>
                            <dd class="mt-0.5 text-lg font-semibold tabular-nums">{{ $this->streams[$key] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </flux:card>

            <flux:card>
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Servers</flux:heading>
                    <flux:button size="sm" variant="ghost" icon-trailing="arrow-right" :href="route('servers.index')" wire:navigate>Manage</flux:button>
                </div>
                <div class="mt-3 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->servers as ['server' => $server, 'counts' => $counts])
                        <div class="flex items-center justify-between gap-3 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon.server class="size-4 text-zinc-500" />
                                </div>
                                <span class="truncate text-sm font-medium">{{ $server->domain }}</span>
                            </div>
                            <div class="flex shrink-0 gap-1">
                                <flux:badge size="sm" color="sky" title="Sold">{{ $counts['sold'] }}</flux:badge>
                                <flux:badge size="sm" color="emerald" title="Ready">{{ $counts['ready'] }}</flux:badge>
                                <flux:badge size="sm" color="amber" title="Needs work">{{ $counts['need_work'] }}</flux:badge>
                            </div>
                        </div>
                    @empty
                        <flux:text class="py-3 text-sm">No servers yet.</flux:text>
                    @endforelse
                </div>
            </flux:card>
            </div>
            @endisland

            @island(name: 'health')
            <div class="contents" wire:poll.10s>
            <flux:card>
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">System health</flux:heading>
                    <flux:button size="sm" variant="ghost" icon-trailing="arrow-right" :href="route('health.index')" wire:navigate>Details</flux:button>
                </div>
                <div class="mt-3 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($this->systemObjects as $object)
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <div>
                                <div class="text-sm font-medium whitespace-nowrap">{{ $object['name'] }}</div>
                                @if ($object['region'])
                                    <div class="text-xs text-zinc-500">{{ $object['region'] }}</div>
                                @endif
                            </div>
                            <x-ui.last-seen :at="$object['last_seen']" />
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between gap-3 py-2.5">
                        <div class="text-sm font-medium">Reseller vendors</div>
                        @if ($this->vendorHealth === null)
                            <flux:text class="text-sm">No vendors</flux:text>
                        @else
                            <flux:badge size="sm" :color="\App\Services\ObjectHealth::percentColor($this->vendorHealth)">{{ $this->vendorHealth }}% online</flux:badge>
                        @endif
                    </div>
                </div>
            </flux:card>
            </div>
            @endisland
        </div>
    </div>
</div>
