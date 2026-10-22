<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <div>
        <flux:heading size="xl" level="1">System settings</flux:heading>
        <flux:text class="mt-1">How StreamAdmin runs. Only owners can see this page.</flux:text>
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">
        <div class="grid gap-6 md:grid-cols-2">
            <flux:card class="flex flex-col gap-5">
                <flux:heading size="lg">General</flux:heading>
                <flux:field>
                    <flux:label>System owner</flux:label>
                    <flux:description>Avatar UID of the owner. Their objects run the mail, notice and other servers, and they receive the owner's share of sales.</flux:description>
                    <flux:input wire:model.live.debounce.400ms="owner_avatar_uid" maxlength="8" class="font-mono" />
                    <flux:error name="owner_avatar_uid" />
                    @if ($ownerAvatar)
                        <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                            <flux:avatar size="xs" :name="$ownerAvatar->avatar_name" /> {{ $ownerAvatar->avatar_name }}
                        </div>
                    @endif
                </flux:field>
                <flux:select wire:model="display_timezone_id" label="Timezone" description="Times on this site and in swap tags use this zone.">
                    @foreach ($timezones as $timezone)
                        <flux:select.option :value="$timezone->id">{{ $timezone->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:field variant="inline">
                    <flux:switch wire:model="events_api" />
                    <flux:label>Events API</flux:label>
                </flux:field>
                <flux:text class="-mt-3 text-sm">Queue rental events (new, renew, expire…) for your own event hook. <flux:link href="https://github.com/Madpeterz/StreamAdmin/wiki/Events-API" external>About the Events API</flux:link></flux:text>
            </flux:card>

            <flux:card class="flex flex-col gap-5">
                <flux:heading size="lg">Resellers</flux:heading>
                <flux:field variant="inline">
                    <flux:switch wire:model.live="new_resellers" />
                    <flux:label>Accept new resellers automatically</flux:label>
                </flux:field>
                <flux:input wire:model="new_resellers_rate" type="number" min="1" max="100" label="Rate for new resellers (%)" description:trailing="The share of each sale they keep. You can change it per reseller later." class="max-w-40" />

                <flux:separator variant="subtle" />

                <flux:heading size="lg">Limits</flux:heading>
                <div class="grid grid-cols-[auto_1fr] items-end gap-x-4 gap-y-4">
                    <div class="pb-2.5"><flux:switch wire:model.live="limit_streams" /></div>
                    <flux:input wire:model="max_total_streams" type="number" min="1" max="2000" :disabled="! $limit_streams" label="Most streams one client can rent" />
                    <div class="pb-2.5"><flux:switch wire:model.live="limit_time" /></div>
                    <flux:input wire:model="max_stream_time_days" type="number" min="7" max="365" :disabled="! $limit_time" label="Most days a rental can run ahead" />
                </div>
            </flux:card>
        </div>

        <flux:card class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Renter HUD</flux:heading>
                <flux:text class="text-sm">What clients can do from their HUD.</flux:text>
            </div>
            <div class="grid gap-x-8 gap-y-4 md:grid-cols-2">
                <div class="flex flex-col gap-4">
                    <flux:field variant="inline">
                        <flux:switch wire:model.live="hud_allow_details" />
                        <flux:label>Clients can ask for their stream details</flux:label>
                    </flux:field>
                    <flux:field variant="inline">
                        <flux:switch wire:model="hud_allow_renewal" :disabled="! $hud_allow_details || $limit_time" />
                        <flux:label>Clients can renew from the HUD</flux:label>
                    </flux:field>
                    @if ($limit_time)
                        <flux:text class="-mt-2 text-sm">HUD renewals are off while the rental time limit is on.</flux:text>
                    @elseif (! $hud_allow_details)
                        <flux:text class="-mt-2 text-sm">HUD renewals need stream details turned on.</flux:text>
                    @endif
                </div>
                <div class="flex flex-col gap-4">
                    <flux:field variant="inline">
                        <flux:switch wire:model.live="hud_allow_discord" />
                        <flux:label>Show a Discord link</flux:label>
                    </flux:field>
                    @if ($hud_allow_discord)
                        <flux:input wire:model="hud_discord_link" type="url" placeholder="https://discord.gg/…" />
                    @endif
                    <flux:field variant="inline">
                        <flux:switch wire:model.live="hud_allow_group" />
                        <flux:label>Show a group link</flux:label>
                    </flux:field>
                    @if ($hud_allow_group)
                        <flux:input wire:model="hud_group_link" placeholder="secondlife:///app/group/…/about" />
                    @endif
                </div>
            </div>
        </flux:card>

        <flux:card class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Marketplace coupons</flux:heading>
                <flux:text class="text-sm">Sell credit through Second Life Marketplace listings; see Coupons for the listings and ANS URL.</flux:text>
            </div>
            <flux:field variant="inline">
                <flux:switch wire:model.live="enable_coupons" />
                <flux:label>Enable coupons</flux:label>
            </flux:field>
            <flux:input wire:model="ans_salt" type="password" viewable label="ANS salt" description:trailing="From the Automated Notification settings of your Marketplace store." class="max-w-md" :disabled="! $enable_coupons" />
        </flux:card>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">Save settings</flux:button>
        </div>
    </form>

    <div class="grid gap-6 md:grid-cols-2">
        <flux:card class="flex flex-col gap-4">
            <div>
                <flux:heading size="lg">Access keys</flux:heading>
                <flux:text class="text-sm">Set these in your in-world objects so they can talk to StreamAdmin.</flux:text>
            </div>
            <dl class="flex flex-col gap-3">
                <x-ui.secret label="Vendors link code" :secret="$config->sl_link_code" />
                <x-ui.secret label="Owner objects link code" :secret="$config->owner_link_code" />
                <x-ui.secret label="Renter HUD link code" :secret="$config->hud_link_code" />
            </dl>
            <div>
                <flux:modal.trigger name="reissue">
                    <flux:button variant="danger" icon="arrow-path" size="sm">Reissue keys</flux:button>
                </flux:modal.trigger>
            </div>
        </flux:card>

        <flux:card class="flex flex-col gap-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">Support key</flux:heading>
                    <flux:text class="text-sm">Your StreamAdmin support key for this site.</flux:text>
                </div>
                <flux:badge size="sm" :color="$keyStatus['ok'] ? 'emerald' : 'amber'">{{ $keyStatus['status'] }}</flux:badge>
            </div>
            @if ($keyStatus['expires'])
                <flux:text class="text-sm">{{ $keyStatus['ok'] ? 'Expires' : 'Expired' }} {{ \Carbon\CarbonImmutable::createFromTimestamp($keyStatus['expires'])->diffForHumans() }}.</flux:text>
            @endif
            <form wire:submit="savePaymentKey" class="flex items-start gap-2">
                <div class="flex-1">
                    <flux:input wire:model="payment_key" maxlength="23" class="font-mono" placeholder="23 character key" />
                    <flux:error name="payment_key" />
                </div>
                <flux:button type="submit">Save key</flux:button>
            </form>
        </flux:card>
    </div>

    <flux:card class="flex flex-wrap items-center justify-between gap-4">
        <div class="max-w-xl">
            <flux:heading size="lg">Stream spreadsheet</flux:heading>
            <flux:text class="text-sm">Every stream with its client, logins and expiry, as an Excel file. It holds stream passwords, so keep it safe. A daily copy can be saved on the server from the Health page.</flux:text>
        </div>
        <flux:button :href="route('export.download')" icon="arrow-down-tray">Download</flux:button>
    </flux:card>

    <flux:modal name="reissue" class="md:w-md">
        <div class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Reissue all keys?</flux:heading>
                <flux:text class="mt-2">Every vendor, server, HUD and custom script stops working until it is given the new link codes. Only do this if a code has leaked.</flux:text>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button variant="danger" wire:click="reissue">Reissue keys</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
