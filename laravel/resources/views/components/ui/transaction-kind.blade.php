@props(['transaction'])

@php([$label, $color] = \App\Models\Transaction::KINDS[$transaction->kind()])

<flux:badge size="sm" :color="$color" inset="top bottom" :title="$transaction->notes ?? $transaction->sl_transaction_uuid">{{ $label }}</flux:badge>
