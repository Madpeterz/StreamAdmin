@props(['label', 'value', 'icon', 'tone' => 'zinc', 'href' => null, 'hint' => null])

@php
    $tones = [
        'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        'amber' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
        'red' => 'bg-red-500/10 text-red-600 dark:text-red-400',
        'sky' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
        'zinc' => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-300',
    ];
@endphp

<a @if ($href) href="{{ $href }}" wire:navigate @endif
   {{ $attributes->class(['group block rounded-xl border border-zinc-200 bg-white p-5 shadow-xs transition dark:border-zinc-700 dark:bg-zinc-900', 'hover:border-zinc-300 hover:shadow-sm dark:hover:border-zinc-600' => $href]) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <flux:text class="text-sm">{{ $label }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums tracking-tight text-zinc-900 dark:text-white">{{ $value }}</div>
            @if ($hint)
                <flux:text class="mt-1 text-xs">{{ $hint }}</flux:text>
            @endif
        </div>
        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg {{ $tones[$tone] ?? $tones['zinc'] }}">
            <flux:icon :name="$icon" variant="outline" class="size-5" />
        </div>
    </div>
</a>
