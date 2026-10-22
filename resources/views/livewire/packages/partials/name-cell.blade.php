<div class="font-medium text-zinc-900 dark:text-white">{{ $package->name }}</div>
<div class="flex items-center gap-1" x-data="{ copied: false }">
    <span class="font-mono text-xs text-zinc-500">{{ $package->package_uid }}</span>
    {{-- the row is a link: stop the click here so copying does not open the package --}}
    <button type="button" x-on:click.stop.prevent="navigator.clipboard.writeText('{{ $package->package_uid }}'); copied = true; setTimeout(() => copied = false, 1500)"
        class="inline-flex items-center rounded p-0.5 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200" x-bind:title="copied ? 'Copied' : 'Copy package UID'" aria-label="Copy package UID">
        <flux:icon.clipboard-document class="size-3.5" x-show="! copied" />
        <flux:icon.check class="size-3.5 text-emerald-500" x-show="copied" x-cloak />
    </button>
</div>
