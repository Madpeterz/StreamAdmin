@if ($stream->rental_id !== null)
    <flux:badge size="sm" color="sky" inset="top bottom">Sold</flux:badge>
@elseif ($stream->need_work)
    <flux:badge size="sm" color="amber" inset="top bottom">Needs work</flux:badge>
@else
    <flux:badge size="sm" color="emerald" inset="top bottom">Ready</flux:badge>
@endif
