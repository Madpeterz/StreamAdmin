<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Tree;

use App\LegacyApi\SlEndpoint;
use App\Models\Package;
use App\Models\Stream;
use App\Models\TreeVendor;
use Illuminate\Support\Facades\DB;

/**
 * The packages a tree vendor offers, as parallel lists (packageUid, package_cost, ...).
 * Packages that look the same to a client (same type, size, price...) are listed once.
 */
class Getpackages extends SlEndpoint
{
    /** server type => the letter tree vendors expect */
    private const SERVER_TYPES = ['Icecast' => 'A', 'ShoutcastV1' => 'B', 'ShoutcastV2' => 'C'];

    public function process(): void
    {
        $vendorId = $this->input->intMin('tree_vender_id', 1);
        if ($vendorId === null) {
            $this->failed('Invaild tree vender id given or none sent!');

            return;
        }
        $vendor = TreeVendor::find($vendorId);
        if ($vendor === null) {
            $this->failed('Unable to load selected tree vender');

            return;
        }
        $this->set('textureInuse', $vendor->texture_inuse);
        $this->set('textureWaiting', $vendor->texture_waiting);

        // 2.0.x loadByTreevenderLink: newest link first
        $packageIds = DB::table('tree_vendor_packages')->where('tree_vendor_id', $vendor->id)->orderByDesc('id')->pluck('package_id');
        if ($packageIds->isEmpty()) {
            $this->failed('No packages assigned to tree vender');

            return;
        }
        $packages = Package::with('serverType')->whereKey($packageIds)->get()->keyBy('id');
        if ($packages->count() !== $packageIds->unique()->count()) {
            $this->failed('Incorrect number of packages loaded');

            return;
        }
        $this->ok('ok');

        $reply = array_fill_keys(['packageUid', 'package_autodj', 'package_autodjsize', 'package_listeners', 'package_bitrate', 'package_days', 'package_cost', 'package_servertype'], []);
        $seen = [];
        foreach ($packageIds as $packageId) {
            $package = $packages[$packageId];
            if ($vendor->hide_soldout && ! Stream::where('package_id', $package->id)->where('need_work', false)->whereNull('rental_id')->exists()) {
                continue;
            }
            $type = self::SERVER_TYPES[$package->serverType->name ?? ''] ?? null;
            $hash = sha1(implode(' ', [$type, $package->autodj, $package->autodj_size, $package->listeners, $package->bitrate, $package->days, $package->cost]));
            if (in_array($hash, $seen, true)) {
                continue;
            }
            $seen[] = $hash;
            $reply['package_servertype'][] = $type;
            $reply['packageUid'][] = $package->package_uid;
            $reply['package_autodj'][] = $package->autodj ? 'Yes' : 'No';
            $reply['package_autodjsize'][] = (string) ($package->autodj_size ?? '0');
            $reply['package_listeners'][] = $package->listeners;
            $reply['package_bitrate'][] = $package->bitrate;
            // 2.0.x meant to say "One week" etc. here but its lookup never matched, so scripts get "N day's"
            $reply['package_days'][] = $package->days." day's";
            $reply['package_cost'][] = $package->cost;
        }
        foreach ($reply as $tag => $values) {
            $this->set($tag, $values);
        }
    }
}
