<?php

namespace App\Livewire\Clients;

use App\Livewire\Concerns\SearchesAvatars;
use App\Models\Avatar;
use App\Models\Notice;
use App\Models\Rental;
use App\Models\Transaction;
use App\Services\NoticeLevels;
use App\Services\RentalService;
use App\Support\Swaps;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;
use RuntimeException;

class Show extends Component
{
    use SearchesAvatars;

    public Rental $rental;

    protected function avatarField(): string
    {
        return 'transferUid';
    }

    #[Validate('required|integer|min:0|max:3650')]
    public int $adjustDays = 0;

    #[Validate('required|integer|min:0|max:23')]
    public int $adjustHours = 0;

    #[Validate('required|in:add,remove')]
    public string $adjustDirection = 'add';

    #[Validate('nullable|string|max:5000')]
    public ?string $note = null;

    #[Validate('required|string|size:8')]
    public string $transferUid = '';

    /** @var list<string> notice level ids this rental skips */
    public array $optOuts = [];

    public function mount(Rental $rental): void
    {
        $this->rental = $rental;
        $this->note = $rental->message;
        $this->optOuts = $rental->noticeOptOuts()->pluck('notice_id')->map(fn ($id) => (string) $id)->all();
    }

    public function saveOptOuts(RentalService $rentals): void
    {
        $changes = $rentals->setNoticeOptOuts($this->rental, array_map('intval', $this->optOuts));
        Flux::modal('notice-opt-outs')->close();
        Flux::toast(
            variant: 'success',
            text: $changes['added'] + $changes['removed'] === 0 ? 'No changes.' : 'Notice opt-outs updated.',
        );
    }

    public function adjustTime(RentalService $rentals): void
    {
        $this->validateOnly('adjustDays');
        $this->validateOnly('adjustHours');
        $this->validateOnly('adjustDirection');
        $hours = $this->adjustDays * 24 + $this->adjustHours;
        if ($hours === 0) {
            $this->addError('adjustDays', 'Enter some days or hours to adjust by.');

            return;
        }
        $rentals->adjustTime($this->rental, $this->adjustDirection === 'add' ? $hours : -$hours);
        $this->reset('adjustDays', 'adjustHours', 'adjustDirection');
        $this->note = $this->rental->message;
        Flux::modal('adjust-time')->close();
        Flux::toast(variant: 'success', text: 'Time left updated.');
    }

    public function saveNote(RentalService $rentals): void
    {
        $this->validateOnly('note');
        $rentals->updateNote($this->rental, $this->note);
        Flux::toast(variant: 'success', text: 'Note saved.');
    }

    public function transfer(RentalService $rentals): void
    {
        $this->validateOnly('transferUid');
        $to = Avatar::where('avatar_uid', $this->transferUid)->first();
        if ($to === null) {
            $this->addError('transferUid', 'No avatar has that UID.');

            return;
        }
        if ($to->id === $this->rental->avatar_id) {
            $this->addError('transferUid', 'This rental already belongs to that avatar.');

            return;
        }
        $rentals->transfer($this->rental, $to);
        $this->reset('transferUid');
        $this->note = $this->rental->message;
        Flux::modal('transfer')->close();
        Flux::toast(variant: 'success', text: 'Rental transferred to '.$to->avatar_name.'.');
    }

    /** Queue the stream details to go out to the client again (the details server sends them). */
    public function resendDetails(RentalService $rentals): void
    {
        Flux::modal('details')->close();
        if (! $rentals->requestDetails($this->rental)) {
            Flux::toast(variant: 'warning', text: 'A details request for this client is already waiting. See the Outbox.');

            return;
        }
        Flux::toast(variant: 'success', text: 'Details queued. The client gets them with the next details run.');
    }

    public function revoke(RentalService $rentals): void
    {
        try {
            $rentals->revoke($this->rental);
        } catch (RuntimeException $e) {
            Flux::modal('revoke')->close();
            Flux::toast(variant: 'danger', text: $e->getMessage());

            return;
        }
        Flux::toast(variant: 'success', text: 'Rental revoked, the stream is marked as needing work.');
        $this->redirectRoute('clients.index', navigate: true);
    }

    public function render()
    {
        $this->rental->load(['avatar.ban', 'stream.server', 'package.serverType', 'notice', 'noticeOptOuts.notice']);
        $avatar = $this->rental->avatar;

        return view('livewire.clients.show', [
            'noticeLevels' => Notice::whereKeyNot(NoticeLevels::ACTIVE)->orderByDesc('hours_remaining')->get(),
            'otherRentals' => $avatar
                ? Rental::with(['stream.server', 'package'])->where('avatar_id', $avatar->id)->whereKeyNot($this->rental->id)->orderBy('expire_unixtime')->get()
                : collect(),
            'transactions' => $avatar
                ? Transaction::with('package')->where('avatar_id', $avatar->id)->latest('unixtime')->limit(8)->get()
                : collect(),
            'refundEstimate' => $this->refundEstimate(),
            'detailsText' => $this->detailsText(),
            'selectedAvatar' => $this->transferUid !== '' ? Avatar::where('avatar_uid', $this->transferUid)->first() : null,
            'matches' => $this->avatarMatches(),
        ])->title(($avatar->avatar_name ?? 'Client').' · '.$this->rental->rental_uid);
    }

    /** The stream details message as the client gets it (the package's template with the swap tags filled in). */
    private function detailsText(): ?string
    {
        $template = $this->rental->package?->template;
        if ($template === null || $this->rental->stream === null) {
            return null;
        }

        return Swaps::forRental((string) $template->detail, $this->rental);
    }

    /** L$ value of the time left, as shown on the 2.0.x revoke button. */
    private function refundEstimate(): int
    {
        $package = $this->rental->package;
        if (! $package || ! $package->days) {
            return 0;
        }
        $hoursLeft = max(0, intdiv($this->rental->expire_unixtime->getTimestamp() - time(), 3600));

        return (int) round($hoursLeft * ($package->cost / $package->days / 24));
    }
}
