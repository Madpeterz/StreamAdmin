<?php

namespace App\Livewire;

use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Sales reports: a year by month (with the previous year for comparison), and a month by week
 * (port of the 2.0.x Reports breakdowns).
 */
#[Title('Reports')]
class Reports extends Component
{
    #[Url]
    public ?int $year = null;

    #[Url]
    public ?int $month = null;

    public function mount(): void
    {
        $this->year = $this->clampYear($this->year ?? (int) now()->format('Y'));
        if ($this->month !== null && ($this->month < 1 || $this->month > 12)) {
            $this->month = null;
        }
    }

    public function updatedYear(): void
    {
        $this->year = $this->clampYear((int) $this->year);
    }

    public function render()
    {
        $months = $this->byMonth($this->year);
        $previous = $this->byMonth($this->year - 1);

        return view('livewire.reports', [
            'years' => $this->years(),
            'months' => $months,
            'totals' => $this->totals($months),
            'previousTotals' => $this->totals($previous),
            'weeks' => $this->month ? $this->byWeek($this->year, $this->month) : null,
            'yearTotals' => $this->yearTotals(),
        ]);
    }

    /** @return Collection<int, array{label: string, new: int, renewal: int, other: int, count: int, total: int}> */
    private function byMonth(int $year): Collection
    {
        $start = CarbonImmutable::create($year, 1, 1, 0, 0, 0, config('app.timezone'));
        $rows = $this->transactionsBetween($start, $start->endOfYear());

        return collect(range(1, 12))->mapWithKeys(fn (int $month) => [
            $month => $this->summarise($rows->filter(fn (Transaction $t) => (int) $t->unixtime->format('n') === $month))
                + ['label' => $start->setMonth($month)->format('M')],
        ]);
    }

    /** Weeks as in 2.0.x: days 1-7, 8-14, 15-21, 22-28 and the rest of the month. */
    private function byWeek(int $year, int $month): Collection
    {
        $start = CarbonImmutable::create($year, $month, 1, 0, 0, 0, config('app.timezone'));
        $rows = $this->transactionsBetween($start, $start->endOfMonth());
        $weeks = [[1, 7], [8, 14], [15, 21], [22, 28], [29, 31]];

        return collect($weeks)
            ->filter(fn ($days) => $days[0] <= $start->daysInMonth)
            ->map(fn ($days, $i) => $this->summarise($rows->filter(fn (Transaction $t) => (int) $t->unixtime->format('j') >= $days[0] && (int) $t->unixtime->format('j') <= $days[1]))
                + ['label' => 'Week '.($i + 1).' · '.$days[0].'–'.min($days[1], $start->daysInMonth)]);
    }

    /** @return Collection<int, Transaction> */
    private function transactionsBetween(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return Transaction::whereBetween('unixtime', [$from->getTimestamp(), $to->getTimestamp()])
            ->get(['unixtime', 'amount', 'renew', 'via_marketplace', 'from_credits']);
    }

    /** @param Collection<int, Transaction> $rows */
    private function summarise(Collection $rows): array
    {
        $rentals = $rows->reject(fn (Transaction $t) => $t->via_marketplace || $t->from_credits);

        return [
            'new' => (int) $rentals->where('renew', false)->sum('amount'),
            'renewal' => (int) $rentals->where('renew', true)->sum('amount'),
            'other' => (int) $rows->filter(fn (Transaction $t) => $t->via_marketplace || $t->from_credits)->sum('amount'),
            'new_count' => $rentals->where('renew', false)->count(),
            'renewal_count' => $rentals->where('renew', true)->count(),
            'count' => $rows->count(),
            'total' => (int) $rows->sum('amount'),
        ];
    }

    private function totals(Collection $months): array
    {
        return collect(['new', 'renewal', 'other', 'new_count', 'renewal_count', 'count', 'total'])
            ->mapWithKeys(fn ($key) => [$key => $months->sum($key)])->all();
    }

    /** Totals for every year with sales, newest first (the 2.0.x "compare years" report). */
    private function yearTotals(): Collection
    {
        return Transaction::query()
            ->selectRaw('YEAR(FROM_UNIXTIME(unixtime)) AS year, COUNT(*) AS count, SUM(amount) AS total')
            ->groupByRaw('YEAR(FROM_UNIXTIME(unixtime))')
            ->orderByDesc('year')
            ->toBase()
            ->get()
            ->map(fn ($row) => ['year' => (int) $row->year, 'count' => (int) $row->count, 'total' => (int) $row->total]);
    }

    /** @return list<int> */
    private function years(): array
    {
        $first = (int) (Transaction::min('unixtime') ?? time());
        $from = max(2013, (int) date('Y', $first));

        return range((int) now()->format('Y'), min($from, (int) now()->format('Y')));
    }

    private function clampYear(int $year): int
    {
        return max(2013, min($year, (int) now()->format('Y')));
    }
}
