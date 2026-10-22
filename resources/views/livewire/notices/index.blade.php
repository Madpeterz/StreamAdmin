<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Notices</flux:heading>
            <flux:text class="mt-1">Reminders sent to clients as their rental runs down. Each client moves down the list as time passes.</flux:text>
        </div>
        <flux:button :href="route('notices.create')" variant="primary" icon="plus" wire:navigate>New notice</flux:button>
    </div>

    <flux:card class="p-0">
        <ol class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @foreach ($notices as $notice)
                @php($locked = $notice->id === \App\Services\NoticeLevels::ACTIVE)
                <li wire:key="notice-{{ $notice->id }}" class="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-6">
                    <div class="shrink-0 sm:w-44">
                        <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ \App\Livewire\Notices\Index::when($notice) }}</div>
                        @unless (in_array($notice->id, [\App\Services\NoticeLevels::ACTIVE, \App\Services\NoticeLevels::EXPIRED], true))
                            <div class="text-xs text-zinc-500 tabular-nums">{{ $notice->hours_remaining }} hours left</div>
                        @endunless
                    </div>
                    <div class="min-w-0 sm:flex-1">
                        @if ($locked)
                            <div class="flex items-center gap-2 font-medium text-zinc-900 dark:text-white">
                                {{ $notice->name }}
                                <flux:icon.lock-closed variant="micro" class="text-zinc-400" />
                            </div>
                            <div class="text-sm text-zinc-500">Clients with time to spare sit here; nothing is sent.</div>
                        @else
                            <a href="{{ route('notices.edit', $notice) }}" wire:navigate class="font-medium text-zinc-900 hover:underline dark:text-white">{{ $notice->name }}</a>
                            <div class="truncate text-sm text-zinc-500">{{ $notice->im_message }}</div>
                        @endif
                    </div>
                    @unless ($locked)
                        <div class="flex flex-wrap gap-1.5">
                            @if ($notice->send_object_im)
                                <flux:badge size="sm" color="sky" icon="cube">Object IM</flux:badge>
                            @endif
                            @if ($notice->use_bot)
                                <flux:badge size="sm" color="violet" icon="cpu-chip">Bot IM</flux:badge>
                            @endif
                            @if ($notice->send_notecard)
                                <flux:badge size="sm" color="amber" icon="document-text">Notecard</flux:badge>
                            @endif
                            @if ($notice->notice_notecard_id !== 1)
                                <flux:badge size="sm" color="zinc" icon="paper-clip">{{ $notice->noticeNotecard->name ?? 'Static notecard' }}</flux:badge>
                            @endif
                        </div>
                    @endunless
                    <a href="{{ route('clients.index', ['notice' => $notice->id]) }}" wire:navigate class="text-sm text-zinc-500 tabular-nums hover:underline sm:w-24 sm:text-right">
                        {{ $notice->rentals_count }} {{ str('client')->plural($notice->rentals_count) }}
                    </a>
                </li>
            @endforeach
        </ol>
    </flux:card>
</div>
