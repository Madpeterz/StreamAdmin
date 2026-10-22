<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Stock;

use App\LegacyApi\SlEndpoint;
use App\Models\Package;
use App\Models\Server;
use App\Models\Stream;

/** Free streams per server and package: results.{serverId}.packages.{packageId}.stock */
class Stocklevel extends SlEndpoint
{
    public function process(): void
    {
        $packages = Package::orderBy('id')->get()
            ->mapWithKeys(fn (Package $package) => [$package->id => ['name' => $package->name, 'stock' => 0]])
            ->all();
        $results = Server::orderBy('id')->get()
            ->mapWithKeys(fn (Server $server) => [$server->id => ['domain' => $server->domain, 'packages' => $packages]])
            ->all();
        $free = Stream::whereNull('rental_id')->where('need_work', false)
            ->selectRaw('server_id, package_id, COUNT(*) AS free')->groupBy('server_id', 'package_id')->toBase()->get();
        foreach ($free as $row) {
            if (isset($results[$row->server_id]['packages'][$row->package_id])) {
                $results[$row->server_id]['packages'][$row->package_id]['stock'] += (int) $row->free;
            }
        }
        $this->set('results', $results);
        $this->ok('see results');
    }
}
