@props(['target'])

{{-- swap tag reference: clicking a tag inserts it at the cursor of the last focused [data-swaps] box (or $target) --}}
<div
    x-data="{
        box: null,
        insert(tag) {
            const el = this.box ?? document.getElementById(@js($target));
            if (! el) return;
            const start = el.selectionStart ?? el.value.length;
            el.setRangeText(tag, start, el.selectionEnd ?? start, 'end');
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.focus();
        },
    }"
    x-on:focusin.window="if ($event.target.matches('[data-swaps]')) box = $event.target"
    {{ $attributes->class('flex flex-col gap-3') }}
>
    <div>
        <flux:heading size="sm">Swap tags</flux:heading>
        <flux:text class="text-sm">Click a tag to add it to the message. Each one is replaced with the client's own details when sent; [[NL]] starts a new line.</flux:text>
    </div>
    <div class="flex flex-wrap gap-1.5">
        @foreach (\App\Support\Swaps::examples() as $tag => $example)
            <button
                type="button"
                x-on:click="insert(@js('[['.$tag.']]'))"
                title="{{ $tag === 'NL' ? 'New line' : 'e.g. '.$example }}"
                class="rounded-md border border-zinc-200 bg-zinc-50 px-1.5 py-0.5 font-mono text-[11px] text-zinc-700 hover:border-accent hover:text-accent-content dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
            >{{ $tag }}</button>
        @endforeach
    </div>
</div>
