@props(['label' => 'Avatar', 'selected' => null, 'matches', 'error' => null])

{{-- search by name, UID or UUID, then pick; needs the SearchesAvatars trait on the Livewire component --}}
<div class="flex flex-col gap-2">
    @if ($selected)
        <flux:field>
            <flux:label>{{ $label }}</flux:label>
            <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                <div class="flex items-center gap-2 text-sm">
                    <flux:avatar size="xs" :name="$selected->avatar_name" />
                    <span class="font-medium">{{ $selected->avatar_name }}</span>
                    <span class="font-mono text-xs text-zinc-500">{{ $selected->avatar_uid }}</span>
                </div>
                <flux:button size="xs" variant="ghost" wire:click="clearAvatar">Change</flux:button>
            </div>
        </flux:field>
    @else
        <flux:field>
            <flux:label>{{ $label }}</flux:label>
            <flux:input wire:model.live.debounce.300ms="avatarSearch" icon="magnifying-glass" placeholder="Name, avatar UID or SL UUID (3+ letters)" autocomplete="off" />
        </flux:field>
        @if ($matches->isNotEmpty())
            <div class="divide-y divide-zinc-100 rounded-lg border border-zinc-200 dark:divide-zinc-800 dark:border-zinc-700">
                @foreach ($matches as $match)
                    <button type="button" wire:click="pickAvatar('{{ $match->avatar_uid }}')" wire:key="match-{{ $match->id }}"
                        class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <span class="font-medium">{{ $match->avatar_name }}</span>
                        <span class="font-mono text-xs text-zinc-500">{{ $match->avatar_uid }}</span>
                    </button>
                @endforeach
            </div>
        @elseif (mb_strlen(trim($this->avatarSearch)) >= 3)
            <flux:text class="text-sm">No avatar matches that.</flux:text>
        @endif
    @endif
    @if ($error)
        <flux:error :message="$error" />
    @endif
</div>
