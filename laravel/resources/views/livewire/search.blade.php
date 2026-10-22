<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <div>
        <flux:heading size="xl" level="1">Search</flux:heading>
        @if ($term !== '' && ! $tooShort)
            <flux:text class="mt-1">Results for <strong>{{ $term }}</strong></flux:text>
        @endif
    </div>

    <form action="{{ route('search') }}" method="get" class="max-w-xl">
        <flux:input name="q" :value="$term" icon="magnifying-glass" placeholder="Avatar name, UID or UUID, rental UID, port, stream admin user…" autofocus />
    </form>

    @if ($term === '')
        <flux:text>Search for an avatar, a client, a stream port or a note.</flux:text>
    @elseif ($tooShort)
        <flux:text>Search needs 3 or more letters.</flux:text>
    @elseif ($avatars->isEmpty() && $rentals->isEmpty() && $streams->isEmpty())
        <flux:card class="flex flex-col items-center gap-2 py-10 text-center">
            <flux:icon.magnifying-glass class="size-8 text-zinc-400" />
            <flux:heading>Nothing found</flux:heading>
            <flux:text>Nothing matches “{{ $term }}”.</flux:text>
        </flux:card>
    @else
        @if ($rentals->isNotEmpty())
            <flux:card class="p-0">
                <div class="flex items-center gap-2 px-6 pt-5">
                    <flux:heading size="lg">Clients</flux:heading>
                    <flux:badge size="sm">{{ $rentals->count() }}</flux:badge>
                </div>
                <div class="px-6 py-2">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Client</flux:table.column>
                            <flux:table.column>Stream</flux:table.column>
                            <flux:table.column>Expires</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($rentals as $rental)
                                <flux:table.row :key="'rental-'.$rental->id">
                                    <flux:table.cell><a href="{{ route('clients.show', $rental) }}" wire:navigate class="font-medium hover:underline">{{ $rental->avatar->avatar_name ?? 'Unknown avatar' }}</a></flux:table.cell>
                                    <flux:table.cell class="text-sm">{{ $rental->stream?->server?->domain }}<span class="text-zinc-400">:{{ $rental->stream?->port }}</span></flux:table.cell>
                                    <flux:table.cell class="text-sm text-zinc-600 dark:text-zinc-300">{{ $rental->expire_unixtime->diffForHumans() }}</flux:table.cell>
                                    <flux:table.cell><x-ui.rental-status :rental="$rental" /></flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        @endif

        @if ($streams->isNotEmpty())
            <flux:card class="p-0">
                <div class="flex items-center gap-2 px-6 pt-5">
                    <flux:heading size="lg">Streams</flux:heading>
                    <flux:badge size="sm">{{ $streams->count() }}</flux:badge>
                </div>
                <div class="px-6 py-2">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Stream</flux:table.column>
                            <flux:table.column>Admin user</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($streams as $stream)
                                <flux:table.row :key="'stream-'.$stream->id">
                                    <flux:table.cell>
                                        <a href="{{ route('streams.edit', $stream) }}" wire:navigate class="font-medium hover:underline">{{ $stream->server?->domain }}:{{ $stream->port }}</a>
                                        <div class="font-mono text-xs text-zinc-500">{{ $stream->stream_uid }}</div>
                                    </flux:table.cell>
                                    <flux:table.cell class="font-mono text-xs">{{ $stream->admin_username }}</flux:table.cell>
                                    <flux:table.cell>
                                        @if ($stream->rental)
                                            <a href="{{ route('clients.show', $stream->rental) }}" wire:navigate class="text-sm hover:underline">Sold to {{ $stream->rental->avatar->avatar_name ?? 'unknown' }}</a>
                                        @elseif ($stream->need_work)
                                            <flux:badge size="sm" color="amber">Needs work</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="emerald">Ready</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        @endif

        @if ($avatars->isNotEmpty())
            <flux:card class="p-0">
                <div class="flex items-center gap-2 px-6 pt-5">
                    <flux:heading size="lg">Avatars</flux:heading>
                    <flux:badge size="sm">{{ $avatars->count() }}</flux:badge>
                </div>
                <div class="px-6 py-2">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Name</flux:table.column>
                            <flux:table.column>UID</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($avatars as $avatar)
                                <flux:table.row :key="'avatar-'.$avatar->id">
                                    <flux:table.cell><a href="{{ route('avatars.show', $avatar) }}" wire:navigate class="font-medium hover:underline">{{ $avatar->avatar_name }}</a></flux:table.cell>
                                    <flux:table.cell class="font-mono text-xs text-zinc-500">{{ $avatar->avatar_uid }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        @endif
    @endif
</div>
