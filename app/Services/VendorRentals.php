<?php

namespace App\Services;

use App\Api\ApiException;
use App\Api\Caller;
use App\Models\Avatar;
use App\Models\Ban;
use App\Models\DetailRequest;
use App\Models\NotecardMail;
use App\Models\NoticeNotecard;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Reseller;
use App\Models\SlConfig;
use App\Models\Stream;
use App\Models\Transaction;
use App\Support\Swaps;
use App\Support\Uid;

/**
 * What vendors, renewal points and the renter HUD do with rentals (port of the 2.0.x Buy, Renew,
 * Proxyrenew, Details and Client endpoints). Every method either returns the reply or throws an
 * ApiException; the caller's request is rolled back in that case.
 *
 * Money rules: a payment is 1 to 4 periods of the package cost; a reseller passes on all but their
 * rate, the system owner's own objects pass nothing on, and prepaid credit is refunded against a
 * payment made at one of the system owner's objects.
 */
class VendorRentals
{
    public function __construct(
        private readonly BotService $bot,
        private readonly EventQueue $events,
        private readonly NoticeLevels $levels,
    ) {}

    /**
     * A client paid a vendor: rent them a free stream of the package for 1 to 4 periods.
     *
     * @return array{message: string, data: array<string, mixed>}
     */
    public function start(Caller $caller, string $packageUid, Avatar $buyer, int $amountPaid): array
    {
        $package = Package::where('package_uid', $packageUid)->first()
            ?? throw ApiException::notFound('Unable to find package');
        if (Ban::where('avatar_id', $buyer->id)->exists()) {
            throw ApiException::rejected('This avatar is banned');
        }
        $this->checkStreamLimits($buyer, $package);
        $stream = Stream::where('package_id', $package->id)->whereNull('rental_id')->where('need_work', false)->inRandomOrder()->first()
            ?? throw ApiException::rejected('Nothing in stock for that package');
        $server = $stream->server ?? throw ApiException::rejected('Unable to find the server attached to the stream');
        $systemOwner = $this->systemOwner();

        $cost = max(1, (int) $package->cost);
        $periods = $amountPaid > 0 && $amountPaid % $cost === 0 ? intdiv($amountPaid, $cost) : 0;
        if ($periods < 1 || $periods > 4) {
            throw ApiException::rejected('Payment amount not accepted');
        }

        $hours = $package->days * 24 * $periods;
        $firstRental = ! Rental::where('avatar_id', $buyer->id)->exists();
        $rental = Rental::create([
            'rental_uid' => Uid::unique(Rental::class, 'rental_uid'),
            'avatar_id' => $buyer->id,
            'package_id' => $stream->package_id,
            'stream_id' => $stream->id,
            'start_unixtime' => time(),
            'expire_unixtime' => time() + $hours * 3600,
            'notice_id' => $this->levels->forHours($hours),
            'total_amount' => $amountPaid,
        ]);
        $stream->update(['rental_id' => $rental->id]);

        $this->queueStaticNotecard($package->setup_notecard_id, $buyer);
        if ($firstRental) {
            $this->queueStaticNotecard($package->welcome_notecard_id, $buyer);
        }
        $this->recordPayment($caller, $buyer, $package, $stream, $amountPaid);
        DetailRequest::create(['rental_id' => $rental->id]);

        $credit = $this->useCredits($caller, $systemOwner, $buyer, $package, $stream, $amountPaid);
        $this->events->push('RentalStart', $package, $buyer, $server, $stream, $rental, $amountPaid);
        if ($package->enable_group_invite) {
            $this->bot->inviteToGroup($buyer);
        }

        return [
            'message' => 'Details should be with you shortly',
            'data' => ['rentalUid' => $rental->rental_uid, 'ownerPayment' => $this->ownerPayment($caller, $systemOwner, $amountPaid), 'credit' => $credit],
        ];
    }

