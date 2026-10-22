<div class="mx-auto flex w-full max-w-2xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('clients.index')" wire:navigate>Clients</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Add client</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div>
        <flux:heading size="xl" level="1">Add client</flux:heading>
        <flux:text class="mt-1">Give a stream to an avatar without a payment, for example after a sale made somewhere else. A free (L$0) transaction is logged.</flux:text>
    </div>

    <form wire:submit="save">
        <flux:card class="flex flex-col gap-5">
            <x-ui.avatar-picker label="Avatar" :selected="$selected" :matches="$matches" :error="$errors->first('avatarUid')" />

            <flux:select wire:model="streamUid" label="Stream" placeholder="Pick a free stream">
                @foreach ($groups as $domain => $streams)
                    <flux:select.option disabled>{{ $domain }}</flux:select.option>
                    @foreach ($streams as $stream)
                        <flux:select.option :value="$stream->stream_uid">{{ $domain }}:{{ $stream->port }} · {{ $stream->package->name ?? 'No package' }}{{ $stream->need_work ? ' · needs work' : '' }}</flux:select.option>
                    @endforeach
                @endforeach
            </flux:select>
            <flux:error name="streamUid" />

            <flux:input wire:model="days" type="number" min="1" max="360" label="Days" description:trailing="How long the rental runs from now." class="max-w-40" />

            <div class="flex justify-end gap-2">
                <flux:button :href="route('clients.index')" wire:navigate variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Create client</flux:button>
            </div>
        </flux:card>
    </form>
</div>
