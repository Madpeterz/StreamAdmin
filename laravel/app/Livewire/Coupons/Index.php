<?php

namespace App\Livewire\Coupons;

use App\Models\MarketplaceCoupon;
use App\Models\SlConfig;
use App\Services\SalesService;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Marketplace listings that top up an avatar's credits when bought (ANS callbacks).
 * Everyone can see them; only owners can change them (as in 2.0.x).
 */
#[Title('Coupons')]
class Index extends Component
{
    public ?int $editingId = null;

    public ?int $listing_id = null;

    public int $cost = 100;

    public int $credit = 100;

    public ?int $deletingId = null;

    public function resetForm(): void
    {
        Gate::authorize('owner');
        $this->resetValidation();
        $this->reset('editingId', 'listing_id', 'cost', 'credit');
    }

    public function edit(MarketplaceCoupon $coupon): void
    {
        $this->resetForm();
        $this->editingId = $coupon->id;
        $this->fill($coupon->only(['listing_id', 'cost', 'credit']));
        Flux::modal('coupon-form')->show();
    }

    public function save(SalesService $sales): void
    {
        Gate::authorize('owner');
        $data = $this->validate([
            'listing_id' => ['required', 'integer', 'min:1', 'max:9999999999', Rule::unique('marketplace_coupons', 'listing_id')->ignore($this->editingId)],
            'cost' => ['required', 'integer', 'min:1', 'max:99999'],
            'credit' => ['required', 'integer', 'min:1', 'max:99999'],
        ], ['listing_id.unique' => 'That listing is already set up as a coupon.'], ['listing_id' => 'listing ID']);
        $sales->saveCoupon($this->editingId ? MarketplaceCoupon::findOrFail($this->editingId) : null, $data);
        Flux::modal('coupon-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? 'Coupon updated.' : 'Coupon added.');
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('owner');
        $this->deletingId = $id;
        Flux::modal('coupon-delete')->show();
    }

    public function delete(SalesService $sales): void
    {
        Gate::authorize('owner');
        $sales->deleteCoupon(MarketplaceCoupon::findOrFail($this->deletingId));
        $this->deletingId = null;
        Flux::modal('coupon-delete')->close();
        Flux::toast(variant: 'success', text: 'Coupon removed.');
    }

    public function render()
    {
        return view('livewire.coupons.index', [
            'coupons' => MarketplaceCoupon::orderBy('cost')->get(),
            'enabled' => (bool) SlConfig::find(1)?->enable_coupons,
            'ansUrl' => rtrim(config('app.url'), '/').'/ans.php',
        ]);
    }
}
