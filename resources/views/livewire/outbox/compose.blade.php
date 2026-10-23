<div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('outbox.index')" wire:navigate>Outbox</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Send a message</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div>
        <flux:heading size="xl" level="1">Send a message</flux:heading>
        <flux:text class="mt-1">Goes out through the mail server object, and as a bot IM when bot IMs are on. Banned avatars are left out.</flux:text>
    </div>

    @php($count = $source === 'avatar' ? ($avatar ? 1 : 0) : count($selected))

    <form wire:submit="send" wire:confirm="Send this message to {{ $source === 'avatar' && $avatar ? $avatar->avatar_name : $count.' '.str('avatar')->plural($count) }}?" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="flex flex-col gap-6">
            <flux:card class="flex flex-col gap-5">
                <flux:heading size="lg">Who to</flux:heading>
                @if ($source === 'rental')
                    <div class="flex items-center gap-3">
                        <flux:avatar size="sm" :name="$single?->avatar?->avatar_name ?? '?'" />
                        <div>
                            <div class="font-medium">{{ $single?->avatar?->avatar_name ?? 'Unknown client' }}</div>
                            <div class="font-mono text-xs text-zinc-500">{{ $single?->rental_uid }}</div>
                        </div>
                        <flux:button size="sm" variant="ghost" class="ml-auto" wire:click="$set('source', 'clients')">Send to a group instead</flux:button>
                    </div>
                @else
                    <flux:radio.group wire:model.live="source" variant="segmented">
                        <flux:radio value="clients" label="All clients" />
                        <flux:radio value="notice" label="Notice level" />
                        <flux:radio value="package" label="Package" />
                        <flux:radio value="server" label="Server" />
                        <flux:radio value="avatar" label="One avatar" />
                    </flux:radio.group>
                    @if ($source === 'avatar')
                        <div class="max-w-lg">
                            <x-ui.avatar-picker label="Avatar" :selected="$avatar" :matches="$matches" :error="$errors->first('avatarUid')" />
                        </div>
                        <flux:text class="text-sm">Any avatar can be messaged, with or without a rental. If they have rentals the oldest fills the swap tags; otherwise only the avatar name tags are filled. Banned avatars are not sent messages.</flux:text>
                    @elseif ($source !== 'clients')
                        <div class="max-w-sm">
                            <flux:select wire:model.live="sourceId" :placeholder="'Choose a '.['notice' => 'notice level', 'package' => 'package', 'server' => 'server'][$source].'…'">
                                @foreach ($options as $id => $label)
                                    <flux:select.option :value="$id">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif
                @endif

                @if ($source === 'avatar')
                    {{-- the avatar is chosen above --}}
                @elseif ($recipients->isNotEmpty() && $source !== 'rental')
                    <div class="flex flex-col gap-3">
                        <flux:text class="text-sm">{{ count($selected) }} of {{ $recipients->count() }} {{ str('avatar')->plural($recipients->count()) }} selected. If an avatar has several matching rentals, the oldest one fills the swap tags.</flux:text>
                        <flux:checkbox.group wire:model.live="selected" class="max-h-72 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                            <flux:checkbox.all label="Everyone" class="mb-2" />
                            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($recipients as $rental)
                                    <flux:checkbox wire:key="to-{{ $rental->avatar_id }}" :value="(string) $rental->avatar_id" :label="$rental->avatar->avatar_name" />
                                @endforeach
                            </div>
                        </flux:checkbox.group>
                    </div>
                @elseif ($source === 'clients' || $sourceId !== null)
                    @if ($recipients->isEmpty())
                        <flux:callout icon="information-circle" color="zinc" text="No clients to send to for this choice." />
                    @endif
                @endif
                @if ($source !== 'avatar')
                    <flux:error name="selected" />
                @endif
            </flux:card>

            <flux:card class="flex flex-col gap-3">
                <div class="flex items-baseline justify-between">
                    <flux:heading size="lg">Message</flux:heading>
                    <span @class(['text-xs tabular-nums', 'text-red-600' => mb_strlen($message) > 800, 'text-zinc-500' => mb_strlen($message) <= 800])>{{ mb_strlen($message) }} / 800</span>
                </div>
                <flux:textarea id="compose-message" data-swaps wire:model.live.debounce.400ms="message" rows="7" class="font-mono text-sm" placeholder="Hello [[AVATAR_FIRSTNAME]], …" />
                <flux:error name="message" />
            </flux:card>

            <div class="flex justify-end gap-2">
                <flux:button :href="route('outbox.index')" variant="ghost" wire:navigate>Cancel</flux:button>
                <flux:button type="submit" variant="primary" icon="paper-airplane">
                    Send to {{ $source === 'avatar' && $avatar ? $avatar->avatar_name : $count.' '.str('avatar')->plural($count) }}
                </flux:button>
            </div>
        </div>

        <div class="flex flex-col gap-6 lg:sticky lg:top-6 lg:self-start">
            <flux:card class="flex flex-col gap-3">
                <flux:heading size="sm">Preview</flux:heading>
                <flux:text class="text-sm">{{ $previewFor ? 'As '.$previewFor.' will see it.' : 'With example values filled in.' }}</flux:text>
                <div class="rounded-lg bg-zinc-50 p-3 text-sm whitespace-pre-line dark:bg-zinc-800/60">{{ $preview !== '' ? $preview : 'The message preview shows here.' }}</div>
            </flux:card>
            <flux:card>
                <x-ui.swap-tags target="compose-message" />
            </flux:card>
        </div>
    </form>
</div>
