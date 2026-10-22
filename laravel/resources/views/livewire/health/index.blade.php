<div class="mx-auto flex w-full max-w-7xl flex-col gap-6" wire:poll.15s.visible>
    <div>
        <flux:heading size="xl" level="1">Health</flux:heading>
        <flux:text class="mt-1">Your in-world objects check in every minute; anything not seen for 2 minutes counts as down.</flux:text>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <flux:card class="lg:col-span-1">
            <flux:heading size="lg">Service objects</flux:heading>
            <flux:text class="text-sm">Owned by the system owner. Which ones you need depends on your bot and events settings.</flux:text>
            <div class="mt-3 divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($servers as $server)
                    <div class="flex items-center justify-between gap-3 py-2.5" wire:key="server-{{ $server['mode'] }}">
                        <div>
                            <div class="text-sm font-medium">{{ $server['name'] }}</div>
                            <div class="text-xs text-zinc-500">{{ $server['region'] ?? 'Never seen' }}</div>
                        </div>
                        <x-ui.last-seen :at="$server['last_seen']" />
                    </div>
                @endforeach
            </div>
        </flux:card>

        <flux:card class="p-0 lg:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-3 px-6 pt-5">
                <div>
                    <flux:heading size="lg">Vendors by region</flux:heading>
                    <flux:text class="text-sm">Reseller vendors and other objects, worst region first. Pick a region to list its objects.</flux:text>
                </div>
                @if ($vendorPercent !== null)
                    <flux:badge :color="\App\Services\ObjectHealth::percentColor($vendorPercent)">{{ $vendorPercent }}% online</flux:badge>
                @endif
            </div>
            <div class="px-6 py-2">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Region</flux:table.column>
                        <flux:table.column>Online</flux:table.column>
                        <flux:table.column align="end">Up</flux:table.column>
                        <flux:table.column align="end">Down</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($regions as $row)
                            <flux:table.row :key="'region-'.$row['region']->id">
                                <flux:table.cell>
                                    <button type="button" wire:click="showRegion({{ $row['region']->id }})" @class(['font-medium hover:underline', 'text-accent-content' => $region === $row['region']->id])>{{ $row['region']->name }}</button>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <div class="h-1.5 w-24 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <div @class([
                                                'h-full rounded-full',
                                                'bg-emerald-500' => $row['percent'] >= 75,
                                                'bg-amber-500' => $row['percent'] >= 50 && $row['percent'] < 75,
                                                'bg-red-500' => $row['percent'] < 50,
                                            ]) style="width: {{ $row['percent'] }}%"></div>
                                        </div>
                                        <span class="text-sm tabular-nums">{{ $row['percent'] }}%</span>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell align="end" class="tabular-nums">{{ $row['up'] }}</flux:table.cell>
                                <flux:table.cell align="end" class="tabular-nums">{{ $row['down'] }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="py-6 text-center text-zinc-500">No vendors have checked in yet.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    </div>

    <flux:card>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="lg">Scheduled tasks</flux:heading>
                <flux:text class="text-sm">Run by StreamAdmin itself, in place of in-world server objects for these queues.</flux:text>
            </div>
            @unless ($schedulerOn)
                <flux:badge color="amber" size="sm">Scheduler off (SCHEDULER_ENABLED)</flux:badge>
            @endunless
        </div>
        <div class="mt-3 divide-y divide-zinc-100 dark:divide-zinc-800">
            @foreach ($tasks as $task)
                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 py-2.5" wire:key="task-{{ $task['key'] }}">
                    <div class="min-w-48 flex-1">
                        <div class="text-sm font-medium">{{ $task['label'] }}</div>
                        <div class="text-xs text-zinc-500">{{ $task['every'] }}</div>
                    </div>
                    <div class="min-w-0 flex-1 text-sm">
                        @if ($task['last'])
                            @if (! $task['on'])
                                <span class="font-medium text-zinc-500">Paused</span>
                            @else
                                <span @class(['font-medium', 'text-emerald-600 dark:text-emerald-400' => $task['last']['status'], 'text-red-600 dark:text-red-400' => ! $task['last']['status']])>{{ $task['last']['status'] ? 'OK' : 'Failed' }}</span>
                            @endif
                            <span class="text-zinc-500">{{ $task['last']['at']->diffForHumans() }}</span>
                            <div class="truncate text-xs text-zinc-500" title="{{ $task['last']['message'] }}">{{ $task['last']['message'] }}</div>
                        @else
                            <span class="text-zinc-500">Not run yet</span>
                        @endif
                    </div>
                    @can('owner')
                        <flux:switch :checked="$task['on']" wire:click="toggleTask('{{ $task['key'] }}')" aria-label="Run {{ $task['label'] }}" />
                    @else
                        <flux:badge size="sm" :color="$task['on'] ? 'zinc' : 'amber'">{{ $task['on'] ? 'On' : 'Paused' }}</flux:badge>
                    @endcan
                </div>
            @endforeach
        </div>
    </flux:card>

    <flux:card class="p-0">
        <div class="flex flex-wrap items-end justify-between gap-4 px-6 pt-5">
            <div>
                <flux:heading size="lg">
                    Objects
                    @if ($regionName)
                        in {{ $regionName }}
                    @endif
                </flux:heading>
                <flux:text class="text-sm">Everything that has checked in, most recent first.</flux:text>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($region)
                    <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="showRegion(null)">All regions</flux:button>
                @endif
                <div class="w-full sm:w-64">
                    <flux:input size="sm" wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search name, type or owner" />
                </div>
                @can('owner')
                    <flux:modal.trigger name="objects-clear">
                        <flux:button size="sm" variant="ghost" icon="trash" class="text-red-600!">Clear list</flux:button>
                    </flux:modal.trigger>
                @endcan
            </div>
        </div>
        <div class="px-6 py-2">
            <flux:table :paginate="$objects">
                <flux:table.columns>
                    <flux:table.column>Object</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Owner</flux:table.column>
                    <flux:table.column>Region</flux:table.column>
                    <flux:table.column>Last seen</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($objects as $object)
                        @php([$name, $script] = array_pad(explode('S:', $object->object_name, 2), 2, null))
                        <flux:table.row :key="'object-'.$object->id">
                            <flux:table.cell>
                                <div class="font-medium">{{ trim($name) }}</div>
                                @if ($script)
                                    <div class="font-mono text-xs text-zinc-500">{{ trim($script) }}</div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell><flux:badge size="sm" color="zinc">{{ $object->object_mode }}</flux:badge></flux:table.cell>
                            <flux:table.cell>{{ $object->avatar->avatar_name ?? 'Unknown' }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($url = \App\Services\ObjectHealth::mapUrl($object))
                                    <flux:link :href="$url" external class="text-sm">{{ $object->region->name }}</flux:link>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell><x-ui.last-seen :at="$object->last_seen" :warn-after="65" :fail-after="120" /></flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-8 text-center text-zinc-500">No objects{{ $search !== '' ? ' match that search' : ' yet' }}.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

    @can('owner')
        <flux:modal name="objects-clear" class="md:w-md">
            <div class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">Clear the object list?</flux:heading>
                    <flux:text class="mt-2">Objects that are still running add themselves back the next time they check in. Use this to drop objects that were deleted in-world.</flux:text>
                </div>
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                    <flux:button variant="danger" wire:click="clearObjects" x-on:click="$flux.modal('objects-clear').close()">Clear list</flux:button>
                </div>
            </div>
        </flux:modal>
    @endcan
</div>
