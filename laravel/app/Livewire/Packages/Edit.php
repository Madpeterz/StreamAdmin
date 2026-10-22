<?php

namespace App\Livewire\Packages;

use App\Models\NoticeNotecard;
use App\Models\Package;
use App\Models\ServerType;
use App\Models\Template;
use App\Services\CatalogService;
use Flux\Flux;
use Livewire\Component;
use RuntimeException;

/**
 * Create (no package) or edit a package. Validation follows the 2.0.x Package controls.
 */
class Edit extends Component
{
    public ?Package $package = null;

    public string $name = '';

    public ?int $server_type_id = null;

    public ?int $template_id = null;

    public int $cost = 0;

    public int $days = 7;

    /** maps to packages.listeners ($listeners is reserved by Livewire) */
    public int $listener_limit = 10;

    public int $bitrate = 128;

    public bool $autodj = false;

    public ?int $autodj_size = null;

    public string $texture_instock_small = '';

    public string $texture_instock_selected = '';

    public string $texture_soldout = '';

    public int $welcome_notecard_id = 1;

    public int $setup_notecard_id = 1;

    public bool $enable_group_invite = false;

    public bool $enforce_custom_max_streams = false;

    public int $max_streams_in_package = 1;

    public function mount(?Package $package = null): void
    {
        if ($package?->exists) {
            $this->package = $package;
            $this->fill($package->only([
                'name', 'server_type_id', 'template_id', 'cost', 'days', 'bitrate', 'autodj',
                'texture_instock_small', 'texture_instock_selected', 'texture_soldout', 'welcome_notecard_id',
                'setup_notecard_id', 'enable_group_invite', 'enforce_custom_max_streams', 'max_streams_in_package',
            ]));
            $this->listener_limit = $package->listeners;
            $this->autodj_size = is_numeric($package->autodj_size) ? (int) $package->autodj_size : null;
        } else {
            $this->server_type_id = ServerType::orderBy('id')->value('id');
            $this->template_id = Template::orderBy('id')->value('id');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:5', 'max:30'],
            'server_type_id' => ['required', 'exists:server_types,id'],
            'template_id' => ['required', 'exists:templates,id'],
            'cost' => ['required', 'integer', 'min:1', 'max:99999'],
            'days' => ['required', 'integer', 'min:1', 'max:999'],
            'listener_limit' => ['required', 'integer', 'min:1', 'max:999'],
            'bitrate' => ['required', 'integer', 'min:56', 'max:999'],
            'autodj' => ['boolean'],
            'autodj_size' => ['nullable', 'required_if:autodj,true', 'integer', 'min:1', 'max:9999'],
            'texture_instock_small' => ['required', 'uuid'],
            'texture_instock_selected' => ['required', 'uuid'],
            'texture_soldout' => ['required', 'uuid'],
            'welcome_notecard_id' => ['required', 'exists:notice_notecards,id'],
            'setup_notecard_id' => ['required', 'exists:notice_notecards,id'],
            'enable_group_invite' => ['boolean'],
            'enforce_custom_max_streams' => ['boolean'],
            'max_streams_in_package' => ['required', 'integer', 'min:1', 'max:2000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'server_type_id' => 'server type',
            'listener_limit' => 'listeners',
            'template_id' => 'template',
            'autodj_size' => 'AutoDJ storage',
            'texture_instock_small' => 'in stock texture',
            'texture_instock_selected' => 'selected texture',
            'texture_soldout' => 'sold out texture',
            'welcome_notecard_id' => 'welcome notecard',
            'setup_notecard_id' => 'setup notecard',
        ];
    }

    public function save(CatalogService $catalog): void
    {
        $data = $this->validate();
        $data['autodj_size'] = $data['autodj_size'] === null ? '0' : (string) $data['autodj_size'];
        $data['listeners'] = $data['listener_limit'];
        unset($data['listener_limit']);
        $creating = $this->package === null;
        $this->package = $catalog->savePackage($this->package, $data);
        Flux::toast(variant: 'success', text: $creating ? 'Package created.' : 'Package saved.');
        if ($creating) {
            $this->redirectRoute('packages.edit', $this->package, navigate: true);
        }
    }

    public function delete(CatalogService $catalog): void
    {
        try {
            $catalog->deletePackage($this->package);
        } catch (RuntimeException $e) {
            Flux::modal('package-delete')->close();
            Flux::toast(variant: 'danger', text: $e->getMessage());

            return;
        }
        Flux::toast(variant: 'success', text: 'Package removed.');
        $this->redirectRoute('packages.index', navigate: true);
    }

    public function render()
    {
        $streams = $this->package?->streams()->get(['rental_id', 'need_work']) ?? collect();

        return view('livewire.packages.edit', [
            'serverTypes' => ServerType::orderBy('name')->get(),
            'templates' => Template::orderBy('name')->get(),
            'notecards' => NoticeNotecard::orderBy('name')->get(),
            'stock' => [
                'ready' => $streams->whereNull('rental_id')->where('need_work', false)->count(),
                'need_work' => $streams->whereNull('rental_id')->where('need_work', true)->count(),
                'sold' => $streams->whereNotNull('rental_id')->count(),
            ],
        ])->title($this->package ? $this->package->name : 'New package');
    }
}
