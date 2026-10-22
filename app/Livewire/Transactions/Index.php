<?php

namespace App\Livewire\Transactions;

use App\Livewire\Tables\StyledTable;
use App\Models\Transaction;
use App\Services\SalesService;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class Index extends StyledTable
{
    protected $model = Transaction::class;

    protected string $pageTitle = 'Transactions';

    protected string $pageDescription = 'Payments for rentals and renewals, credit refunds and marketplace top-ups.';

    protected int $refreshSeconds = 30;

    public function mount(): void
    {
        // client pages link here with ?avatar=<avatar uid>
        if (is_string(request()->query('avatar'))) {
            $this->setSearch(request()->query('avatar'));
        }
    }

    public function configure(): void
    {
        $this->styleTable();
        $this->setPrimaryKey('id')
            ->setDefaultSort('unixtime', 'desc')
            ->setSearchPlaceholder('Search avatar, transaction UID or notes')
            ->setAdditionalSelects([
                'transactions.id', 'transactions.transaction_uid', 'transactions.avatar_id', 'transactions.target_avatar_id',
                'transactions.renew', 'transactions.via_hud', 'transactions.via_marketplace', 'transactions.from_credits',
                'transactions.notes', 'transactions.sl_transaction_uuid',
            ])
            ->setTrAttributes(fn () => [
                'default' => false,
                'class' => 'transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50',
            ]);
    }

    public function builder(): Builder
    {
        return Transaction::query()->with(['avatar', 'targetAvatar']);
    }

    public function columns(): array
    {
        return array_filter([
            Column::make('When', 'unixtime')
                ->sortable()
                ->format(fn ($value, Transaction $row) => '<div class="text-zinc-900 dark:text-white">'.e($row->unixtime?->toFormattedDateString()).'</div>'
                    .'<div class="text-xs text-zinc-500">'.e($row->unixtime?->format('H:i')).'</div>')
                ->html(),
            Column::make('Payer', 'avatar.avatar_name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, Transaction $row) => '<div class="font-medium text-zinc-900 dark:text-white">'.e($value ?? 'Unknown avatar').'</div>'
                    .($row->targetAvatar && $row->target_avatar_id !== $row->avatar_id
                        ? '<div class="text-xs text-zinc-500">for '.e($row->targetAvatar->avatar_name).'</div>' : ''))
                ->html(),
            Column::make('Type')
                ->label(fn (Transaction $row) => view('components.ui.transaction-kind', ['transaction' => $row]))
                ->html(),
            Column::make('Package', 'package.name')
                ->sortable()
                ->searchable()
                ->collapseOnMobile(),
            Column::make('Region', 'region.name')
                ->sortable()
                ->searchable()
                ->collapseOnTablet(),
            Column::make('UID', 'transaction_uid')
                ->searchable()
                ->format(fn ($value) => '<span class="font-mono text-xs text-zinc-500">'.e($value).'</span>')
                ->html()
                ->collapseOnTablet(),
            Column::make('Notes', 'notes')->searchable()->hideIf(true),
            Column::make('Amount', 'amount')
                ->sortable()
                ->format(fn ($value) => '<span class="font-medium tabular-nums '.((int) $value < 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white').'">L$ '.number_format((int) $value).'</span>')
                ->html(),
            Gate::allows('owner')
                ? Column::make('')
                    ->label(fn (Transaction $row) => view('livewire.transactions.partials.remove', ['transaction' => $row]))
                    ->html()
                : null,
        ]);
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Type', 'type')
                ->options(['' => 'Any type'] + collect(Transaction::KINDS)->map(fn ($kind) => $kind[0])->all())
                ->filter(fn (Builder $query, string $value) => match ($value) {
                    'marketplace' => $query->where('via_marketplace', true),
                    'credits' => $query->where('from_credits', true),
                    'hud' => $query->where('renew', true)->where('via_hud', true),
                    'proxy' => $query->where('renew', true)->whereNotNull('target_avatar_id')->whereColumn('target_avatar_id', '!=', 'transactions.avatar_id'),
                    'renewal' => $query->where('renew', true)->where('via_hud', false)->where('via_marketplace', false),
                    'new' => $query->where('renew', false)->where('via_marketplace', false)->where('from_credits', false),
                    default => $query,
                }),
            DateFilter::make('From', 'from')
                ->filter(fn (Builder $query, string $value) => $query->where('unixtime', '>=', strtotime($value.' 00:00:00'))),
            DateFilter::make('To', 'to')
                ->filter(fn (Builder $query, string $value) => $query->where('unixtime', '<=', strtotime($value.' 23:59:59'))),
        ];
    }

    public function remove(int $id, SalesService $sales): void
    {
        Gate::authorize('owner');
        $sales->deleteTransaction(Transaction::with('avatar')->findOrFail($id));
        Flux::toast(variant: 'success', text: 'Transaction removed. Rental times are unchanged.');
    }
}
