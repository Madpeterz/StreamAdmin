<div class="mx-auto flex w-full max-w-7xl flex-col gap-6" wire:poll.5s>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Outbox</flux:heading>
            <flux:text class="mt-1">Everything waiting to be delivered in Second Life by your in-world objects and bot.</flux:text>
        </div>
        <flux:button :href="route('outbox.compose')" variant="primary" icon="paper-airplane" wire:navigate>Send a message</flux:button>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        @foreach (\App\Livewire\Outbox\Index::QUEUES as $key => $queue)
            @php($count = $counts[$key])
            <button
                type="button"
                wire:click="$set('queue', '{{ $key }}')"
                wire:key="queue-{{ $key }}"
                @class([
                    'flex flex-col gap-1 rounded-xl border p-4 text-left transition',
                    'border-accent bg-white ring-1 ring-accent dark:bg-zinc-900' => $current === $key,
                    'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600' => $current !== $key,
                ])
            >
                <span class="text-sm text-zinc-500">{{ $queue['label'] }}</span>
                <span @class(['text-2xl font-semibold tabular-nums', 'text-zinc-900 dark:text-white' => $count > 0, 'text-zinc-400' => $count === 0])>{{ number_format($count) }}</span>
                <span class="text-xs text-zinc-500">
                    @if ($count > 0)
                        clears in ~{{ \App\Livewire\Outbox\Index::timeToClear($queue['norm'] * $count) }}
                    @else
                        empty
                    @endif
                </span>
            </button>
        @endforeach
    </div>

    <flux:card class="p-0">
        <div class="px-6 pt-5">
            <flux:heading size="lg">{{ \App\Livewire\Outbox\Index::QUEUES[$current]['label'] }}</flux:heading>
            <flux:text class="text-sm">
                {{ \App\Livewire\Outbox\Index::QUEUES[$current]['about'] }}.
                @php($q = \App\Livewire\Outbox\Index::QUEUES[$current])
                @if ($counts[$current] > 0 && $q['cron'] !== $q['norm'])
                    With the scheduler working this queue too it clears in about {{ \App\Livewire\Outbox\Index::timeToClear($q['cron'] * $counts[$current]) }}.
                @endif
            </flux:text>
        </div>
        <div class="px-6 py-2">
            <flux:table :paginate="$items">
                <flux:table.columns>
                    <flux:table.column class="w-16">#</flux:table.column>
                    @switch($current)
                        @case('mail')
                            <flux:table.column>To</flux:table.column>
                            <flux:table.column>Message</flux:table.column>
                            @break
                        @case('notecards')
                            <flux:table.column>To</flux:table.column>
                            <flux:table.column>Rental</flux:table.column>
                            <flux:table.column>Notecard</flux:table.column>
                            @break
                        @case('docs')
                            <flux:table.column>To</flux:table.column>
                            <flux:table.column>Notecard</flux:table.column>
                            @break
                        @case('details')
                            <flux:table.column>To</flux:table.column>
                            <flux:table.column>Rental</flux:table.column>
                            @break
                        @case('events')
                            <flux:table.column>Event</flux:table.column>
                            <flux:table.column>When</flux:table.column>
                            @break
                        @case('bot')
                            <flux:table.column>Command</flux:table.column>
                            <flux:table.column align="end">Arguments</flux:table.column>
                            <flux:table.column>Queued</flux:table.column>
                            @break
                    @endswitch
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($items as $item)
                        <flux:table.row :key="$current.'-'.$item->id">
                            <flux:table.cell class="text-zinc-400 tabular-nums">{{ $item->id }}</flux:table.cell>
                            @switch($current)
                                @case('mail')
                                    <flux:table.cell class="font-medium">{{ $item->avatar->avatar_name ?? 'Unknown avatar' }}</flux:table.cell>
                                    <flux:table.cell class="max-w-xl"><div class="truncate text-zinc-600 dark:text-zinc-300" title="{{ $item->message }}">{{ $item->message }}</div></flux:table.cell>
                                    @break
                                @case('notecards')
                                    <flux:table.cell class="font-medium">{{ $item->rental->avatar->avatar_name ?? 'Unknown avatar' }}</flux:table.cell>
                                    <flux:table.cell>
                                        @if ($item->rental)
                                            <a href="{{ route('clients.show', $item->rental) }}" wire:navigate class="font-mono text-xs hover:underline">{{ $item->rental->rental_uid }}</a>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $item->as_notice ? 'Notice: '.($item->notice->name ?? '?') : 'Stream details' }}</flux:table.cell>
                                    @break
                                @case('docs')
                                    <flux:table.cell class="font-medium">{{ $item->avatar->avatar_name ?? 'Unknown avatar' }}</flux:table.cell>
                                    <flux:table.cell>{{ $item->noticeNotecard->name ?? '?' }}</flux:table.cell>
                                    @break
                                @case('details')
                                    <flux:table.cell class="font-medium">{{ $item->rental->avatar->avatar_name ?? 'Unknown avatar' }}</flux:table.cell>
                                    <flux:table.cell>
                                        @if ($item->rental)
                                            <a href="{{ route('clients.show', $item->rental) }}" wire:navigate class="font-mono text-xs hover:underline">{{ $item->rental->rental_uid }}</a>
                                        @endif
                                    </flux:table.cell>
                                    @break
                                @case('events')
                                    <flux:table.cell class="font-medium">{{ $item->event_name }}</flux:table.cell>
                                    <flux:table.cell title="{{ $item->event_unixtime?->toDayDateTimeString() }}">{{ $item->event_unixtime?->diffForHumans() }}</flux:table.cell>
                                    @break
                                @case('bot')
                                    <flux:table.cell class="font-mono text-sm">{{ $item->command }}</flux:table.cell>
                                    <flux:table.cell align="end" class="tabular-nums">{{ $item->args ? count(json_decode($item->args, true) ?? []) : 0 }}</flux:table.cell>
                                    <flux:table.cell title="{{ $item->unixtime?->toDayDateTimeString() }}">{{ $item->unixtime?->diffForHumans() }}</flux:table.cell>
                                    @break
                            @endswitch
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="py-8 text-center text-zinc-500">Nothing waiting.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>
</div>
