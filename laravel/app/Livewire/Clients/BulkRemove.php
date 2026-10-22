<?php

namespace App\Livewire\Clients;

use App\Models\DetailRequest;
use App\Models\Rental;
use App\Services\NoticeLevels;
use App\Services\RentalService;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Clear out expired rentals in one go (port of the 2.0.x Client/Bulkremove page): the streams go
 * back to "needs work". Clients with a details request waiting are not listed.
 */
#[Title('Remove expired clients')]
class BulkRemove extends Component
{
    /** @var list<string> rental UIDs ticked for removal */
    public array $selected = [];

    public function selectAll(): void
    {
        $this->selected = $this->rentals()->pluck('rental_uid')->all();
    }

    public function remove(RentalService $rentals): void
    {
        if ($this->selected === []) {
            Flux::toast(text: 'Tick the clients to remove first.');

            return;
        }
        $result = $rentals->purgeExpired($this->selected);
        $this->selected = [];
        Flux::toast(
            variant: 'success',
            text: "Removed {$result['removed']} ".str('rental')->plural($result['removed']).($result['skipped'] > 0 ? " and skipped {$result['skipped']}." : '.'),
        );
    }

    /** @return Collection<int, Rental> */
    private function rentals(): Collection
    {
        return Rental::with(['avatar', 'stream.server'])
            ->where('notice_id', NoticeLevels::EXPIRED)
            ->where('expire_unixtime', '<=', time())
            ->whereNotIn('id', DetailRequest::select('rental_id'))
            ->orderBy('expire_unixtime')
            ->get();
    }

    public function render()
    {
        return view('livewire.clients.bulk-remove', ['rentals' => $this->rentals()]);
    }
}