    /**
     * A renewal payment at a vendor, renewal point or the HUD: 1 to 4 periods. For the HUD $onlyFor
     * is the wearer, who can only pay their own rentals; the HUD takes the payment in SL on behalf
     * of the system owner, so it is recorded against the owner's reseller entry.
     *
     * @return array{message: string, data: array<string, mixed>}
     */
    public function renew(Caller $caller, string $rentalUid, int $amountPaid, Avatar $payer, ?string $slTransactionKey = null, ?Avatar $onlyFor = null): array
    {
        $rental = Rental::with(['stream.server', 'package', 'avatar'])->where('rental_uid', $rentalUid)->first()
            ?? throw ApiException::notFound('Unable to find rental');
        if ($rental->stream?->server === null || $rental->package === null || $rental->avatar === null) {
            throw ApiException::rejected('One or more required objects did not load');
        }
        $owner = $rental->avatar;
        if (Ban::whereIn('avatar_id', [$owner->id, $payer->id])->exists()) {
            throw ApiException::rejected('This avatar is banned');
        }
        if ($onlyFor !== null && ($owner->id !== $onlyFor->id || $payer->id !== $onlyFor->id)) {
            throw ApiException::forbidden('You can not renew other peoples rentals via the hud');
        }
        $periods = $this->periodsFor($rental->package, $amountPaid);
        $this->checkTimeLimit($rental, $periods);

        $reseller = $slTransactionKey !== null
            ? Reseller::firstOrCreate(['avatar_id' => $this->systemOwner()->id], ['allowed' => true, 'rate' => 0])
            : $caller->reseller;
        $ownerObject = $slTransactionKey !== null || $caller->isOwner();

        $oldNotice = $rental->notice_id;
        $expires = $rental->expire_unixtime->getTimestamp() + $rental->package->days * 86400 * $periods;
        $remaining = $expires - time();
        $rental->expire_unixtime = $expires;
        $rental->renewals += $periods;
        $rental->total_amount += $amountPaid;
        $rental->notice_id = $remaining > 0 ? $this->levels->forHours((int) ceil($remaining / 3600)) : NoticeLevels::EXPIRED;
        $rental->save();

        // "RentalRenew" when this payment brings a lapsed rental back; "RentalRenewAny" for every other renewal
        $event = $oldNotice === NoticeLevels::EXPIRED && $rental->notice_id !== NoticeLevels::EXPIRED && $remaining > 0 ? 'RentalRenew' : 'RentalRenewAny';
        $this->events->push($event, $rental->package, $owner, $rental->stream->server, $rental->stream, $rental, $amountPaid, $payer);

        Transaction::create([
            'avatar_id' => $payer->id,
            'package_id' => $rental->package_id,
            'stream_id' => $rental->stream_id,
            'reseller_id' => $reseller->id,
            'region_id' => $caller->region?->id,
            'amount' => $amountPaid,
            'unixtime' => time(),
            'transaction_uid' => Uid::unique(Transaction::class, 'transaction_uid'),
            'renew' => true,
            'via_hud' => $slTransactionKey !== null,
            'sl_transaction_uuid' => $slTransactionKey,
            'target_avatar_id' => $payer->id !== $owner->id ? $owner->id : null,
        ]);

        $systemOwner = $this->systemOwner();
        // credit only counts when paying for your own rental
        $credit = $payer->id === $owner->id
            ? $this->useCredits($caller, $systemOwner, $payer, $rental->package, $rental->stream, $amountPaid, true, $reseller)
            : ['returned' => 0, 'remaining' => 0];
        $arrears = $remaining < 0;

        return [
            'message' => $arrears ? 'Payment on account but account is still in arrears' : 'Payment accepted',
            'data' => [
                'expiresAt' => $expires,
                'timeLeft' => Swaps::timeLeft($rental->expire_unixtime),
                'inArrears' => $arrears,
                'ownerPayment' => $this->ownerPayment($caller, $systemOwner, $amountPaid, $ownerObject, $reseller),
                'credit' => $credit,
            ],
        ];
    }

