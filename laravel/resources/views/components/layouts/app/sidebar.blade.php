@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="mr-5 flex items-center space-x-2" wire:navigate>
                <x-app-logo />
            </a>

            <form action="{{ route('search') }}" method="get" role="search">
                <flux:input name="q" :value="request()->routeIs('search') ? request('q') : null" icon="magnifying-glass" placeholder="Search clients, streams…" aria-label="Search" size="sm" />
            </form>

            <flux:navlist variant="outline">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Dashboard</flux:navlist.item>

                @foreach (config('navigation') as $group => $items)
                    <flux:navlist.group class="mt-4 grid">
                        <div class="px-1 py-2 text-xs leading-none text-zinc-400">{{ $group }}</div>
                        @foreach ($items as $item)
                            @if (! ($item['owner'] ?? false) || auth()->user()->can('owner'))
                                <flux:navlist.item
                                    :icon="$item['icon']"
                                    :href="route($item['route'])"
                                    :current="request()->routeIs(str($item['route'])->beforeLast('.').'.*')"
                                    wire:navigate
                                >{{ $item['label'] }}</flux:navlist.item>
                            @endif
                        @endforeach
                    </flux:navlist.group>
                @endforeach
            </flux:navlist>

            <flux:spacer />

            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->username"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-up-down"
                />

                <flux:menu class="w-[220px]">
                    <x-user-menu-header />

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.password')" icon="cog" wire:navigate>Account settings</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile header -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <x-user-menu-header />

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.password')" icon="cog" wire:navigate>Account settings</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast />
        @endpersist

        @fluxScripts
    </body>
</html>
