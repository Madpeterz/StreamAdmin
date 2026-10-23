<?php

namespace App\Livewire\Outbox;

use App\Livewire\Concerns\SearchesAvatars;
use App\Models\Avatar;
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
 * Bulk message: pick who (all clients, a notice level, package, server, one client or one chosen avatar), write the
 * message with swap tags, untick anyone to skip, send (port of the 2.0.x Outbox bulk send).
 */
#[Title('Send a message')]
class Compose extends Component
{
    use SearchesAvatars;

    #[Url]
    public string $source = 'clients';

    /** the avatar for the 'avatar' source (any avatar, with a rental or not) */
    #[Url(as: 'uid')]
    public string $avatarUid = '';

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
        if ($this->source === 'avatar' || ($this->source !== 'clients' && $this->sourceId === null)) {
            return collect();
        }
        try {
            return $messaging->recipients($this->source, $this->sourceId);
        } catch (RuntimeException) {
            return collect();
        }
    }

    private function chosenAvatar(): ?Avatar
    {
        return $this->avatarUid === '' ? null : Avatar::where('avatar_uid', $this->avatarUid)->first();
    }

    private function sendToAvatar(MessagingService $messaging): void
    {
        $this->validate([
            'message' => ['required', 'string', 'min:10', 'max:800'],
            'avatarUid' => ['required', 'string', 'exists:avatars,avatar_uid'],
        ], ['avatarUid.required' => 'Choose an avatar to send to.', 'avatarUid.exists' => 'That avatar was not found.']);
        $avatar = $this->chosenAvatar();
        try {
            $messaging->sendToAvatar($this->message, $avatar);
        } catch (RuntimeException $e) {
            $this->addError('message', $e->getMessage());

            return;
        }
        Flux::toast(variant: 'success', text: "Message queued for {$avatar->avatar_name}.");
        $this->redirectRoute('outbox.index', navigate: true);
    }

    public function send(MessagingService $messaging): void
    {
        if ($this->source === 'avatar') {
            $this->sendToAvatar($messaging);

            return;
        }
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
        $avatar = $this->source === 'avatar' ? $this->chosenAvatar() : null;

        return view('livewire.outbox.compose', [
            'recipients' => $recipients,
            'options' => match ($this->source) {
                'notice' => Notice::orderByDesc('hours_remaining')->pluck('name', 'id'),
                'package' => Package::orderBy('name')->pluck('name', 'id'),
                'server' => Server::orderBy('domain')->pluck('domain', 'id'),
                default => collect(),
            },
            'single' => $this->source === 'rental' ? Rental::with('avatar')->find($this->sourceId) : null,
            'avatar' => $avatar,
            'matches' => $this->source === 'avatar' ? $this->avatarMatches() : collect(),
            'preview' => match (true) {
                $avatar !== null => $messaging->textForAvatar($this->message, $avatar),
                $first !== null => Swaps::forRental($this->message, $first),
                default => Swaps::example($this->message),
            },
            'previewFor' => $avatar?->avatar_name ?? $first?->avatar?->avatar_name,
        ]);
    }
}
