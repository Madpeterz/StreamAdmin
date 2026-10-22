<?php

namespace Tests\Feature\Catalog;

use App\Livewire\Packages\Edit;
use App\Models\Package;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class PackagesTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->staff());
    }

    private function filledForm()
    {
        return Livewire::test(Edit::class)
            ->set('name', 'Gold package')->set('cost', 900)->set('days', 30)->set('listener_limit', 100)->set('bitrate', 192)
            ->set('autodj', true)->set('autodj_size', 5)
            ->set('texture_instock_small', (string) Str::uuid())->set('texture_instock_selected', (string) Str::uuid())
            ->set('texture_soldout', (string) Str::uuid());
    }

    public function test_list_shows_stock(): void
    {
        $this->rental();

        $this->get('/packages')->assertOk()->assertSee('Test package')->assertSee('1 sold');
    }

    public function test_create_package(): void
    {
        $this->filledForm()->call('save')->assertHasNoErrors();

        $package = Package::where('name', 'Gold package')->firstOrFail();
        $this->assertSame(8, strlen($package->package_uid));
        $this->assertSame(100, $package->listeners);
        $this->assertSame('5', $package->autodj_size);
    }

    public function test_validation_follows_2_0_rules(): void
    {
        $this->filledForm()->set('name', 'Gold')->set('bitrate', 32)->set('texture_soldout', 'nope')->set('autodj_size', null)
            ->call('save')->assertHasErrors(['name', 'bitrate', 'texture_soldout', 'autodj_size']);
    }

    public function test_package_in_use_cannot_be_removed(): void
    {
        $package = $this->rental()->package;

        Livewire::test(Edit::class, ['package' => $package])->call('delete')->assertNoRedirect();
        $this->assertNotNull(Package::find($package->id));
    }

    public function test_unused_package_is_removed_and_transactions_unlinked(): void
    {
        $this->filledForm()->call('save');
        $package = Package::where('name', 'Gold package')->firstOrFail();
        $transaction = Transaction::create([
            'avatar_id' => $this->avatar()->id, 'package_id' => $package->id, 'amount' => 900,
            'unixtime' => time(), 'transaction_uid' => Str::random(8),
        ]);

        Livewire::test(Edit::class, ['package' => $package])->call('delete')->assertRedirect(route('packages.index'));

        $this->assertNull(Package::find($package->id));
        $this->assertNull($transaction->refresh()->package_id);
    }
}
