<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('streams.index')" wire:navigate>Streams</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $stream ? $stream->server?->domain.':'.$stream->port : 'Add stream' }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <flux:heading size="xl" level="1">{{ $stream ? $stream->server?->domain.':'.$stream->port : 'Add stream' }}</flux:heading>
                @if ($stream)
                    @include('livewire.streams.partials.status', ['stream' => $stream])
                @endif
            </div>
            @if ($stream)
                <flux:text class="mt-1">Stream UID <span class="font-mono">{{ $stream->stream_uid }}</span></flux:text>
            @else
                <flux:text class="mt-1">Set the stream up on the streaming server first, then add its details here.</flux:text>
            @endif
        </div>
        @if ($stream?->rental)
            <flux:button icon="user" :href="route('clients.show', $stream->rental)" wire:navigate>
                Rented by {{ $stream->rental->avatar->avatar_name ?? 'unknown avatar' }}
            </flux:button>
        @endif
    </div>

    @if ($stream?->need_work && ! $stream->rental_id)
        <flux:callout icon="wrench-screwdriver" color="amber">
            <flux:callout.heading>This stream needs work</flux:callout.heading>
            <flux:callout.text>
                Change its passwords on the streaming server, then save the new ones here. Saving marks it ready to sell.
                Several streams at once? Use <flux:link :href="route('streams.needs-work')" wire:navigate>Needs work</flux:link>.
            </flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:card class="flex flex-col gap-5">
            <flux:heading size="lg">Where</flux:heading>
            <div class="grid gap-5 md:grid-cols-3">
                <flux:select wire:model.live="server_id" label="Server">
                    @foreach ($servers as $server)
                        <flux:select.option :value="$server->id">{{ $server->domain }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="port" type="number" min="1" label="Port" />
                <flux:input wire:model="mountpoint" label="Mount point" placeholder="/live" />
            </div>
            <flux:select wire:model="package_id" label="Package" :description:trailing="$stream?->rental ? 'Changing the package also moves the rental to it.' : null">
                @foreach ($packages as $package)
                    <flux:select.option :value="$package->id">{{ $package->name }} · {{ $package->listeners }} listeners · {{ $package->bitrate }} kbps</flux:select.option>
                @endforeach
            </flux:select>
        </flux:card>

        <flux:card class="flex flex-col gap-5">
            <flux:heading size="lg">Logins</flux:heading>
            <div class="grid gap-5 md:grid-cols-3">
                <flux:input wire:model="admin_username" label="Admin username" />
                <flux:input wire:model="admin_password" label="Admin password" viewable type="password" />
                <flux:input wire:model="dj_password" label="DJ / source password" viewable type="password" />
            </div>
            @unless ($stream)
                <flux:field variant="inline">
                    <flux:switch wire:model="need_work" />
                    <flux:label>Needs work before it can be sold</flux:label>
                </flux:field>
            @endunless
        </flux:card>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex gap-2">
                @if ($stream && ! $stream->rental_id)
                    <flux:modal.trigger name="stream-delete">
                        <flux:button variant="ghost" icon="trash" class="text-red-600!">Remove</flux:button>
                    </flux:modal.trigger>
                    <flux:modal.trigger name="stream-restore">
                        <flux:button variant="ghost" icon="arrow-uturn-left">Restore to last client</flux:button>
                    </flux:modal.trigger>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('streams.index')" variant="ghost" wire:navigate>Cancel</flux:button>
                @unless ($stream)
                    <flux:button wire:click="saveAndNext" icon="plus">Create and add next port</flux:button>
                @endunless
                <flux:button type="submit" variant="primary">{{ $stream ? 'Save stream' : 'Create stream' }}</flux:button>
            </div>
        </div>
    </form>

    @if ($stream)
        <flux:modal name="stream-delete" class="md:w-md">
            <div class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">Remove port {{ $stream->port }}?</flux:heading>
                    <flux:text class="mt-2">Old transactions keep their amounts but lose the stream link. This cannot be undone.</flux:text>
                </div>
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                    <flux:button variant="danger" wire:click="delete">Remove stream</flux:button>
                </div>
            </div>
        </flux:modal>

        <flux:modal name="stream-restore" class="md:w-md">
            <div class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">Restore to the last client?</flux:heading>
                    <flux:text class="mt-2">
                        Creates a rental for the avatar who last bought this stream. It starts with no time left,
                        so the client needs to renew to keep it.
                    </flux:text>
                </div>
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                    <flux:button variant="primary" wire:click="restore">Restore</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