    /**
     * What renewing a rental costs: price per period, who to pay and how the period reads.
     * For the HUD $onlyFor is the wearer.
     *
     * @return array<string, mixed>
     */
    public function cost(string $rentalUid, ?Avatar $onlyFor = null): array
    {
        $rental = Rental::where('rental_uid', $rentalUid)->first() ?? throw ApiException::notFound('Unable to find rental');
        $package = $rental->package ?? throw ApiException::rejected('Unable to load package');
        if ($onlyFor !== null && $onlyFor->id !== $rental->avatar_id) {
            throw ApiException::forbidden('Unable to renew someone elses rental via your hud, please use the proxy pay');
        }
        [$word, $daysPerStack] = match (true) {
            $package->days % 7 === 0 => ['Week', 7],
            $package->days % 31 === 0, $package->days % 30 === 0 => ['Month', 31],
            default => ['Day', 1],
        };

        return [
            'systemOwnerKey' => $this->systemOwner()->avatar_uuid,
            'cost' => (int) $package->cost,
            'word' => $word,
            'stackSize' => (int) ceil($package->days / $daysPerStack),
            'timeLeft' => Swaps::timeLeft($rental->expire_unixtime),
        ];
    }

    /**
     * An avatar's rentals for a renewal point, newest first.
     *
     * @return array{message: string, data: array<string, mixed>}
     */
    public function rentalsOf(Avatar $avatar): array
    {
        if (Ban::where('avatar_id', $avatar->id)->exists()) {
            throw ApiException::notFound('Unable to find avatar');
        }
        $rentals = $avatar->rentals()->with('stream')->orderByDesc('id')->get();
        if ($rentals->isEmpty()) {
            return ['message' => 'No active rentals', 'data' => ['client' => $avatar->avatar_name, 'rentals' => []]];
        }
        $list = $rentals->filter(fn ($rental) => $rental->stream !== null)
            ->map(fn ($rental) => ['rentalUid' => $rental->rental_uid, 'port' => (int) $rental->stream->port, 'timeLeft' => Swaps::timeLeft($rental->expire_unixtime)])
            ->values()->all();
        if ($list === []) {
            throw ApiException::rejected('No streams are linked to those rentals. A stream that is busy with an API request is hidden from this list');
        }

        return ['message' => 'Client account: '.$avatar->avatar_name, 'data' => ['client' => $avatar->avatar_name, 'rentals' => $list]];
    }

    /** Queue a rental's stream details to be sent again. For the HUD $onlyFor is the wearer. */
    public function sendDetails(string $rentalUid, ?Avatar $onlyFor = null): string
    {
        $rental = Rental::where('rental_uid', $rentalUid)->first() ?? throw ApiException::notFound('Unable to find rental');
        if ($onlyFor !== null && $rental->avatar_id !== $onlyFor->id) {
            throw ApiException::forbidden("You can not request someone else's rental details via the hud");
        }
        if (DetailRequest::where('rental_id', $rental->id)->exists()) {
            return 'You already have a pending details request - please wait for it or contact support';
        }
        DetailRequest::create(['rental_id' => $rental->id]);

        return 'Details request accepted, it should be with you shortly';
    }

    public function hasRental(?Avatar $avatar): bool
    {
        return $avatar !== null && Rental::where('avatar_id', $avatar->id)->exists();
    }

    /** The global and per package caps on how many streams one client can rent. */
    private function checkStreamLimits(Avatar $buyer, Package $package): void
    {
        $config = SlConfig::findOrFail(1);
        if (! $config->limit_streams) {
            return;
        }
        $total = Rental::where('avatar_id', $buyer->id)->count();
        $inPackage = Rental::where('avatar_id', $buyer->id)->where('package_id', $package->id)->count();
        if ($total >= $config->max_total_streams || ($package->enforce_custom_max_streams && $inPackage >= $package->max_streams_in_package)) {
            throw ApiException::rejected('This client has reached the stream limit');
        }
    }

    /** With the rental time limit on, a rental may not run more than the limit ahead. */
    private function checkTimeLimit(Rental $rental, int $periods): void
    {
        $config = SlConfig::findOrFail(1);
        if (! $config->limit_time) {
            return;
        }
        $expires = $rental->expire_unixtime->getTimestamp();
        if ($this->daysAhead($expires) >= $config->max_stream_time_days) {
            throw ApiException::rejected('Payment not accepted: there is already too much time on this rental');
        }
        if ($this->daysAhead($expires + $rental->package->days * 86400 * $periods) >= $config->max_stream_time_days) {
            throw ApiException::rejected('Payment not accepted: it would go over the time limit for this rental');
        }
    }

