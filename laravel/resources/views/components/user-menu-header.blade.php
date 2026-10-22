<flux:menu.radio.group>
    <div class="p-0 text-sm font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                <span class="flex h-full w-full items-center justify-center rounded-lg bg-accent text-accent-foreground">
                    {{ auth()->user()->initials() }}
                </span>
            </span>

            <div class="grid flex-1 text-left text-sm leading-tight">
                <span class="truncate font-semibold">{{ auth()->user()->username }}</span>
                <span class="truncate text-xs text-zinc-500">{{ auth()->user()->owner_level ? 'Owner' : 'Staff' }}</span>
            </div>
        </div>
    </div>
</flux:menu.radio.group>
