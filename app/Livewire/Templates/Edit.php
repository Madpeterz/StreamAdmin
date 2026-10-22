<?php

namespace App\Livewire\Templates;

use App\Models\Template;
use App\Services\MessagingService;
use App\Support\Swaps;
use Flux\Flux;
use Livewire\Component;
use RuntimeException;

/**
 * Create (no template) or edit a template. Lengths follow the 2.0.x Template controls.
 */
class Edit extends Component
{
    public ?Template $template = null;

    public string $name = '';

    public string $detail = '';

    public string $notecard_detail = '';

    public function mount(?Template $template = null): void
    {
        if ($template?->exists) {
            $this->template = $template;
            $this->fill($template->only(['name', 'detail', 'notecard_detail']));
        }
    }

    public function save(MessagingService $messaging): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'min:5', 'max:30'],
            'detail' => ['required', 'string', 'min:5', 'max:800'],
            'notecard_detail' => ['required', 'string', 'min:5', 'max:5000'],
        ], [], ['detail' => 'IM message', 'notecard_detail' => 'notecard']);
        $creating = $this->template === null;
        $this->template = $messaging->saveTemplate($this->template, $data);
        Flux::toast(variant: 'success', text: $creating ? 'Template created.' : 'Template saved.');
        if ($creating) {
            $this->redirectRoute('templates.edit', $this->template, navigate: true);
        }
    }

    public function delete(MessagingService $messaging): void
    {
        try {
            $messaging->deleteTemplate($this->template);
        } catch (RuntimeException $e) {
            Flux::modal('template-delete')->close();
            Flux::toast(variant: 'danger', text: $e->getMessage());

            return;
        }
        Flux::toast(variant: 'success', text: 'Template removed.');
        $this->redirectRoute('templates.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.templates.edit', [
            'previewIm' => Swaps::example($this->detail),
            'previewNotecard' => Swaps::example($this->notecard_detail),
            'packages' => $this->template?->packages()->orderBy('name')->get() ?? collect(),
        ])->title($this->template?->name ?? 'New template');
    }
}
