<div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('notices.index')" wire:navigate>Notices</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $notice?->name ?? 'New notice' }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div>
        <flux:heading size="xl" level="1">{{ $notice?->name ?? 'New notice' }}</flux:heading>
        <flux:text class="mt-1">
            @if ($this->isExpiredLevel())
                Sent when a rental runs out.
            @elseif ($notice)
                {{ $clientCount }} {{ str('client')->plural($clientCount) }} at this level now.
            @else
                Sent once to each client when their rental reaches the hours left you set.
            @endif
        </flux:text>
    </div>

    <form wire:submit="save" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="flex flex-col gap-6">
            <flux:card class="flex flex-col gap-5">
                <div class="grid items-start gap-5 sm:grid-cols-2">
                    <flux:input wire:model="name" label="Name" />
                    @if ($this->isExpiredLevel())
                        <flux:input value="0" label="Hours remaining" description:trailing="The Expired notice always triggers at 0." disabled />
                    @else
                        <flux:input wire:model="hours_remaining" type="number" min="1" max="998" label="Hours remaining" description:trailing="Triggers when a rental has this many hours left (1 to 998)." />
                    @endif
                </div>

                <flux:field>
                    <div class="flex items-baseline justify-between">
                        <flux:label>Message</flux:label>
                        <span @class(['text-xs tabular-nums', 'text-red-600' => mb_strlen($im_message) > 800, 'text-zinc-500' => mb_strlen($im_message) <= 800])>{{ mb_strlen($im_message) }} / 800</span>
                    </div>
                    <flux:textarea id="notice-message" data-swaps wire:model.live.debounce.400ms="im_message" rows="6" class="font-mono text-sm" />
                    <flux:error name="im_message" />
                </flux:field>

                <div class="flex flex-col gap-3">
                    <flux:heading size="sm">Send it by</flux:heading>
                    <flux:field variant="inline">
                        <flux:switch wire:model="send_object_im" />
                        <flux:label>IM from the in-world mail server object</flux:label>
                    </flux:field>
                    <flux:field variant="inline">
                        <flux:switch wire:model="use_bot" />
                        <flux:label>IM from the bot</flux:label>
                    </flux:field>
                </div>
            </flux:card>

            <flux:card class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">Notecards</flux:heading>
                    <flux:text class="text-sm">Optionally give the client a notecard with the notice.</flux:text>
                </div>

                <div class="flex flex-col gap-3">
                    <flux:field variant="inline">
                        <flux:switch wire:model.live="send_notecard" />
                        <flux:label>Write a notecard for each client (needs the bot)</flux:label>
                    </flux:field>
                    @if ($send_notecard)
                        <flux:textarea data-swaps wire:model="notecard_detail" rows="8" class="font-mono text-sm" placeholder="Notecard content, swap tags work here too" />
                        <flux:error name="notecard_detail" />
                    @endif
                </div>

                <div class="max-w-sm">
                    <flux:select wire:model="notice_notecard_id" label="Static notecard" description="A notecard from the notecard server, given as well.">
                        @foreach ($notecards as $notecard)
                            <flux:select.option :value="$notecard->id">{{ $notecard->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @if ($currentNotecardMissing)
                        <flux:text class="mt-2 text-sm text-red-600!">The notecard this notice used, {{ $notice->noticeNotecard->name }}, is missing from the notecard server. Choose another.</flux:text>
                    @endif
                </div>
            </flux:card>

            <div class="flex flex-wrap items-center justify-between gap-3">
                @if ($notice && ! $this->isExpiredLevel())
                    <flux:modal.trigger name="notice-delete">
                        <flux:button variant="ghost" icon="trash" class="text-red-600!">Remove notice</flux:button>
                    </flux:modal.trigger>
                @else
                    <span></span>
                @endif
                <div class="flex gap-2">
                    <flux:button :href="route('notices.index')" variant="ghost" wire:navigate>Back</flux:button>
                    <flux:button type="submit" variant="primary">{{ $notice ? 'Save notice' : 'Create notice' }}</flux:button>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-6 lg:sticky lg:top-6 lg:self-start">
            <flux:card>
                <x-ui.swap-tags target="notice-message" />
            </flux:card>
            <flux:card class="flex flex-col gap-3">
                <flux:heading size="sm">Preview</flux:heading>
                <div class="rounded-lg bg-zinc-50 p-3 text-sm whitespace-pre-line dark:bg-zinc-800/60">{{ $previewIm !== '' ? $previewIm : 'The message preview shows here.' }}</div>
            </flux:card>
        </div>
    </form>

    @if ($notice && ! $this->isExpiredLevel())
        <flux:modal name="notice-delete" class="md:w-md">
            <form wire:submit="delete" class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">Remove {{ $notice->name }}?</flux:heading>
                    <flux:text class="mt-2">
                        Choose where its {{ $clientCount }} {{ str('client')->plural($clientCount) }} move to.
                        @if ($optOutCount > 0)
                            {{ $optOutCount }} {{ str('client')->plural($optOutCount) }} opted out of this notice; those opt-outs are removed with it.
                        @endif
                    </flux:text>
                </div>
                <flux:select wire:model="transferTo" label="Move clients to" placeholder="Choose a notice level…">
                    @foreach ($others as $other)
                        <flux:select.option :value="$other->id">{{ $other->name }} ({{ \App\Livewire\Notices\Index::when($other) }})</flux:select.option>
                    @endforeach
                </flux:select>
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                    <flux:button type="submit" variant="danger">Remove notice</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</div>
