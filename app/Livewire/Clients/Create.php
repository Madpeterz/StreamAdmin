<?php

namespace App\Livewire\Clients;

use App\Livewire\Concerns\SearchesAvatars;
use App\Models\Avatar;
use App\Models\Stream;
use App\Services\RentalService;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use RuntimeException;

/**
 * Give a stream to an avatar by hand, with no payment (port of the 2.0.x Client/Create page).
 */
#[Title('Add client')]
class Create extends Component
{
    use SearchesAvatars;

    public string $avatarUid = '';

    #[Url(as: 'stream')]
    public string $streamUid = '';

    public int $days = 7;

    public function save(RentalService $rentals): void
    {
        $this->validate([
            'avatarUid' => ['required', 'exists:avatars,avatar_uid'],
            'streamUid' => ['required', 'exists:streams,stream_uid'],
            'days' => ['required', 'integer', 'min:1', 'max:360'],
        ], [
            'avatarUid.required' => 'Pick the avatar who gets the stream.',
            'avatarUid.exists' => 'No avatar has that UID.',
            'streamUid.required' => 'Pick a stream.',
        ]);

        try {
            $rental = $rentals->create(
                Avatar::where('avatar_uid', $this->avatarUid)->firstOrFail(),
                Stream::where('stream_uid', $this->streamUid)->firstOrFail(),
                $this->days,
            );
        } catch (RuntimeException $e) {
            $this->addError('streamUid', $e->getMessage());

            return;
        }
        Flux::toast(variant: 'success', text: 'Client created.');
        $this->redirectRoute('clients.show', $rental, navigate: true);
    }

    public function render()
    {
        $streams = Stream::with(['server', 'package'])->whereNull('rental_id')->orderBy('port')->get();

        return view('livewire.clients.create', [
            'selected' => $this->avatarUid !== '' ? Avatar::where('avatar_uid', $this->avatarUid)->first() : null,
            'matches' => $this->avatarMatches(),
            'groups' => $streams->groupBy(fn (Stream $stream) => $stream->server?->domain ?? 'Unknown server')->sortKeys(),
        ]);
    }
}
