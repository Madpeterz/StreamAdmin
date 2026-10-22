<flux:button size="xs" variant="ghost" icon="trash" class="text-zinc-400 hover:text-red-600!"
    wire:click="remove({{ $transaction->id }})"
    wire:confirm="Remove transaction {{ $transaction->transaction_uid }} (L$ {{ number_format($transaction->amount) }})? This does not change any rental's time."
    tooltip="Remove transaction" />
