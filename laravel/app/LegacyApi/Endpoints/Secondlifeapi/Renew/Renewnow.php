<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Renew;

use App\LegacyApi\SlEndpoint;
use App\Models\Avatar;
use App\Models\Ban;
use App\Models\Region;
use App\Models\Rental;
use App\Models\Reseller;
use App\Models\Transaction;
use App\Services\EventQueue;
use App\Services\NoticeLevels;
use App\Support\Swaps;
use App\Support\Uid;

/**
 * A renewal payment at a vendor or renewal point (and, through Hudapi/Rentals/Renew, the HUD):
 * adds 1 to 4 periods, records the payment and tells the object what to pay the system owner.
 * Anyone can pay for anyone's rental here (proxy renewal); the HUD only for its wearer.
 */
class Renewnow extends SlEndpoint
{
    private Rental $rental;

    private Avatar $accountOwner;

    private Avatar $payer;

    private int $amountPaid = 0;

    private int $periods = 0;

    public function process(): void
    {
        $this->renew(null, null);
    }

    /** For the HUD: renewals there are paid to the system owner and are tied to an SL transaction. */
    public function actingFor(Reseller $reseller, ?Region $region, bool $ownerOverride): static
    {
        $this->reseller = $reseller;
        $this->region = $region;
        $this->ownerOverride = $ownerOverride;

        return $this;
    }

    public function renew(?Avatar $onlyFor, ?string $slTransactionUuid): void
    {
        if (! $this->load($onlyFor) || ! $this->acceptPaymentAmount() || ! $this->withinTimeLimit()) {
            return;
        }
        $this->extendRental();
        $this->recordRenewal($slTransactionUuid);
        $this->ownerPayment();
        if ($this->rental->expire_unixtime->getTimestamp() < time()) {
            $this->ok('Payment on account but account is still in arrears');

            return;
        }
        $this->ok(sprintf(
            'Payment accepted there is now: %1$s remaining you will next need to renew %2$s',
            Swaps::timeLeft($this->rental->expire_unixtime),
            date('l jS \of F Y h:i:s A', $this->rental->expire_unixtime->getTimestamp()),
        ));
    }

    private function load(?Avatar $onlyFor): bool
    {
        $rentalUid = $this->input->string('rentalUid');
        if ($rentalUid === null || $rentalUid === '') {
            $this->failed('No rental selected for renewal');

            return false;
        }
        $rental = Rental::with(['stream.server', 'package', 'avatar'])->where('rental_uid', $rentalUid)->first();
        if ($rental === null) {
            $this->failed('Unable to find rental');

            return false;
        }
        if ($rental->stream?->server === null || $rental->package === null || $rental->avatar === null) {
            $this->failed('One or more required objects did not load');

            return false;
        }
        $this->rental = $rental;
        $this->accountOwner = $rental->avatar;
        $this->amountPaid = (int) $this->input->int('amountpaid');

        $payer = self::avatarFor($this->input->uuid('avatarUUID'), $this->input->string('avatarName'));
        if ($payer === null || Ban::whereIn('avatar_id', [$this->accountOwner->id, $payer->id])->exists()) {
            $this->failed('Unable to find avatar');

            return false;
        }
        $this->payer = $payer;
        if ($onlyFor !== null && ($this->accountOwner->id !== $onlyFor->id || $payer->id !== $onlyFor->id)) {
            $this->failed('You can not renew other peoples rentals via the hud!');

            return false;
        }

        return true;
    }

    private function acceptPaymentAmount(): bool
    {
        $cost = (int) $this->rental->package->cost;
        foreach ([1, 2, 3, 4] as $periods) {
            if ($this->amountPaid === $cost * $periods) {
                $this->periods = $periods;

                return true;
            }
        }
        $this->failed('payment not accepted (Invaild amount)');

        return false;
    }

    /** With the rental time limit on, a rental may not run more than the limit ahead. */
    private function withinTimeLimit(): bool
    {
        $config = $this->slConfig();
        if (! $config->limit_time) {
            return true;
        }
        $expires = $this->rental->expire_unixtime->getTimestamp();
        if (self::daysAhead($expires) >= $config->max_stream_time_days) {
            $this->failed('payment not accepted (currently to long remaining on rental)');

            return false;
        }
        if (self::daysAhead($expires + $this->addedSeconds()) >= $config->max_stream_time_days) {
            $this->failed('payment not accepted (would go over limit for remaining time on rental)');

            return false;
        }

        return true;
    }

    /** days left as 2.0.x counted them (a lapsed rental gives its negative seconds, always under the limit) */
    private static function daysAhead(int $expires): float
    {
        $seconds = $expires - time();

        return $seconds > 0 ? $seconds / 86400 : $seconds;
    }

    private function addedSeconds(): int
    {
        return $this->rental->package->days * 86400 * $this->periods;
    }

    private function extendRental(): void
    {
        $rental = $this->rental;
        $oldNotice = $rental->notice_id;
        $expires = $rental->expire_unixtime->getTimestamp() + $this->addedSeconds();
        $remaining = $expires - time();
        $rental->expire_unixtime = $expires;
        $rental->renewals += $this->periods;
        $rental->total_amount += $this->amountPaid;
        $rental->notice_id = $remaining > 0 ? app(NoticeLevels::class)->forHours((int) ceil($remaining / 3600)) : NoticeLevels::EXPIRED;
        $rental->save();

        // "RentalRenew" when this payment brings a lapsed rental back; "RentalRenewAny" for every other renewal
        $event = $oldNotice === NoticeLevels::EXPIRED && $rental->notice_id !== NoticeLevels::EXPIRED && $remaining > 0 ? 'RentalRenew' : 'RentalRenewAny';
        app(EventQueue::class)->push($event, $rental->package, $this->accountOwner, $rental->stream->server, $rental->stream, $rental, $this->amountPaid, $this->payer);
    }

    private function recordRenewal(?string $slTransactionUuid): void
    {
        Transaction::create([
            'avatar_id' => $this->payer->id,
            'package_id' => $this->rental->package_id,
            'stream_id' => $this->rental->stream_id,
            'reseller_id' => $this->reseller->id,
            'region_id' => $this->region?->id,
            'amount' => $this->amountPaid,
            'unixtime' => time(),
            'transaction_uid' => Uid::unique(Transaction::class, 'transaction_uid'),
            'renew' => true,
            'via_hud' => $slTransactionUuid !== null,
            'sl_transaction_uuid' => $slTransactionUuid,
            'target_avatar_id' => $this->payer->id !== $this->accountOwner->id ? $this->accountOwner->id : null,
        ]);
    }

    /** Credit only counts when paying for your own rental; resellers pass on all but their rate. */
    private function ownerPayment(): void
    {
        $this->set('owner_payment', 0);
        $systemOwner = Avatar::find($this->slConfig()->owner_avatar_id);
        if ($systemOwner === null) {
            $this->failed('Unable to find system owner avatar');

            return;
        }
        if ($this->payer->id === $this->accountOwner->id) {
            $this->useCredits($systemOwner, $this->payer, $this->rental->package, $this->rental->stream, $this->amountPaid, true);
        }
        if ($this->ownerOverride) {
            return;
        }
        $this->set('owner_payment', 1);
        $this->set('owner_payment_amount', $this->ownerShare($this->amountPaid));
        $this->set('owner_payment_uuid', $systemOwner->avatar_uuid);
    }
}
