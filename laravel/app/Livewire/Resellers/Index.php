<?php

namespace App\Livewire\Resellers;

use App\Models\Reseller;
use App\Models\SlConfig;
use App\Models\SlObject;
use App\Models\Transaction;
use App\Services\SalesService;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Title('Resellers')]
class Index extends Component
{
    public string $search = '';

    public ?int $editingId = null;

    public bool $allowed = false;

    public int $rate = 0;

    public ?int $deletingId = null;

    public function edit(Reseller $reseller): void
    {
        $this->resetValidation();
        $this->editingId = $reseller->id;
        $this->allowed = $reseller->allowed;
        $this->rate = $reseller->rate;
        Flux::modal('reseller-form')->show();
    }

    public function save(SalesService $sales): void
    {
        $data = $this->validate(['allowed' => ['boolean'], 'rate' => ['required', 'integer', 'min:1', 'max:100']]);
        $sales->updateReseller(Reseller::findOrFail($this->editingId), $data);
        Flux::modal('reseller-form')->close();
        Flux::toast(variant: 'success', text: 'Reseller updated.');
    }

    public function toggleAllowed(Reseller $reseller, SalesService $sales): void
    {
        $sales->updateReseller($reseller, ['allowed' => ! $reseller->allowed, 'rate' => max(1, $reseller->rate)]);
        Flux::toast(variant: 'success', text: $reseller->avatar?->avatar_name.($reseller->allowed ? ' can now use vendors.' : ' is no longer allowed.'));
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('reseller-delete')->show();
    }

    public function delete(SalesService $sales): void
    {
        try {
            $sales->deleteReseller(Reseller::with('avatar')->findOrFail($this->deletingId));
            Flux::toast(variant: 'success', text: 'Reseller removed. Their sales now count for the system owner.');
        } catch (RuntimeException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
        $this->deletingId = null;
        Flux::modal('reseller-delete')->close();
    }

    public function render()
    {
        $resellers = Reseller::with('avatar')
            ->when($this->search !== '', fn ($query) => $query->whereHas('avatar', fn ($avatar) => $avatar->where('avatar_name', 'like', '%'.$this->search.'%')))
            ->get()
            ->sortBy(fn (Reseller $r) => [! $r->allowed, $r->avatar?->avatar_name]);
        $sales = Transaction::selectRaw('reseller_id, COUNT(*) AS count, SUM(amount) AS total, MAX(unixtime) AS last')
            ->whereIn('reseller_id', $resellers->pluck('id'))
            ->groupBy('reseller_id')->toBase()->get()->keyBy('reseller_id');
        $vendors = SlObject::selectRaw('avatar_id, COUNT(*) AS count, MAX(last_seen) AS last_seen')
            ->whereIn('avatar_id', $resellers->pluck('avatar_id'))
            ->groupBy('avatar_id')->toBase()->get()->keyBy('avatar_id');

        return view('livewire.resellers.index', [
            'resellers' => $resellers,
            'sales' => $sales,
            'vendors' => $vendors,
            'ownerAvatarId' => SlConfig::find(1)?->owner_avatar_id,
            'editing' => $this->editingId ? $resellers->firstWhere('id', $this->editingId) : null,
        ]);
    }
}
