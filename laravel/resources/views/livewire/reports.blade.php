@php
    $change = fn (int $now, int $before) => $before > 0 ? round(($now - $before) / $before * 100, 1) : null;
    $peak = max(1, $months->max(fn ($m) => max(0, $m['new']) + max(0, $m['renewal']) + max(0, $m['other'])));
    $segments = ['new' => ['New rentals', 'bg-emerald-500'], 'renewal' => ['Renewals', 'bg-sky-500'], 'other' => ['Credits & marketplace', 'bg-violet-500']];
@endphp

<div class="mx-auto flex w-full max-w-7xl flex-col gap-6" wire:poll.300s>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Reports</flux:heading>
            <flux:text class="mt-1">Sales by month, compared with the year before. Pick a month for its weekly breakdown.</flux:text>
        </div>
        <div class="w-36">
            <flux:select wire:model.live="year" label="Year">
                @foreach ($years as $option)
                    <flux:select.option :value="$option">{{ $option }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php($totalChange = $change($totals['total'], $previousTotals['total']))
        <x-ui.stat-card label="Total {{ $year }}" :value="'L$ '.number_format($totals['total'])" icon="banknotes" tone="sky"
            :hint="$totalChange === null ? 'No sales the year before' : ($totalChange >= 0 ? '+' : '').$totalChange.'% on '.($year - 1)" />
        <x-ui.stat-card label="New rentals" :value="'L$ '.number_format($totals['new'])" icon="user-plus" tone="emerald"
            :hint="number_format($totals['new_count']).' sales'" />
        <x-ui.stat-card label="Renewals" :value="'L$ '.number_format($totals['renewal'])" icon="arrow-path" tone="sky"
            :hint="number_format($totals['renewal_count']).' renewals'" />
        <x-ui.stat-card label="Credits & marketplace" :value="'L$ '.number_format($totals['other'])" icon="ticket" tone="zinc"
            hint="Credit top-ups less credits spent" />
    </div>

    <flux:card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:heading size="lg">{{ $year }} by month</flux:heading>
            <div class="flex flex-wrap gap-4 text-xs text-zinc-500">
                @foreach ($segments as [$label, $color])
                    <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm {{ $color }}"></span>{{ $label }}</span>
                @endforeach
            </div>
        </div>
        <div class="mt-6 grid h-56 grid-cols-12 items-end gap-2 sm:gap-3">
            @foreach ($months as $number => $data)
                <button type="button" wire:click="$set('month', {{ $number }})" wire:key="bar-{{ $number }}"
                    class="group flex h-full flex-col items-center justify-end gap-2"
                    title="{{ $data['label'] }}: L$ {{ number_format($data['total']) }} ({{ $data['count'] }} transactions)">
                    <div class="flex w-full flex-col-reverse overflow-hidden rounded-md transition group-hover:opacity-80 {{ $month === $number ? 'ring-2 ring-accent ring-offset-2 ring-offset-white dark:ring-offset-zinc-900' : '' }}"
                         style="height: {{ max(2, (max(0, $data['new']) + max(0, $data['renewal']) + max(0, $data['other'])) / $peak * 100) }}%">
                        @foreach ($segments as $key => [$label, $color])
                            @if ($data[$key] > 0)
                                <div class="{{ $color }}" style="flex: {{ $data[$key] }} 1 0%"></div>
                            @endif
                        @endforeach
                        @if ($data['total'] <= 0)
                            <div class="h-full bg-zinc-200 dark:bg-zinc-700"></div>
                        @endif
                    </div>
                    <span class="text-xs {{ $month === $number ? 'font-semibold text-zinc-900 dark:text-white' : 'text-zinc-500' }}">{{ $data['label'] }}</span>
                </button>
            @endforeach
        </div>
    </flux:card>

    <div class="grid gap-6 lg:grid-cols-3">
        <flux:card class="p-0 lg:col-span-2">
            <div class="flex items-center justify-between px-6 pt-5 pb-3">
                @if ($weeks)
                    <flux:heading size="lg">{{ \Carbon\Carbon::create($year, $month)->format('F Y') }} by week</flux:heading>
                    <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="$set('month', null)">Whole year</flux:button>
                @else
                    <flux:heading size="lg">{{ $year }} months</flux:heading>
                @endif
            </div>
            <div class="px-6 pb-2">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ $weeks ? 'Week' : 'Month' }}</flux:table.column>
                        <flux:table.column align="end">New</flux:table.column>
                        <flux:table.column align="end">Renewals</flux:table.column>
                        <flux:table.column align="end">Transactions</flux:table.column>
                        <flux:table.column align="end">Total</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($weeks ?? $months as $key => $row)
                            <flux:table.row :key="$key">
                                <flux:table.cell>
                                    @if ($weeks)
                                        {{ $row['label'] }}
                                    @else
                                        <button type="button" wire:click="$set('month', {{ $key }})" class="font-medium hover:underline">{{ $row['label'] }}</button>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell align="end" class="tabular-nums">{{ $row['new_count'] }} · L$ {{ number_format($row['new']) }}</flux:table.cell>
                                <flux:table.cell align="end" class="tabular-nums">{{ $row['renewal_count'] }} · L$ {{ number_format($row['renewal']) }}</flux:table.cell>
                                <flux:table.cell align="end" class="tabular-nums">{{ $row['count'] }}</flux:table.cell>
                                <flux:table.cell align="end" class="font-medium tabular-nums">L$ {{ number_format($row['total']) }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>

        <flux:card class="p-0">
            <div class="px-6 pt-5 pb-3">
                <flux:heading size="lg">All years</flux:heading>
            </div>
            <div class="px-6 pb-2">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Year</flux:table.column>
                        <flux:table.column align="end">Transactions</flux:table.column>
                        <flux:table.column align="end">Total</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($yearTotals as $row)
                            <flux:table.row :key="$row['year']">
                                <flux:table.cell>
                                    <button type="button" wire:click="$set('year', {{ $row['year'] }})" class="font-medium hover:underline">{{ $row['year'] }}</button>
                                </flux:table.cell>
                                <flux:table.cell align="end" class="tabular-nums">{{ number_format($row['count']) }}</flux:table.cell>
                                <flux:table.cell align="end" class="font-medium tabular-nums">L$ {{ number_format($row['total']) }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="3" class="py-6 text-center text-zinc-500">No sales yet.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    </div>
</div>
