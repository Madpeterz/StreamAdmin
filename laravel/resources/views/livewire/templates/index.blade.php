<div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Templates</flux:heading>
            <flux:text class="mt-1">The stream details each package sends a new client, as an IM and as a notecard.</flux:text>
        </div>
        <flux:button :href="route('templates.create')" variant="primary" icon="plus" wire:navigate>New template</flux:button>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($templates as $template)
            <a href="{{ route('templates.edit', $template) }}" wire:navigate wire:key="template-{{ $template->id }}" class="group">
                <flux:card class="flex h-full flex-col gap-3 transition group-hover:border-zinc-300 dark:group-hover:border-zinc-600">
                    <div class="flex items-start justify-between gap-3">
                        <flux:heading size="lg">{{ $template->name }}</flux:heading>
                        <flux:badge size="sm" :color="$template->packages_count > 0 ? 'emerald' : 'zinc'">
                            {{ $template->packages_count }} {{ str('package')->plural($template->packages_count) }}
                        </flux:badge>
                    </div>
                    <p class="line-clamp-3 font-mono text-xs text-zinc-500 dark:text-zinc-400">{{ $template->detail }}</p>
                </flux:card>
            </a>
        @empty
            <flux:card class="py-10 text-center text-zinc-500 md:col-span-2 xl:col-span-3">
                No templates yet. Packages need one to send stream details.
            </flux:card>
        @endforelse
    </div>
</div>
