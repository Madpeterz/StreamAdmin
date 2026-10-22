@props(['rental'])

@switch($rental->status())
    @case('expired')
        <flux:badge color="red" size="sm" inset="top bottom">Expired</flux:badge>
        @break
    @case('expiring')
        <flux:badge color="amber" size="sm" inset="top bottom">Expires soon</flux:badge>
        @break
    @default
        <flux:badge color="emerald" size="sm" inset="top bottom">Active</flux:badge>
@endswitch
