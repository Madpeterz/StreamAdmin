@props(['at', 'warnAfter' => 65, 'failAfter' => 240])

{{-- online indicator for in-world objects, thresholds match the 2.0.x dashboard --}}
@php
    $age = $at ? now()->getTimestamp() - $at->getTimestamp() : null;
    $state = match (true) {
        $age === null => 'missing',
        $age > $failAfter => 'down',
        $age > $warnAfter => 'late',
        default => 'ok',
    };
    $dot = ['ok' => 'bg-emerald-500', 'late' => 'bg-amber-500', 'down' => 'bg-red-500', 'missing' => 'bg-zinc-400'][$state];
@endphp

<span {{ $attributes->class('inline-flex shrink-0 items-center gap-2 whitespace-nowrap') }}>
    <span class="relative flex size-2.5">
        @if ($state === 'ok')
            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
        @endif
        <span class="relative inline-flex size-2.5 rounded-full {{ $dot }}"></span>
    </span>
    <span class="text-sm text-zinc-600 dark:text-zinc-300">
        {{ $at ? $at->diffForHumans() : 'Offline' }}
    </span>
</span>
