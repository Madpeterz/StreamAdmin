<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <div>
        <flux:heading size="xl" level="1">Bans</flux:heading>
        <flux:text class="mt-1">Banned avatars cannot rent or renew, and are left out of messages.</flux:text>
    </div>

    <flux:card class="flex flex-col gap-3">
        <flux:heading size="lg">Ban an avatar</flux:heading>
        <form wire:submit="ban" class="flex flex-col gap-3 sm:flex-row sm:items-start">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="find" icon="magnifying-glass" placeholder="Name, UID or SL UUID" autocomplete="off" />
                <flux:error name="find" />
            </div>
            <flux:button type="submit" variant="danger" icon="no-symbol">Ban</flux:button>
        </form>
        @if ($suggestions->isNotEmpty())
            <div class="divide-y divide-zinc-100 rounded-lg border border-zinc-200 dark:divide-zinc-800 dark:border-zinc-700">
                @foreach ($suggestions as $suggestion)
                    <div class="flex items-center justify-between gap-3 px-3 py-2" wire:key="suggest-{{ $suggestion->id }}">
                        <div class="flex items-center gap-2">
                            <flux:avatar size="xs" :name="$suggestion->avatar_name" />
                            <span class="text-sm font-medium">{{ $suggestion->avatar_name }}</span>
                            <span class="font-mono text-xs text-zinc-500">{{ $suggestion->avatar_uid }}</span>
                        </div>
                        <flux:button size="xs" variant="ghost" wire:click="ban({{ $suggestion->id }})">Ban</flux:button>
                    </div>
                @endforeach
            </div>
        @endif
    </flux:card>

    <flux:card class="p-0">
        <div class="flex flex-wrap items-end justify-between gap-4 px-6 pt-5">
            <flux:heading size="lg">Banned</flux:heading>
            <div class="w-full sm:w-64">
                <flux:input size="sm" wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search banned avatars" />
            </div>
        </div>
        <div class="px-6 py-2">
            <flux:table :paginate="$bans">
                <flux:table.columns>
                    <flux:table.column>Avatar</flux:table.column>
                    <flux:table.column>SL UUID</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($bans as $ban)
                        <flux:table.row :key="'ban-'.$ban->id">
                            <flux:table.cell>
                                @if ($ban->avatar)
                                    <a href="{{ route('avatars.show', $ban->avatar) }}" wire:navigate class="font-medium hover:underline">{{ $ban->avatar->avatar_name }}</a>
                                    <div class="font-mono text-xs text-zinc-500">{{ $ban->avatar->avatar_uid }}</div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="font-mono text-xs text-zinc-500">{{ $ban->avatar?->avatar_uuid }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button size="sm" variant="ghost" icon="check-circle" wire:click="unban({{ $ban->id }})">Lift ban</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="3" class="py-8 text-center text-zinc-500">No one is banned{{ $search !== '' ? ' with that name' : '' }}.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>
</div>
