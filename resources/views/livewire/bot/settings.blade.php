<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Bot</flux:heading>
            <flux:text class="mt-1">A Second Life bot can IM clients, write notecards and send group invites for you.</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button href="https://wiki.blackatom.win/" target="_blank" variant="ghost" icon="book-open">Bot wiki</flux:button>
            @if ($httpSaved)
                <flux:button wire:click="testHttp" icon="signal">Test HTTP</flux:button>
            @endif
        </div>
    </div>

    <flux:callout icon="information-circle" color="sky">
        <flux:callout.text>
            The bot picks up its work from the outbox. {{ $queued }} {{ str('command')->plural($queued) }} waiting now.
            The scheduler must be running for the bot to be sent its commands.
        </flux:callout.text>
    </flux:callout>

    <form wire:submit="save" class="flex flex-col gap-6">
        <div class="grid gap-6 md:grid-cols-2">
            <flux:card class="flex flex-col gap-5">
                <flux:heading size="lg">Bot avatar</flux:heading>
                <x-ui.avatar-picker label="Avatar" :selected="$botAvatar" :matches="$matches" :error="$errors->first('avatar_uid')" />
                <flux:text class="-mt-3 text-sm">The bot has to exist on the Avatars page first. Search by name, avatar UID or SL UUID.</flux:text>
                <flux:input wire:model="secret" type="password" viewable label="Secret" description="Shared with the bot (its .json settings or env value). 8 to 30 characters." />
            </flux:card>

            <flux:card class="flex flex-col gap-4">
                <flux:heading size="lg">What it does</flux:heading>
                <flux:field variant="inline">
                    <flux:switch wire:model="ims" />
                    <flux:label>Send IMs</flux:label>
                </flux:field>
                <flux:field variant="inline">
                    <flux:switch wire:model="notecards" />
                    <flux:label>Write notecards</flux:label>
                </flux:field>
                <flux:separator variant="subtle" />
                <flux:field variant="inline">
                    <flux:switch wire:model.live="invites" />
                    <flux:label>Invite new clients to a group</flux:label>
                </flux:field>
                @if ($invites)
                    <flux:input wire:model="invite_group_uuid" label="Group UUID" description="Invited with the everyone role. You can turn invites off per package." class="font-mono" />
                @endif
            </flux:card>
        </div>

        <flux:card class="flex flex-col gap-4">
            <div>
                <flux:heading size="lg">HTTP mode</flux:heading>
                <flux:text class="text-sm">Send commands straight to the bot over HTTP instead of by IM.</flux:text>
            </div>
            <flux:field variant="inline">
                <flux:switch wire:model.live="http_mode" />
                <flux:label>Use HTTP</flux:label>
            </flux:field>
            @if ($http_mode)
                <flux:input wire:model="http_url" type="url" label="Bot URL" placeholder="https://bot.example.com/" class="max-w-lg" />
            @endif
        </flux:card>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">Save bot settings</flux:button>
        </div>
    </form>
</div>
