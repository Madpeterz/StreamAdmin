<?php

namespace App\Livewire\Templates;

use App\Models\Template;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Templates hold the stream details message and notecard a package sends to new clients.
 */
#[Title('Templates')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.templates.index', [
            'templates' => Template::withCount('packages')->orderBy('name')->get(),
        ]);
    }
}