    /** days left as 2.0.x counted them (a lapsed rental gives its negative seconds, always under the limit) */
    private function daysAhead(int $expires): float
    {
        $seconds = $expires - time();

        return $seconds > 0 ? $seconds / 86400 : $seconds;
    }

    private function periodsFor(Package $package, int $amountPaid): int
    {
        $cost = (int) $package->cost;
        foreach ([1, 2, 3, 4] as $periods) {
            if ($amountPaid === $cost * $periods) {
                return $periods;
            }
        }

        throw ApiException::rejected('Payment not accepted (invalid amount)');
    }

    private function systemOwner(): Avatar
    {
        return Avatar::find(SlConfig::findOrFail(1)->owner_avatar_id) ?? throw ApiException::rejected('Unable to load the system owner avatar');
    }

    /** Queue a notecard from the notecard server (id 1 is "none"). */
    private function queueStaticNotecard(int $noticeNotecardId, Avatar $avatar): void
    {
        if ($noticeNotecardId <= 1) {
            return;
        }
        $notecard = NoticeNotecard::find($noticeNotecardId);
        if ($notecard === null || $notecard->missing) {
            return;
        }
        NotecardMail::create(['avatar_id' => $avatar->id, 'notice_notecard_id' => $notecard->id]);
    }

    private function recordPayment(Caller $caller, Avatar $avatar, Package $package, Stream $stream, int $amount, bool $renewal = false, ?string $notes = null, ?Reseller $reseller = null): Transaction
    {
        return Transaction::create([
            'avatar_id' => $avatar->id,
            'package_id' => $package->id,
            'stream_id' => $stream->id,
            'reseller_id' => ($reseller ?? $caller->reseller)->id,
            'region_id' => $caller->region?->id,
            'amount' => $amount,
            'unixtime' => time(),
            'notes' => $notes,
            'transaction_uid' => Uid::unique(Transaction::class, 'transaction_uid'),
            'renew' => $renewal,
        ]);
    }

    /**
     * Prepaid credit (from Marketplace coupons) is refunded against a payment made at one of the
     * system owner's objects: the vendor pays back up to the amount paid, and a negative
     * transaction records it.
     *
     * @return array{returned: int, remaining: int}
     */
    private function useCredits(Caller $caller, Avatar $systemOwner, Avatar $avatar, Package $package, Stream $stream, int $amountPaid, bool $renewal = false, ?Reseller $reseller = null): array
    {
        $reseller ??= $caller->reseller;
        if ($reseller?->avatar_id !== $systemOwner->id || $avatar->credits <= 0) {
            return ['returned' => 0, 'remaining' => 0];
        }
        $refund = min($avatar->credits, $amountPaid);
        $avatar->update(['credits' => $avatar->credits - $refund]);
        $this->recordPayment($caller, $avatar, $package, $stream, 0 - $refund, $renewal, 'refund of prepaid credits', $reseller);

        return ['returned' => $refund, 'remaining' => (int) $avatar->credits];
    }

    /**
     * What the object has to pay the system owner: nothing for the owner's own objects, otherwise
     * all but the reseller's rate (leaving them at least 1 L$ when possible).
     *
     * @return array{required: bool, amount: int, key: string|null}
     */
    private function ownerPayment(Caller $caller, Avatar $systemOwner, int $amountPaid, ?bool $ownerObject = null, ?Reseller $reseller = null): array
    {
        if ($ownerObject ?? $caller->isOwner()) {
            return ['required' => false, 'amount' => 0, 'key' => null];
        }
        $rate = (int) ($reseller ?? $caller->reseller)->rate;
        $share = $amountPaid;
        if ($rate > 0) {
            $resellerCut = (int) floor($amountPaid / 100 * $rate);
            $share = $amountPaid - $resellerCut;
            if ($resellerCut < 1 && $share >= 2) {
                $share--;
            }
        }

        return ['required' => true, 'amount' => $share, 'key' => $systemOwner->avatar_uuid];
    }
}
