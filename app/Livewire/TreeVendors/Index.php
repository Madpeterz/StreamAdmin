<?php

namespace App\Livewire\TreeVendors;

use App\Models\Stream;
use App\Models\TreeVendor;
use App\Services\SalesService;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Tree vendors')]
class Index extends Component
{
    public string $name = '';

    public function create(SalesService $sales): void
    {
        $data = $this->validate(['name' => ['required', 'string', 'min:4', 'max:100', Rule::unique('tree_vendors', 'name')]],
            ['name.unique' => 'There is already a tree vendor with that name.']);
        $vendor = $sales->saveTreeVendor(null, $data);
        Flux::toast(variant: 'success', text: 'Tree vendor created. Now add its packages.');
        $this->redirectRoute('tree-vendors.edit', $vendor, navigate: true);
    }

    public function render()
    {
        $ready = Stream::whereNull('rental_id')->where('need_work', false)
            ->selectRaw('package_id, COUNT(*) AS ready')->groupBy('package_id')->toBase()->get()->pluck('ready', 'package_id');

        return view('livewire.tree-vendors.index', [
            'vendors' => TreeVendor::with(['packages' => fn ($query) => $query->orderBy('name')])->orderBy('name')->get(),
            'ready' => $ready,
        ]);
    }
}
