<?php

namespace App\Livewire\TexturePacks;

use App\Models\TexturePack;
use App\Services\SystemService;
use Flux\Flux;
use Livewire\Component;

/**
 * Create (no pack) or edit a texture pack: the textures vendors and servers show in each state.
 */
class Edit extends Component
{
    /** column => [label, when it shows], in the order staff see them */
    public const TEXTURES = [
        'offline' => ['Offline', 'StreamAdmin cannot be reached.'],
        'wait_owner' => ['Waiting for owner', 'The object is waiting for its owner to set it up.'],
        'in_use' => ['In use', 'Someone else is using the vendor.'],
        'stock_levels' => ['Stock levels', 'Showing what is in stock.'],
        'make_payment' => ['Request payment', 'Waiting for the client to pay.'],
        'renew_here' => ['Renew here', 'Idle renewal point.'],
        'proxy_renew' => ['Proxy renew', 'Renewing for someone else.'],
        'getting_details' => ['Fetching details', 'Looking up the stream a client rents.'],
        'request_details' => ['Request details', 'Sending a client their details.'],
    ];

    public ?TexturePack $pack = null;

    public string $name = '';

    /** @var array<string, string> */
    public array $textures = [];

    public function mount(?TexturePack $pack = null): void
    {
        $this->textures = array_fill_keys(array_keys(self::TEXTURES), '');
        if ($pack?->exists) {
            $this->pack = $pack;
            $this->name = $pack->name;
            $this->textures = $pack->only(array_keys(self::TEXTURES));
        }
    }

    public function save(SystemService $system): void
    {
        $rules = ['name' => ['required', 'string', 'min:4', 'max:30']];
        $names = ['name' => 'name'];
        foreach (self::TEXTURES as $column => [$label]) {
            $rules["textures.{$column}"] = ['required', 'uuid'];
            $names["textures.{$column}"] = strtolower($label).' texture';
        }
        $data = $this->validate($rules, [], $names);
        $creating = $this->pack === null;
        $this->pack = $system->saveTexturePack($this->pack, ['name' => $data['name']] + $data['textures']);
        Flux::toast(variant: 'success', text: $creating ? 'Texture pack created.' : 'Texture pack saved.');
        if ($creating) {
            $this->redirectRoute('texture-packs.edit', $this->pack, navigate: true);
        }
    }

    public function delete(SystemService $system): void
    {
        $system->deleteTexturePack($this->pack);
        Flux::toast(variant: 'success', text: 'Texture pack removed.');
        $this->redirectRoute('texture-packs.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.texture-packs.edit')->title($this->pack?->name ?? 'New texture pack');
    }
}
