{{-- SA7 mark from 2.0.x on a dark tile: the light blue logo needs a dark backdrop in light mode too --}}
<div class="flex h-9 shrink-0 items-center rounded-lg bg-zinc-900 px-1.5 ring-1 ring-black/5 dark:ring-white/10">
    <img src="{{ asset('logo.png') }}?v={{ config('app.version') }}" alt="" class="h-6 w-auto" />
</div>
<div class="ml-1 grid flex-1 text-left text-sm">
    <span class="mb-0.5 truncate leading-none font-semibold">{{ config('app.name', 'StreamAdmin') }}</span>
</div>
