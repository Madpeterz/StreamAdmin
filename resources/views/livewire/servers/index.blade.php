<div class="mx-auto flex w-full max-w-7xl flex-col gap-6" wire:poll.120s>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Servers</flux:heading>
            <flux:text class="mt-1">Streaming servers, their streams and how much of their capacity the packages on them need.</flux:text>
        </div>
        <flux:modal.trigger name="server-form">
            <flux:button variant="primary" icon="plus" wire:click="resetForm">Add server</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        @forelse ($servers as ['server' => $server, 'counts' => $counts, 'bandwidth' => $bandwidth, 'storage' => $storage])
            <flux:card wire:key="server-{{ $server->id }}" class="flex flex-col gap-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon.server-stack class="size-5 text-zinc-500" />
                        </div>
                        <div class="min-w-0">
                            <flux:heading class="truncate">{{ $server->domain }}</flux:heading>
                            <flux:text class="truncate text-xs">{{ $server->ip_address ?? 'No IP address set' }}</flux:text>
                        </div>
                    </div>
                    <flux:dropdown align="end">
                        <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                        <flux:menu>
                            <flux:menu.item icon="pencil-square" wire:click="edit({{ $server->id }})">Edit</flux:menu.item>
                            <flux:menu.item icon="arrow-top-right-on-square" :href="$server->control_panel_url" target="_blank">Control panel</flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $server->id }})">Remove</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center">
                    @foreach (['sold' => ['Sold', 'sky', 'sold'], 'ready' => ['Ready', 'emerald', 'ready'], 'need_work' => ['Needs work', 'amber', 'needs-work']] as $key => [$label, $color, $status])
                        <a href="{{ route('streams.index', ['status' => $status, 'server' => $server->id]) }}" wire:navigate
                           class="rounded-lg bg-zinc-50 py-2 transition hover:bg-zinc-100 dark:bg-zinc-800/60 dark:hover:bg-zinc-800">
                            <div class="text-xs text-zinc-500">{{ $label }}</div>
                            <div class="mt-0.5 text-lg font-semibold tabular-nums">{{ $counts[$key] }}</div>
                        </a>
                    @endforeach
                </div>

                <div class="flex flex-col gap-3">
                    <x-ui.capacity-bar label="Bandwidth" :used="$bandwidth['used']" :assigned="$bandwidth['assigned']"
                        :capacity="number_format($server->bandwidth).' '.($server->bandwidth_type === 'gpbs' ? 'gbps' : $server->bandwidth_type)" />
                    <x-ui.capacity-bar label="AutoDJ storage" :used="$storage['used']" :assigned="$storage['assigned']"
                        :capacity="number_format($server->total_storage).' '.strtoupper($server->total_storage_type)" />
                </div>
            </flux:card>
        @empty
            <flux:card class="col-span-full flex flex-col items-center gap-3 py-12 text-center">
                <flux:icon.server-stack class="size-8 text-zinc-400" />
                <flux:heading>No servers yet</flux:heading>
                <flux:text>Add your first streaming server, then create streams on it.</flux:text>
                <flux:modal.trigger name="server-form">
                    <flux:button variant="primary" icon="plus" wire:click="resetForm">Add server</flux:button>
                </flux:modal.trigger>
            </flux:card>
        @endforelse
    </div>

    <flux:modal name="server-form" class="md:w-xl">
        <form wire:submit="save" class="flex flex-col gap-5">
            <flux:heading size="lg">{{ $editingId ? 'Edit server' : 'Add server' }}</flux:heading>
            <flux:input wire:model="domain" label="Domain" placeholder="stream.example.com" />
            <flux:input wire:model="control_panel_url" label="Control panel URL" placeholder="https://cp.example.com" />
            <flux:input wire:model="ip_address" label="IP address" description:trailing="Optional. Used by the [[SERVER_IP]] swap tag." placeholder="127.0.0.1" />
            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2"><flux:input wire:model="bandwidth" type="number" min="1" label="Bandwidth" /></div>
                <flux:select wire:model="bandwidth_type" label="Unit">
                    <flux:select.option value="kbps">kbps</flux:select.option>
                    <flux:select.option value="mbps">mbps</flux:select.option>
                    <flux:select.option value="gbps">gbps</flux:select.option>
                </flux:select>
                <div class="col-span-2"><flux:input wire:model="total_storage" type="number" min="1" label="AutoDJ storage" /></div>
                <flux:select wire:model="total_storage_type" label="Unit">
                    <flux:select.option value="mb">MB</flux:select.option>
                    <flux:select.option value="gb">GB</flux:select.option>
                    <flux:select.option value="tb">TB</flux:select.option>
                </flux:select>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Save' : 'Add server' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="server-delete" class="md:w-md">
        <div class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Remove this server?</flux:heading>
                <flux:text class="mt-2">Servers with streams cannot be removed. This cannot be undone.</flux:text>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button variant="danger" wire:click="delete">Remove server</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
