@props(['label', 'secret', 'username' => null])

{{-- credential that is never shown on screen, only copied (the secret lives in x-data: Blade
     directives are not compiled inside flux component attributes) --}}
<div x-data="{ copied: false, secret: @js($secret) }">
    <dt class="text-sm text-zinc-500">{{ $label }}</dt>
    <dd class="mt-1 flex items-center gap-2">
        @if ($username)
            <span class="font-mono text-sm">{{ $username }}</span>
            <span class="text-zinc-300 dark:text-zinc-600">/</span>
        @endif
        <span class="font-mono text-sm" aria-hidden="true">••••••••</span>
        <flux:button size="xs" variant="ghost" icon="clipboard"
            x-on:click="navigator.clipboard.writeText(secret); copied = true; setTimeout(() => copied = false, 1500)">
            <span x-text="copied ? 'Copied' : 'Copy'"></span>
        </flux:button>
    </dd>
</div>
