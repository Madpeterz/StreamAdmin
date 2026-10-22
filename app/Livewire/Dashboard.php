<?php

namespace App\Livewire;

use App\Models\Rental;
use App\Models\Server;
use App\Models\SlConfig;
use App\Models\Stream;
use App\Models\Transaction;
use App\Services\ObjectHealth;
use App\Support\PaymentKey;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The dashboard is made of islands that each refresh on their own timer (see the view), so every
 * block of data is a computed property: only the blocks an island shows are queried when it polls.
 */
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard');
    }

    #[Computed]
    public function config(): ?SlConfig
    {
        return SlConfig::find(1);
    }

    /** @return array{ok: bool, status: string, expires: ?int} */
    #[Computed]
    public function supportKey(): array
    {
        return PaymentKey::check($this->config?->payment_key);
    }

    /** @return array{active: int, expiring: int, expired: int} */
    #[Computed]
    public function clients(): array
    {
        return [
            'active' => Rental::active()->count(),
            'expiring' => Rental::expiringSoon()->count(),
            'expired' => Rental::expired()->count(),
        ];
    }

    #[Computed]
    public function revenue(): int
    {
        return (int) Transaction::where('unixtime', '>=', now()->subDays(30)->getTimestamp())->sum('amount');
    }

    /** @return array{ready: int, need_work: int, sold: int} */
    #[Computed]
    public function streams(): array
    {
        return $this->streamCounts(Stream::query()->toBase());
    }

    /** @return Collection<int, array{server: Server, counts: array{ready: int, need_work: int, sold: int}}> */
    #[Computed]
    public function servers(): Collection
    {
        return Server::orderBy('domain')->get()->map(fn (Server $server) => [
            'server' => $server,
            'counts' => $this->streamCounts(Stream::where('server_id', $server->id)->toBase()),
        ]);
    }

    /** @return Collection<int, Rental> */
    #[Computed]
    public function expiringRentals(): Collection
    {
        return Rental::with(['avatar', 'stream.server', 'package'])
            ->where('expire_unixtime', '>', time())
            ->orderBy('expire_unixtime')
            ->limit(6)
            ->get();
    }

    /** @return Collection<int, Transaction> */
    #[Computed]
    public function recentTransactions(): Collection
    {
        return Transaction::with(['avatar', 'package'])->latest('unixtime')->limit(6)->get();
    }

    #[Computed]
    public function systemObjects(): Collection
    {
        return app(ObjectHealth::class)->serverObjects();
    }

    #[Computed]
    public function vendorHealth(): ?float
    {
        return app(ObjectHealth::class)->vendorPercent();
    }

    /** @return array{ready: int, need_work: int, sold: int} */
    private function streamCounts($query): array
    {
        $row = $query->selectRaw('SUM(rental_id IS NOT NULL) AS sold')
            ->selectRaw('SUM(rental_id IS NULL AND need_work = 0) AS ready')
            ->selectRaw('SUM(rental_id IS NULL AND need_work = 1) AS need_work')
            ->first();

        return ['ready' => (int) $row->ready, 'need_work' => (int) $row->need_work, 'sold' => (int) $row->sold];
    }
}
