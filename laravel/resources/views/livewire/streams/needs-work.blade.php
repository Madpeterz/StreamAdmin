<div class="mx-auto flex w-full max-w-6xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('streams.index')" wire:navigate>Streams</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Needs work</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div>
        <flux:heading size="xl" level="1">Needs work</flux:heading>
        <flux:text class="mt-1">
            Streams freed by ended rentals. Change their passwords on the streaming server, enter the new ones here,
            then mark them ready to sell. Both passwords must change; enter N/A as the admin password if the server has none.
        </flux:text>
    </div>

    @if ($groups->isEmpty())
        <flux:card class="flex flex-col items-center gap-3 py-12 text-center">
            <flux:icon.check-circle class="size-8 text-emerald-500" />
            <flux:heading>Nothing needs work</flux:heading>
            <flux:text>Every free stream is ready to sell.</flux:text>
        </flux:card>
    @else
        <form wire:submit="markReady" class="flex flex-col gap-6">
            @foreach ($groups as $domain => $streams)
                <flux:card class="p-0" wire:key="group-{{ $domain }}">
                    <div class="flex items-center gap-3 px-6 pt-5 pb-3">
                        <flux:icon.server-stack class="size-5 text-zinc-500" />
                        <flux:heading size="lg">{{ $domain }}</flux:heading>
                        <flux:badge size="sm" color="amber">{{ $streams->count() }}</flux:badge>
                    </div>
                    <div class="px-6 pb-4">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Port</flux:table.column>
                                <flux:table.column>Current passwords</flux:table.column>
                                <flux:table.column>New admin password</flux:table.column>
                                <flux:table.column>New DJ password</flux:table.column>
                                <flux:table.column></flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($streams as $stream)
                                    <flux:table.row :key="$stream->id">
                                        <flux:table.cell>
                                            <a href="{{ route('streams.edit', $stream) }}" wire:navigate class="font-medium tabular-nums hover:underline">{{ $stream->port }}</a>
                                            <div class="text-xs text-zinc-500">{{ $stream->package->name ?? '' }}</div>
                                        </flux:table.cell>
                                        <flux:table.cell class="font-mono text-xs text-zinc-500">
                                            admin {{ $stream->admin_password }}<br>dj {{ $stream->dj_password }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:input size="sm" wire:model="passwords.{{ $stream->id }}.admin" placeholder="or N/A" class="font-mono" />
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:input size="sm" wire:model="passwords.{{ $stream->id }}.dj" class="font-mono" />
                                        </flux:table.cell>
                                        <flux:table.cell align="end">
                                            <flux:button size="sm" variant="ghost" icon="sparkles" wire:click="generate({{ $stream->id }})" tooltip="Fill in random passwords" />
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </flux:card>
            @endforeach

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="check">Mark updated streams ready</flux:button>
            </div>
        </form>
    @endif
</div>
