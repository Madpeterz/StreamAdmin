<?php

namespace App\Livewire\Outbox;

use App\Models\Notice;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Services\MessagingService;
use App\Support\Swaps;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use RuntimeException;

/**
 * Bulk message: pick who (all clients, a notice level, package, server, or one client), write the
 * message with swap tags, untick anyone to skip, send (port of the 2.0.x Outbox bulk send).
 */
#[Title('Send a message')]
class Compose extends Component
{
    #[Url]
    public string $source = 'clients';

    #[Url(as: 'id')]
    public ?int $sourceId = null;

    public string $message = '';

    /** @var list<string> avatar ids to send to */
    public array $selected = [];

    public function mount(MessagingService $messaging): void
    {
        $this->selectAll($messaging);
    }

    public function updatedSource(MessagingService $messaging): void
    {
        $this->sourceId = null;
        $this->selectAll($messaging);
    }

    public function updatedSourceId(MessagingService $messaging): void
    {
        $this->selectAll($messaging);
    }

    private function selectAll(MessagingService $messaging): void
    {
        $this->selected = $this->recipients($messaging)->pluck('avatar_id')->map(fn ($id) => (string) $id)->all();
    }

    /** @return Collection<int, Rental> */
    private function recipients(MessagingService $messaging): Collection
    {
        if ($this->source !== 'clients' && $this->sourceId === null) {
            return collect();
        }
        try {
            return $messaging->recipients($this->source, $this->sourceId);
        } catch (RuntimeException) {
            return collect();
        }
    }

    public function send(MessagingService $messaging): void
    {
        $this->validate([
            'message' => ['required', 'string', 'min:10', 'max:800'],
            'selected' => ['required', 'array', 'min:1'],
        ], ['selected.required' => 'Choose at least one client to send to.']);
        $rentals = $this->recipients($messaging)->whereIn('avatar_id', array_map('intval', $this->selected));
        try {
            $sent = $messaging->sendBulk($this->message, $rentals);
        } catch (RuntimeException $e) {
            $this->addError('message', $e->getMessage());

            return;
        }
        Flux::toast(variant: 'success', text: "Message queued for {$sent} ".str('avatar')->plural($sent).'.');
        $this->redirectRoute('outbox.index', navigate: true);
    }

    public function render(MessagingService $messaging)
    {
        $recipients = $this->recipients($messaging);
        $first = $recipients->firstWhere(fn (Rental $rental) => in_array((string) $rental->avatar_id, $this->selected, true));

        return view('livewire.outbox.compose', [
            'recipients' => $recipients,
            'options' => match ($this->source) {
                'notice' => Notice::orderByDesc('hours_remaining')->pluck('name', 'id'),
                'package' => Package::orderBy('name')->pluck('name', 'id'),
                'server' => Server::orderBy('domain')->pluck('domain', 'id'),
                default => collect(),
            },
            'single' => $this->source === 'rental' ? Rental::with('avatar')->find($this->sourceId) : null,
            'preview' => $first ? Swaps::forRental($this->message, $first) : Swaps::example($this->message),
            'previewFor' => $first?->avatar?->avatar_name,
        ]);
    }
}
