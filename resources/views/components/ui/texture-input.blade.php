@props(['model', 'label', 'description' => null])

{{-- Second Life texture UUID with a live preview from the SL picture service --}}
<div x-data="{ uuid: $wire.entangle('{{ $model }}') }" class="flex gap-4">
    <div class="relative flex h-[72px] w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:icon.photo class="size-6 text-zinc-400" />
        <template x-if="/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i.test(uuid || '')">
            <img :src="'https://picture-service.secondlife.com/' + uuid + '/256x192.jpg'" alt=""
                 class="absolute inset-0 size-full object-cover" x-on:error="$el.remove()">
        </template>
    </div>
    <div class="min-w-0 flex-1">
        <flux:input :wire:model.live.debounce.400ms="$model" :label="$label" :description:trailing="$description" class="font-mono" placeholder="00000000-0000-0000-0000-000000000000" />
    </div>
</div>
