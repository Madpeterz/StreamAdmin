<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('packages.index')" wire:navigate>Packages</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $package ? $package->name : 'New package' }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ $package ? $package->name : 'New package' }}</flux:heading>
            @if ($package)
                <flux:text class="mt-1 flex flex-wrap items-center gap-1" x-data="{ copied: false }">
                    Package UID <span class="font-mono">{{ $package->package_uid }}</span>
                    <button type="button" x-on:click="navigator.clipboard.writeText('{{ $package->package_uid }}'); copied = true; setTimeout(() => copied = false, 1500)"
                        class="inline-flex items-center rounded p-0.5 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200" x-bind:title="copied ? 'Copied' : 'Copy package UID'" aria-label="Copy package UID">
                        <flux:icon.clipboard-document class="size-3.5" x-show="! copied" />
                        <flux:icon.check class="size-3.5 text-emerald-500" x-show="copied" x-cloak />
                    </button>
                    · use this in vendor configs
                </flux:text>
            @else
                <flux:text class="mt-1">A package is what vendors sell. Streams are added to it afterwards.</flux:text>
            @endif
        </div>
        @if ($package)
            <div class="flex flex-wrap items-center gap-2">
                <flux:badge color="emerald">{{ $stock['ready'] }} ready</flux:badge>
                <flux:badge color="amber">{{ $stock['need_work'] }} need work</flux:badge>
                <flux:badge color="sky">{{ $stock['sold'] }} sold</flux:badge>
                <flux:button size="sm" icon="signal" :href="route('streams.index', ['package' => $package->id])" wire:navigate>Streams</flux:button>
            </div>
        @endif
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:card class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Product</flux:heading>
                <flux:text class="text-sm">What the client gets for their money.</flux:text>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="name" label="Name" maxlength="30" placeholder="5 to 30 characters" />
                <flux:select wire:model="server_type_id" label="Server type">
                    @foreach ($serverTypes as $type)
                        <flux:select.option :value="$type->id">{{ $type->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="listener_limit" type="number" min="1" label="Listeners" />
                <flux:input wire:model="bitrate" type="number" min="56" label="Bitrate (kbps)" />
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <flux:field variant="inline">
                    <flux:switch wire:model.live="autodj" />
                    <flux:label>AutoDJ included</flux:label>
                </flux:field>
                @if ($autodj)
                    <flux:input wire:model="autodj_size" type="number" min="1" label="AutoDJ storage (GB)" />
                @endif
            </div>
        </flux:card>

        <flux:card class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Price</flux:heading>
                <flux:text class="text-sm">Vendors accept 1 to 4 periods in one payment.</flux:text>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="cost" type="number" min="1" label="Cost per period (L$)" />
                <flux:input wire:model="days" type="number" min="1" label="Period (days)" />
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <flux:field variant="inline">
                    <flux:switch wire:model.live="enforce_custom_max_streams" />
                    <flux:label>Limit how many of this package one avatar can rent</flux:label>
                </flux:field>
                @if ($enforce_custom_max_streams)
                    <flux:input wire:model="max_streams_in_package" type="number" min="1" label="Max per avatar" />
                @endif
            </div>
        </flux:card>

        <flux:card class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Vendor textures</flux:heading>
                <flux:text class="text-sm">Texture UUIDs shown on vendors for this package.</flux:text>
            </div>
            <x-ui.texture-input model="texture_instock_small" label="In stock" description:trailing="Shown in vendor lists." />
            <x-ui.texture-input model="texture_instock_selected" label="Selected" description:trailing="Shown when the package is picked." />
            <x-ui.texture-input model="texture_soldout" label="Sold out" description:trailing="Shown when no stream is ready." />
        </flux:card>

        <flux:card class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">Delivery</flux:heading>
                <flux:text class="text-sm">Templates and notecards sent to new clients.</flux:text>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                <flux:select wire:model="template_id" label="Details template">
                    @foreach ($templates as $template)
                        <flux:select.option :value="$template->id">{{ $template->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="welcome_notecard_id" label="Welcome notecard" description:trailing="Sent the first time a client rents.">
                    @foreach ($notecards as $notecard)
                        <flux:select.option :value="$notecard->id">{{ $notecard->name }}{{ $notecard->missing ? ' (missing)' : '' }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="setup_notecard_id" label="Setup notecard" description:trailing="Sent with every rental.">
                    @foreach ($notecards as $notecard)
                        <flux:select.option :value="$notecard->id">{{ $notecard->name }}{{ $notecard->missing ? ' (missing)' : '' }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <flux:field variant="inline">
                <flux:switch wire:model="enable_group_invite" />
                <flux:label>Invite new clients to the group (needs the bot)</flux:label>
            </flux:field>
        </flux:card>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                @if ($package)
                    <flux:modal.trigger name="package-delete">
                        <flux:button variant="ghost" icon="trash" class="text-red-600!">Remove package</flux:button>
                    </flux:modal.trigger>
                @endif
            </div>
            <div class="flex gap-2">
                <flux:button :href="route('packages.index')" variant="ghost" wire:navigate>Cancel</flux:button>
                <flux:button type="submit" variant="primary">{{ $package ? 'Save package' : 'Create package' }}</flux:button>
            </div>
        </div>
    </form>

    @if ($package)
        <flux:modal name="package-delete" class="md:w-md">
            <div class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">Remove {{ $package->name }}?</flux:heading>
                    <flux:text class="mt-2">Only possible when no streams, clients or tree vendors use it. Old transactions keep their amounts but lose the package link.</flux:text>
                </div>
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                    <flux:button variant="danger" wire:click="delete">Remove package</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
