<?php

namespace App\Services;

use App\Api\ApiException;
use App\Api\Caller;
use App\Models\Package;
use App\Models\Server;
use App\Models\Stream;
use App\Models\TexturePack;
use App\Models\TreeVendor;
use Illuminate\Support\Facades\DB;

/**
 * What vendors, stock boards and tree vendors read to show the catalog (port of the 2.0.x Buy,
 * Stock, Texturepack and Tree endpoints).
 */
class VendorCatalog
{
    /** server type => the letter tree vendors expect */
    private const SERVER_TYPES = ['Icecast' => 'A', 'ShoutcastV1' => 'B', 'ShoutcastV2' => 'C'];

    /**
     * Whether a stream of this package is free to rent, and how the vendor shows it.
     *
     * @return array<string, mixed>
     */
    public function packageStatus(string $packageUid): array
    {
        $package = $this->package($packageUid);

        return [
            'inStock' => $this->inStock($package),
            'cost' => (int) $package->cost,
            'textures' => $this->packageTextures($package),
        ];
    }

    /**
     * A rental vendor's setup: textures, price, stock and the reseller's rate.
     *
     * @return array<string, mixed>
     */
    public function vendorConfig(Caller $caller, string $packageUid, int $texturePackId): array
    {
        $pack = TexturePack::find($texturePackId) ?? throw ApiException::notFound('Unable to load texture pack');
        $package = $this->package($packageUid);

        return [
            'inStock' => $this->inStock($package),
            'cost' => (int) $package->cost,
            'textures' => [
                'offline' => $pack->offline,
                'waitOwner' => $pack->wait_owner,
                'gettingDetails' => $pack->getting_details,
                'makePayment' => $pack->make_payment,
            ] + $this->packageTextures($package),
            'reseller' => $this->reseller($caller),
        ];
    }

    /**
     * All textures of a texture pack, plus the calling reseller's rate.
     *
     * @return array<string, mixed>
     */
    public function texturePack(Caller $caller, int $id): array
    {
        $pack = TexturePack::find($id) ?? throw ApiException::notFound('Unable to load texture pack');

        return [
            'textures' => [
                'offline' => $pack->offline,
                'waitOwner' => $pack->wait_owner,
                'gettingDetails' => $pack->getting_details,
                'makePayment' => $pack->make_payment,
                'renewHere' => $pack->renew_here,
                'inUse' => $pack->in_use,
                'requestDetails' => $pack->request_details,
                'stockLevels' => $pack->stock_levels,
                'proxyRenew' => $pack->proxy_renew,
            ],
            'reseller' => $this->reseller($caller),
        ];
    }

    /**
     * Free streams per server and package.
     *
     * @return list<array<string, mixed>>
     */
    public function stockLevels(): array
    {
        $packages = Package::orderBy('id')->get()
            ->mapWithKeys(fn (Package $package) => [$package->id => ['id' => $package->id, 'name' => $package->name, 'stock' => 0]])
            ->all();
        $servers = Server::orderBy('id')->get()
            ->mapWithKeys(fn (Server $server) => [$server->id => ['id' => $server->id, 'domain' => $server->domain, 'packages' => $packages]])
            ->all();
        $free = Stream::whereNull('rental_id')->where('need_work', false)
            ->selectRaw('server_id, package_id, COUNT(*) AS free')->groupBy('server_id', 'package_id')->toBase()->get();
        foreach ($free as $row) {
            if (isset($servers[$row->server_id]['packages'][$row->package_id])) {
                $servers[$row->server_id]['packages'][$row->package_id]['stock'] += (int) $row->free;
            }
        }

        return array_values(array_map(fn (array $server) => ['packages' => array_values($server['packages'])] + $server, $servers));
    }

    /**
     * The packages a tree vendor offers. Packages that look the same to a client (same type, size,
     * price...) are listed once.
     *
     * @return array<string, mixed>
     */
    public function treePackages(int $treeVendorId): array
    {
        $vendor = TreeVendor::find($treeVendorId) ?? throw ApiException::notFound('Unable to load the tree vendor');
        // newest link first
        $packageIds = DB::table('tree_vendor_packages')->where('tree_vendor_id', $vendor->id)->orderByDesc('id')->pluck('package_id');
        if ($packageIds->isEmpty()) {
            throw ApiException::rejected('No packages are assigned to this tree vendor');
        }
        $packages = Package::with('serverType')->whereKey($packageIds)->get()->keyBy('id');
        if ($packages->count() !== $packageIds->unique()->count()) {
            throw ApiException::rejected('Incorrect number of packages loaded');
        }

        $list = [];
        $seen = [];
        foreach ($packageIds as $packageId) {
            $package = $packages[$packageId];
            if ($vendor->hide_soldout && ! $this->inStock($package)) {
                continue;
            }
            $type = self::SERVER_TYPES[$package->serverType->name ?? ''] ?? null;
            $hash = sha1(implode(' ', [$type, $package->autodj, $package->autodj_size, $package->listeners, $package->bitrate, $package->days, $package->cost]));
            if (in_array($hash, $seen, true)) {
                continue;
            }
            $seen[] = $hash;
            $list[] = [
                'packageUid' => $package->package_uid,
                'serverType' => $type,
                'autoDj' => (bool) $package->autodj,
                'autoDjSize' => (int) ($package->autodj_size ?? 0),
                'listeners' => (int) $package->listeners,
                'bitrate' => (int) $package->bitrate,
                'days' => (int) $package->days,
                'cost' => (int) $package->cost,
            ];
        }

        return ['textureInUse' => $vendor->texture_inuse, 'textureWaiting' => $vendor->texture_waiting, 'packages' => $list];
    }

    private function package(string $packageUid): Package
    {
        return Package::where('package_uid', $packageUid)->first() ?? throw ApiException::notFound('Unable to find package');
    }

    private function inStock(Package $package): bool
    {
        return Stream::where('package_id', $package->id)->whereNull('rental_id')->where('need_work', false)->exists();
    }

    /** @return array<string, string> */
    private function packageTextures(Package $package): array
    {
        return [
            'packageSmall' => $package->texture_instock_small,
            'packageBig' => $package->texture_instock_selected,
            'packageSoldOut' => $package->texture_soldout,
        ];
    }

    /** @return array{rate: int, mode: string} */
    private function reseller(Caller $caller): array
    {
        return $caller->isOwner()
            ? ['rate' => 100, 'mode' => 'owner']
            : ['rate' => (int) $caller->reseller->rate, 'mode' => 'reseller'];
    }
}
