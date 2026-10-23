<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
        <x-build-info class="mt-12" />
    </flux:main>
</x-layouts.app.sidebar>
