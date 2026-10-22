<?php

namespace App\Livewire\TexturePacks;

use App\Models\TexturePack;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Texture packs')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.texture-packs.index', [
            'packs' => TexturePack::orderBy('name')->get(),
        ]);
    }
}
