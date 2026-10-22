<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Server;
use App\Models\Transaction;
use App\Models\TreeVendor;
use App\Support\Uid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Create/update/remove for servers and packages (port of the 2.0.x Server and Package controls).
 */
class CatalogService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $attributes */
    public function saveServer(?Server $server, array $attributes): Server
    {
        return $this->save($server ?? new Server, $attributes, 'Server', 'domain');
    }

    public function deleteServer(Server $server): void
    {
        $streams = $server->streams()->count();
        if ($streams > 0) {
            throw new RuntimeException("This server still has {$streams} stream(s). Move or remove them first.");
        }
        DB::transaction(function () use ($server) {
            $this->audit->log('Server', (string) $server->id, '---', $server->domain);
            $server->delete();
        });
    }

    /** @param array<string, mixed> $attributes */
    public function savePackage(?Package $package, array $attributes): Package
    {
        if ($package === null) {
            $attributes['package_uid'] = Uid::unique(Package::class, 'package_uid');
        }

        return $this->save($package ?? new Package, $attributes, 'Package', 'name');
    }

    public function deletePackage(Package $package): void
    {
        $uses = array_filter([
            'stream(s)' => $package->streams()->count(),
            'client(s)' => $package->rentals()->count(),
            'tree vendor(s)' => TreeVendor::whereHas('packages', fn ($query) => $query->whereKey($package->id))->count(),
        ]);
        if ($uses !== []) {
            $what = collect($uses)->map(fn ($count, $label) => $count.' '.$label)->implode(', ');
            throw new RuntimeException('This package is still used by '.$what.'.');
        }
        DB::transaction(function () use ($package) {
            Transaction::where('package_id', $package->id)->update(['package_id' => null]);
            $this->audit->log('Package', (string) $package->id, '---', $package->name);
            $package->delete();
        });
    }

    /**
     * @template T of Model
     *
     * @param  T  $model
     * @param  array<string, mixed>  $attributes
     * @return T
     */
    private function save(Model $model, array $attributes, string $store, string $nameField): Model
    {
        return DB::transaction(function () use ($model, $attributes, $store, $nameField) {
            $creating = ! $model->exists;
            $model->fill($attributes)->save();
            if ($creating) {
                $this->audit->log($store, (string) $model->getKey(), '+++', null, $model->{$nameField});
            } else {
                $this->audit->logChanges($store, (string) $model->getKey(), $model);
            }

            return $model;
        });
    }
}
