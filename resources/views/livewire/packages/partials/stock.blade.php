<div class="flex gap-1">
    <flux:badge size="sm" :color="$ready > 0 ? 'emerald' : 'red'" title="Ready to sell">{{ $ready }} ready</flux:badge>
    <flux:badge size="sm" color="sky" title="Rented">{{ $sold }} sold</flux:badge>
</div>
