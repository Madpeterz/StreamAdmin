<?php

namespace Tests\Feature\Api;

use App\Api\ApiClass;
use App\Models\Package;
use App\Models\Stream;
use App\Models\TexturePack;
use App\Models\TreeVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesStreamAdminData;
use Tests\Support\SignsApiRequests;
use Tests\TestCase;

/** Stock, prices and textures for vendors, stock boards and tree vendors. */
class CatalogApiTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;
    use SignsApiRequests;

    private function freePackage(): Package
    {
        $rental = $this->rental();
        $rental->stream->update(['rental_id' => null]);
        $rental->delete();

        return Package::where('package_uid', 'pkgTest1')->sole();
    }

    public function test_package_status_follows_the_stock(): void
    {
        $package = $this->freePackage();

        $this->api('api/v3/packages/status', ['packageUid' => 'pkgTest1'])->assertOk()->assertJsonPath('data', [
            'inStock' => true, 'cost' => 700,
            'textures' => ['packageSmall' => $package->texture_instock_small, 'packageBig' => $package->texture_instock_selected, 'packageSoldOut' => $package->texture_soldout],
        ]);

        Stream::where('package_id', $package->id)->update(['need_work' => true]);
        $this->api('api/v3/packages/status', ['packageUid' => 'pkgTest1'])->assertJsonPath('data.inStock', false);
        $this->api('api/v3/packages/status', ['packageUid' => 'nope'])->assertStatus(404);
        $this->api('api/v3/packages/status', [])->assertStatus(422);
    }

    public function test_a_vendors_config_has_its_textures_and_the_resellers_rate(): void
    {
        $this->freePackage();
        $pack = TexturePack::findOrFail(1);

        $reseller = $this->api('api/v3/vendors/config', ['packageUid' => 'pkgTest1', 'texturePackId' => 1])->assertOk()
            ->assertJsonPath('data.inStock', true)
            ->assertJsonPath('data.textures.offline', $pack->offline)
            ->assertJsonPath('data.textures.makePayment', $pack->make_payment)
            ->assertJsonPath('data.reseller', ['rate' => 10, 'mode' => 'reseller']);
        $reseller->assertJsonStructure(['data' => ['textures' => ['waitOwner', 'gettingDetails', 'packageSmall', 'packageBig', 'packageSoldOut']]]);

        $this->api('api/v3/vendors/config', ['packageUid' => 'pkgTest1', 'texturePackId' => 1], ApiClass::Owner)
            ->assertJsonPath('data.reseller', ['rate' => 100, 'mode' => 'owner']);
        $this->api('api/v3/vendors/config', ['packageUid' => 'pkgTest1', 'texturePackId' => 999])->assertStatus(404);
        $this->api('api/v3/vendors/config', ['packageUid' => 'nope', 'texturePackId' => 1])->assertStatus(404);
        $this->api('api/v3/vendors/config', ['packageUid' => 'pkgTest1', 'texturePackId' => 0])->assertStatus(422);
    }

    public function test_a_texture_pack_has_every_texture(): void
    {
        $pack = TexturePack::findOrFail(1);

        $this->api('api/v3/texture-packs/show', ['texturePackId' => 1])->assertOk()
            ->assertJsonPath('data.textures', [
                'offline' => $pack->offline, 'waitOwner' => $pack->wait_owner, 'gettingDetails' => $pack->getting_details, 'makePayment' => $pack->make_payment,
                'renewHere' => $pack->renew_here, 'inUse' => $pack->in_use, 'requestDetails' => $pack->request_details,
                'stockLevels' => $pack->stock_levels, 'proxyRenew' => $pack->proxy_renew,
            ])
            ->assertJsonPath('data.reseller.mode', 'reseller');
        $this->api('api/v3/texture-packs/show', ['texturePackId' => 999])->assertStatus(404);
    }

    public function test_stock_levels_count_free_streams_per_server_and_package(): void
    {
        $package = $this->freePackage();
        $stream = Stream::where('package_id', $package->id)->sole();
        Stream::create([
            'server_id' => $stream->server_id, 'package_id' => $package->id, 'port' => 9999, 'admin_username' => 'a', 'admin_password' => 'b',
            'dj_password' => 'c', 'stream_uid' => 'extra001', 'mountpoint' => '/x', 'need_work' => true,
        ]);

        $servers = $this->api('api/v3/stock/levels')->assertOk()->json('data.servers');

        $this->assertCount(1, $servers);
        $this->assertSame('stream.test', $servers[0]['domain']);
        $this->assertSame([['id' => $package->id, 'name' => 'Test package', 'stock' => 1]], $servers[0]['packages'], 'a stream that needs work is not stock');
    }

    public function test_a_tree_vendor_lists_its_packages_once_per_look_alike(): void
    {
        $package = $this->freePackage();
        $twin = $package->replicate();
        $twin->package_uid = 'pkgTwin1';
        $twin->name = 'Twin package';
        $twin->save();
        $dearer = $package->replicate();
        $dearer->package_uid = 'pkgDear1';
        $dearer->name = 'Dearer package';
        $dearer->cost = 900;
        $dearer->save();
        $vendor = TreeVendor::create(['name' => 'Tree', 'texture_waiting' => '11111111-1111-4111-8111-111111111111', 'texture_inuse' => '22222222-2222-4222-8222-222222222222', 'hide_soldout' => false]);
        foreach ([$package, $twin, $dearer] as $linked) {
            DB::table('tree_vendor_packages')->insert(['tree_vendor_id' => $vendor->id, 'package_id' => $linked->id]);
        }

        $response = $this->api('api/v3/tree-vendors/packages', ['treeVendorId' => $vendor->id])->assertOk()
            ->assertJsonPath('data.textureInUse', '22222222-2222-4222-8222-222222222222')
            ->assertJsonPath('data.textureWaiting', '11111111-1111-4111-8111-111111111111');

        // newest link first; the original and its twin look the same to a client, so only the newer is listed
        $this->assertSame(['pkgDear1', 'pkgTwin1'], array_column($response->json('data.packages'), 'packageUid'));
        $this->assertSame(['packageUid' => 'pkgTwin1', 'serverType' => 'B', 'autoDj' => false, 'autoDjSize' => 0, 'listeners' => 10, 'bitrate' => 128, 'days' => 7, 'cost' => 700], $response->json('data.packages.1'));

        // only the original has a free stream
        $vendor->update(['hide_soldout' => true]);
        $this->assertSame(['pkgTest1'], array_column($this->api('api/v3/tree-vendors/packages', ['treeVendorId' => $vendor->id])->json('data.packages'), 'packageUid'));

        $this->api('api/v3/tree-vendors/packages', ['treeVendorId' => 999])->assertStatus(404);
        $empty = TreeVendor::create(['name' => 'Empty', 'texture_waiting' => $vendor->texture_waiting, 'texture_inuse' => $vendor->texture_inuse, 'hide_soldout' => false]);
        $this->api('api/v3/tree-vendors/packages', ['treeVendorId' => $empty->id])->assertStatus(422)->assertJson(['message' => 'No packages are assigned to this tree vendor']);
    }
}
