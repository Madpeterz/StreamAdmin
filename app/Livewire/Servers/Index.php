<?php

namespace App\Livewire\Servers;

use App\Models\Server;
use App\Models\Stream;
use App\Services\CatalogService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Title('Servers')]
class Index extends Component
{
    public ?int $editingId = null;

    public string $domain = '';

    public string $control_panel_url = '';

    public ?string $ip_address = null;

    public int $bandwidth = 500;

    public string $bandwidth_type = 'mbps';

    public int $total_storage = 50;

    public string $total_storage_type = 'gb';

    public ?int $deletingId = null;

    protected function rules(): array
    {
        return [
            'domain' => ['required', 'string', 'min:5', 'max:100', Rule::unique('servers', 'domain')->ignore($this->editingId)],
            'control_panel_url' => ['required', 'string', 'min:5', 'max:200'],
            'ip_address' => ['nullable', 'ip', Rule::unique('servers', 'ip_address')->ignore($this->editingId)],
            'bandwidth' => ['required', 'integer', 'min:1', 'max:99999'],
            'bandwidth_type' => ['required', Rule::in(['kbps', 'mbps', 'gbps'])],
            'total_storage' => ['required', 'integer', 'min:1', 'max:99999999'],
            'total_storage_type' => ['required', Rule::in(['mb', 'gb', 'tb'])],
        ];
    }

    protected function messages(): array
    {
        return [
            'domain.unique' => 'There is already a server with that domain.',
            'ip_address.unique' => 'There is already a server with that IP address.',
        ];
    }

    public function edit(Server $server): void
    {
        $this->resetForm();
        $this->editingId = $server->id;
        $this->fill($server->only(['domain', 'control_panel_url', 'ip_address', 'bandwidth', 'total_storage', 'total_storage_type']));
        $this->bandwidth_type = $server->bandwidth_type === 'gpbs' ? 'gbps' : $server->bandwidth_type;
        Flux::modal('server-form')->show();
    }

    public function save(CatalogService $catalog): void
    {
        $data = $this->validate();
        $data['ip_address'] = $data['ip_address'] ?: null;
        $catalog->saveServer($this->editingId ? Server::findOrFail($this->editingId) : null, $data);
        Flux::modal('server-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? 'Server updated.' : 'Server added.');
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('server-delete')->show();
    }

    public function delete(CatalogService $catalog): void
    {
        try {
            $catalog->deleteServer(Server::findOrFail($this->deletingId));
            Flux::toast(variant: 'success', text: 'Server removed.');
        } catch (RuntimeException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
        $this->deletingId = null;
        Flux::modal('server-delete')->close();
    }

    public function resetForm(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'domain', 'control_panel_url', 'ip_address', 'bandwidth', 'bandwidth_type', 'total_storage', 'total_storage_type');
    }

    public function render()
    {
        return view('livewire.servers.index', ['servers' => $this->serversWithLoad()]);
    }

    /**
     * Per server: stream counts and how much bandwidth/storage the packages on it need, assigned
     * (all streams) and in use (rented streams).
     */
    private function serversWithLoad(): Collection
    {
        $streams = Stream::with('package')->get()->groupBy('server_id');

        return Server::orderBy('domain')->get()->map(function (Server $server) use ($streams) {
            $load = ['bandwidth_assigned' => 0, 'bandwidth_used' => 0, 'storage_assigned' => 0, 'storage_used' => 0];
            $counts = ['sold' => 0, 'ready' => 0, 'need_work' => 0];
            foreach ($streams->get($server->id, []) as $stream) {
                $package = $stream->package;
                $kbps = $package ? $package->bitrate * $package->listeners : 0;
                $mb = $package && $package->autodj ? (int) $package->autodj_size * 1000 : 0;
                $load['bandwidth_assigned'] += $kbps;
                $load['storage_assigned'] += $mb;
                if ($stream->rental_id !== null) {
                    $load['bandwidth_used'] += $kbps;
                    $load['storage_used'] += $mb;
                    $counts['sold']++;
                } else {
                    $counts[$stream->need_work ? 'need_work' : 'ready']++;
                }
            }
            $percent = fn (int $value, int $of) => $of > 0 ? round($value / $of * 100, 1) : 0;

            return [
                'server' => $server,
                'counts' => $counts,
                'bandwidth' => [
                    'assigned' => $percent($load['bandwidth_assigned'], $server->bandwidthKbps()),
                    'used' => $percent($load['bandwidth_used'], $server->bandwidthKbps()),
                ],
                'storage' => [
                    'assigned' => $percent($load['storage_assigned'], $server->storageMb()),
                    'used' => $percent($load['storage_used'], $server->storageMb()),
                ],
            ];
        });
    }
}
