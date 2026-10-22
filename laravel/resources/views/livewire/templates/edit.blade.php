<div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('templates.index')" wire:navigate>Templates</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $template?->name ?? 'New template' }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div>
        <flux:heading size="xl" level="1">{{ $template?->name ?? 'New template' }}</flux:heading>
        <flux:text class="mt-1">
            @if ($template && $packages->isNotEmpty())
                Used by {{ $packages->pluck('name')->join(', ', ' and ') }}.
            @else
                Sent to a client when they rent a stream from a package using this template.
            @endif
        </flux:text>
    </div>

    <form wire:submit="save" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="flex flex-col gap-6">
            <flux:card class="flex flex-col gap-5">
                <flux:input wire:model="name" label="Name" class="max-w-sm" />

                <flux:field>
                    <div class="flex items-baseline justify-between">
                        <flux:label>IM message</flux:label>
                        <span @class(['text-xs tabular-nums', 'text-red-600' => mb_strlen($detail) > 800, 'text-zinc-500' => mb_strlen($detail) <= 800])>{{ mb_strlen($detail) }} / 800</span>
                    </div>
                    <flux:description>Sent by the in-world objects and, when IMs are on, the bot.</flux:description>
                    <flux:textarea id="template-detail" data-swaps wire:model.live.debounce.400ms="detail" rows="8" class="font-mono text-sm" />
                    <flux:error name="detail" />
                </flux:field>

                <flux:field>
                    <div class="flex items-baseline justify-between">
                        <flux:label>Notecard</flux:label>
                        <span @class(['text-xs tabular-nums', 'text-red-600' => mb_strlen($notecard_detail) > 5000, 'text-zinc-500' => mb_strlen($notecard_detail) <= 5000])>{{ mb_strlen($notecard_detail) }} / 5000</span>
                    </div>
                    <flux:description>Given as a notecard by the bot when notecards are on.</flux:description>
                    <flux:textarea data-swaps wire:model.live.debounce.400ms="notecard_detail" rows="14" class="font-mono text-sm" />
                    <flux:error name="notecard_detail" />
                </flux:field>
            </flux:card>

            <div class="flex flex-wrap items-center justify-between gap-3">
                @if ($template)
                    <flux:modal.trigger name="template-delete">
                        <flux:button variant="ghost" icon="trash" class="text-red-600!">Remove template</flux:button>
                    </flux:modal.trigger>
                @else
                    <span></span>
                @endif
                <div class="flex gap-2">
                    <flux:button :href="route('templates.index')" variant="ghost" wire:navigate>Back</flux:button>
                    <flux:button type="submit" variant="primary">{{ $template ? 'Save template' : 'Create template' }}</flux:button>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-6 lg:sticky lg:top-6 lg:self-start">
            <flux:card>
                <x-ui.swap-tags target="template-detail" />
            </flux:card>
            <flux:card class="flex flex-col gap-3">
                <flux:heading size="sm">Preview</flux:heading>
                <flux:text class="text-sm">With example values filled in.</flux:text>
                <div class="max-h-48 overflow-y-auto rounded-lg bg-zinc-50 p-3 text-sm whitespace-pre-line dark:bg-zinc-800/60">{{ $previewIm !== '' ? $previewIm : 'The IM message preview shows here.' }}</div>
                <div class="max-h-72 overflow-y-auto rounded-lg bg-zinc-50 p-3 font-mono text-xs whitespace-pre-line dark:bg-zinc-800/60">{{ $previewNotecard !== '' ? $previewNotecard : 'The notecard preview shows here.' }}</div>
            </flux:card>
        </div>
    </form>

    @if ($template)
        <flux:modal name="template-delete" class="md:w-md">
            <div class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">Remove {{ $template->name }}?</flux:heading>
                    <flux:text class="mt-2">
                        @if ($packages->isNotEmpty())
                            {{ $packages->count() }} {{ str('package')->plural($packages->count()) }} still use this template; move them to another template first.
                        @else
                            No packages use this template.
                        @endif
                    </flux:text>
                </div>
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                    <flux:button variant="danger" wire:click="delete" :disabled="$packages->isNotEmpty()">Remove template</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
