<?php

namespace App\Livewire\TreeVendors;

use App\Models\Package;
use App\Models\Stream;
use App\Models\TreeVendor;
use App\Services\SalesService;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Component;
use RuntimeException;

class Edit extends Component
{
    public TreeVendor $vendor;

    public string $name = '';

    public string $texture_waiting = '';

    public string $texture_inuse = '';

    public bool $hide_soldout = false;

    public ?int $addPackageId = null;

    public function mount(TreeVendor $vendor): void
    {
        $this->vendor = $vendor;
        $this->fill($vendor->only(['name', 'texture_waiting', 'texture_inuse', 'hide_soldout']));
    }

    public function save(SalesService $sales): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'min:4', 'max:100', Rule::unique('tree_vendors', 'name')->ignore($this->vendor->id)],
            'texture_waiting' => ['required', 'uuid'],
            'texture_inuse' => ['required', 'uuid'],
            'hide_soldout' => ['boolean'],
        ], ['name.unique' => 'There is already a tree vendor with that name.']);
        $sales->saveTreeVendor($this->vendor, $data);
        Flux::toast(variant: 'success', text: 'Tree vendor saved.');
    }

    public function addPackage(SalesService $sales): void
    {
        $this->validate(['addPackageId' => ['required', 'exists:packages,id']], [], ['addPackageId' => 'package']);
        try {
            $sales->addTreePackage($this->vendor, Package::findOrFail($this->addPackageId));
            $this->reset('addPackageId');
        } catch (RuntimeException $e) {
            $this->addError('addPackageId', $e->getMessage());
        }
    }

    public function removePackage(int $packageId, SalesService $sales): void
    {
        $sales->removeTreePackage($this->vendor, Package::findOrFail($packageId));
        Flux::toast(text: 'Package removed from the tree vendor.');
    }

    public function delete(SalesService $sales): void
    {
        $sales->deleteTreeVendor($this->vendor);
        Flux::toast(variant: 'success', text: 'Tree vendor removed.');
        $this->redirectRoute('tree-vendors.index', navigate: true);
    }

    public function render()
    {
        $packages = $this->vendor->packages()->with('serverType')->orderBy('name')->get();
        $ready = Stream::whereNull('rental_id')->where('need_work', false)->whereIn('package_id', $packages->pluck('id'))
            ->selectRaw('package_id, COUNT(*) AS ready')->groupBy('package_id')->toBase()->get()->pluck('ready', 'package_id');

        return view('livewire.tree-vendors.edit', [
            'packages' => $packages,
            'ready' => $ready,
            'available' => Package::whereNotIn('id', $packages->pluck('id'))->orderBy('name')->get(),
        ])->title($this->vendor->name);
    }
}
