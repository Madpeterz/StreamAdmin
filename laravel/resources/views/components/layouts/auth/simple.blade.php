<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <div class="flex flex-col items-center gap-3 font-medium">
                    <span class="flex items-center justify-center rounded-2xl bg-zinc-900 px-4 py-3 shadow-sm ring-1 ring-black/5 dark:ring-white/10">
                        <img src="{{ asset('logo.png') }}" alt="" class="h-12 w-auto" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'StreamAdmin') }}</span>
                </div>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
