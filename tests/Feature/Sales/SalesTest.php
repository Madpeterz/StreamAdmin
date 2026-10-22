<?php

namespace Tests\Feature\Sales;

use App\Livewire\Coupons;
use App\Livewire\Reports;
use App\Livewire\Resellers;
use App\Livewire\Transactions;
use App\Livewire\TreeVendors;
use App\Models\MarketplaceCoupon;
use App\Models\Reseller;
use App\Models\Transaction;
use App\Models\TreeVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->staff());
    }

    private function sale(array $attributes = []): Transaction
    {
        return Transaction::create($attributes + [
            'avatar_id' => $this->avatar('Paying Client')->id,
            'amount' => 500,
            'unixtime' => time(),
            'transaction_uid' => Str::random(8),
        ]);
    }

    public function test_pages_render(): void
    {
        $this->sale();
        foreach (['/transactions', '/reports', '/resellers', '/tree-vendors', '/coupons'] as $page) {
            $this->get($page)->assertOk();
        }
        $this->get('/transactions')->assertSee('Paying Client')->assertSee('New rental');
    }

    public function test_transaction_kinds(): void
    {
        $payer = $this->avatar();
        $this->assertSame('renewal', $this->sale(['renew' => true])->kind());
        $this->assertSame('hud', $this->sale(['renew' => true, 'via_hud' => true])->kind());
        $this->assertSame('proxy', $this->sale(['renew' => true, 'avatar_id' => $payer->id, 'target_avatar_id' => $this->avatar()->id])->kind());
        $this->assertSame('marketplace', $this->sale(['via_marketplace' => true])->kind());
    }

    public function test_only_owners_remove_transactions(): void
    {
        $sale = $this->sale();

        $this->actingAs($this->staff(owner: false));
        Livewire::test(Transactions\Index::class)->call('remove', $sale->id)->assertForbidden();

        $this->actingAs($this->staff(owner: true));
        Livewire::test(Transactions\Index::class)->call('remove', $sale->id);
        $this->assertNull(Transaction::find($sale->id));
    }

    public function test_reports_split_new_and_renewals_by_month(): void
    {
        $this->sale(['amount' => 300]);
        $this->sale(['amount' => 200, 'renew' => true]);

        Livewire::test(Reports::class)
            ->assertSee('L$ 500')
            ->set('month', (int) now()->format('n'))
            ->assertSee('by week');
    }

    public function test_reseller_rate_and_removal_moves_sales_to_the_owner(): void
    {
        $reseller = Reseller::create(['avatar_id' => $this->avatar('Shop Owner')->id, 'allowed' => false, 'rate' => 10]);
        $sale = $this->sale(['reseller_id' => $reseller->id]);
        $ownerReseller = Reseller::firstOrCreate(['avatar_id' => 1], ['allowed' => true, 'rate' => 100]);

        Livewire::test(Resellers\Index::class)->call('edit', $reseller->id)->set('allowed', true)->set('rate', 30)->call('save')->assertHasNoErrors();
        $this->assertTrue($reseller->refresh()->allowed);
        $this->assertSame(30, $reseller->rate);

        Livewire::test(Resellers\Index::class)->call('confirmDelete', $reseller->id)->call('delete');
        $this->assertNull(Reseller::find($reseller->id));
        $this->assertSame($ownerReseller->id, $sale->refresh()->reseller_id);
    }

    public function test_tree_vendor_packages(): void
    {
        $package = $this->rental()->package;

        Livewire::test(TreeVendors\Index::class)->set('name', 'Front door tree')->call('create')->assertHasNoErrors();
        $vendor = TreeVendor::where('name', 'Front door tree')->firstOrFail();

        Livewire::test(TreeVendors\Edit::class, ['vendor' => $vendor])
            ->set('addPackageId', $package->id)->call('addPackage')->assertHasNoErrors()
            ->set('addPackageId', $package->id)->call('addPackage')->assertHasErrors('addPackageId');
        $this->assertTrue($vendor->packages()->whereKey($package->id)->exists());

        Livewire::test(TreeVendors\Edit::class, ['vendor' => $vendor])->call('delete');
        $this->assertNull(TreeVendor::find($vendor->id));
        $this->assertNotNull($package->fresh());
    }

    public function test_only_owners_manage_coupons(): void
    {
        $this->actingAs($this->staff(owner: false));
        Livewire::test(Coupons\Index::class)->set('listing_id', 12345)->call('save')->assertForbidden();

        $this->actingAs($this->staff(owner: true));
        Livewire::test(Coupons\Index::class)->call('resetForm')
            ->set('listing_id', 12345)->set('cost', 100)->set('credit', 120)->call('save')->assertHasNoErrors();
        $this->assertSame(120, MarketplaceCoupon::where('listing_id', 12345)->value('credit'));

        Livewire::test(Coupons\Index::class)->call('resetForm')->set('listing_id', 12345)->call('save')->assertHasErrors('listing_id');
    }
}
