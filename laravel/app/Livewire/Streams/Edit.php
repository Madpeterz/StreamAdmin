<?php

namespace App\Livewire\Streams;

use App\Models\Package;
use App\Models\Server;
use App\Models\Stream;
use App\Services\StreamService;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Component;
use RuntimeException;

/**
 * Create (no stream) or edit a stream. Validation follows the 2.0.x Stream controls.
 */
class Edit extends Component
{
    public ?Stream $stream = null;

    public ?int $server_id = null;

    public ?int $package_id = null;

    public ?int $port = null;

    public string $mountpoint = '/live';

    public string $admin_username = '';

    public string $admin_password = '';

    public string $dj_password = '';

    public bool $need_work = false;

    public function mount(?Stream $stream = null): void
    {
        if ($stream?->exists) {
            $this->stream = $stream;
            $this->fill($stream->only(['server_id', 'package_id', 'port', 'mountpoint', 'admin_username', 'admin_password', 'dj_password', 'need_work']));

            return;
        }
        $this->server_id = ctype_digit((string) request()->query('server')) ? (int) request()->query('server') : Server::orderBy('domain')->value('id');
        $this->package_id = ctype_digit((string) request()->query('package')) ? (int) request()->query('package') : Package::orderBy('name')->value('id');
        $this->port = $this->nextFreePort();
    }

    protected function rules(): array
    {
        return [
            'server_id' => ['required', 'exists:servers,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'port' => ['required', 'integer', 'min:1', 'max:99999',
                Rule::unique('streams', 'port')->where('server_id', $this->server_id)->ignore($this->stream?->id)],
            'mountpoint' => ['required', 'string', 'max:100'],
            'admin_username' => ['required', 'string', 'min:3', 'max:50'],
            'admin_password' => ['required', 'string', 'min:4', 'max:20'],
            'dj_password' => ['required', 'string', 'min:4', 'max:20'],
            'need_work' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return ['port.unique' => 'There is already a stream on that port for the selected server.'];
    }

    protected function validationAttributes(): array
    {
        return ['server_id' => 'server', 'package_id' => 'package', 'dj_password' => 'DJ password'];
    }

    public function save(StreamService $streams): void
    {
        $data = $this->validate();
        if ($this->stream) {
            unset($data['need_work']);
            $streams->update($this->stream, $data);
            $this->need_work = false;
            Flux::toast(variant: 'success', text: 'Stream saved.');

            return;
        }
        $stream = $streams->create($data);
        Flux::toast(variant: 'success', text: 'Stream created on port '.$stream->port.'.');
        $this->redirectRoute('streams.edit', $stream, navigate: true);
    }

    /** Create, then keep the form filled in for the next port on the same server. */
    public function saveAndNext(StreamService $streams): void
    {
        $stream = $streams->create($this->validate());
        Flux::toast(variant: 'success', text: 'Stream created on port '.$stream->port.'.');
        $this->resetValidation();
        $this->reset('admin_password', 'dj_password');
        $this->port = $this->nextFreePort($stream->port);
    }

    public function updatedServerId(): void
    {
        if ($this->stream === null) {
            $this->port = $this->nextFreePort();
        }
    }

    public function delete(StreamService $streams): void
    {
        $this->attempt(fn () => $streams->delete($this->stream), 'Stream removed.', 'stream-delete');
        if (! $this->stream->exists) {
            $this->redirectRoute('streams.index', navigate: true);
        }
    }

    public function restore(StreamService $streams): void
    {
        $this->attempt(function () use ($streams) {
            $rental = $streams->restore($this->stream);
            $this->redirectRoute('clients.show', $rental, navigate: true);
        }, 'Stream restored to its last client. The rental has no time left until they renew.', 'stream-restore');
    }

    private function attempt(callable $action, string $success, string $modal): void
    {
        try {
            $action();
            Flux::toast(variant: 'success', text: $success);
        } catch (RuntimeException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
        Flux::modal($modal)->close();
    }

    /** The first free port after $after (default: after the highest port on the server). */
    private function nextFreePort(?int $after = null): int
    {
        $taken = Stream::where('server_id', $this->server_id)->pluck('port')->all();
        $port = ($after ?? (max($taken ?: [7999]))) + 1;
        while (in_array($port, $taken, true)) {
            $port++;
        }

        return $port;
    }

    public function render()
    {
        $this->stream?->load(['rental.avatar', 'server']);

        return view('livewire.streams.edit', [
            'servers' => Server::orderBy('domain')->get(),
            'packages' => Package::orderBy('name')->get(),
        ])->title($this->stream ? 'Stream '.$this->stream->server?->domain.':'.$this->stream->port : 'Add stream');
    }
}
