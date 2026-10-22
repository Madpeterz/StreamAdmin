@props(['label', 'used', 'assigned', 'capacity'])

{{-- in use (solid) over assigned (faint), both as % of the server's capacity --}}
@php
    $tone = match (true) {
        $assigned > 100 => 'bg-red-500',
        $assigned > 80 => 'bg-amber-500',
        default => 'bg-accent',
    };
@endphp

<div {{ $attributes }}>
    <div class="flex items-baseline justify-between gap-2 text-xs">
        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
        <span class="tabular-nums text-zinc-500">{{ $used }}% in use · {{ $assigned }}% assigned of {{ $capacity }}</span>
    </div>
    <div class="relative mt-1.5 h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
        <div class="absolute inset-y-0 left-0 rounded-full {{ $tone }} opacity-30" style="width: {{ min(100, $assigned) }}%"></div>
        <div class="absolute inset-y-0 left-0 rounded-full {{ $tone }}" style="width: {{ min(100, $used) }}%"></div>
    </div>
</div>
