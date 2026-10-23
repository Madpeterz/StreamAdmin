{{-- git tag and build date of this image, from the APP_VERSION and APP_BUILD_DATE build arguments --}}
<div {{ $attributes->class('text-center text-xs text-zinc-400 dark:text-zinc-500') }}>
    {{ config('app.name', 'StreamAdmin') }} {{ config('app.version') }}@if (config('app.build_date')) · built {{ config('app.build_date') }}@endif
</div>
