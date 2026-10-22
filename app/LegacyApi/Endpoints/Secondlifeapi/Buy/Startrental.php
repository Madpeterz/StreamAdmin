<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Buy;

use App\LegacyApi\SlEndpoint;
use App\Models\Avatar;
use App\Models\Ban;
use App\Models\DetailRequest;
use App\Models\NotecardMail;
use App\Models\NoticeNotecard;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Stream;
use App\Services\BotService;
use App\Services\EventQueue;
use App\Services\NoticeLevels;
use App\Support\Uid;

/**
 * A client paid a vendor: rent them a free stream of the package for 1 to 4 periods, queue their
 * details and notecards, and tell the vendor what to pay the system owner.
 */
class Startrental extends SlEndpoint
{
    public function process(): void
    {
        $avatar = $this->buyer();
        $package = Package::where('package_uid', $this->input->string('packageuid'))->first();
        if ($package === null) {
            $this->failed('Unable to find package');

            return;
        }
        if ($avatar === null || Ban::where('avatar_id', $avatar->id)->exists()) {
            $this->failed('Unable to attach avatar');

            return;
        }
        if (! $this->withinStreamLimits($avatar, $package)) {
            $this->failed('Server setup rejected extra streams');

            return;
        }
        $stream = Stream::where('package_id', $package->id)->whereNull('rental_id')->where('need_work', false)->inRandomOrder()->first();
        if ($stream === null) {
            $this->failed('Unable to find a unsold stream in that package');

            return;
        }
        $server = $stream->server;
        if ($server === null) {
            $this->failed('Unable to find the server attached to the stream');

            return;
        }
        $amountPaid = (int) $this->input->intMin('amountpaid', 1);
        $cost = max(1, (int) $package->cost);
        $periods = $amountPaid > 0 && $amountPaid % $cost === 0 ? intdiv($amountPaid, $cost) : 0;
        if ($periods < 1 || $periods > 4) {
            $this->failed('Payment amount not accepted');

            return;
        }

        $hours = $package->days * 24 * $periods;
        $firstRental = ! Rental::where('avatar_id', $avatar->id)->exists();
        $rental = Rental::create([
            'rental_uid' => Uid::unique(Rental::class, 'rental_uid'),
            'avatar_id' => $avatar->id,
            'package_id' => $stream->package_id,
            'stream_id' => $stream->id,
            'start_unixtime' => time(),
            'expire_unixtime' => time() + $hours * 3600,
            'notice_id' => app(NoticeLevels::class)->forHours($hours),
            'total_amount' => $amountPaid,
        ]);
        $stream->update(['rental_id' => $rental->id]);

        $this->sendStaticNotecard($package->setup_notecard_id, $avatar);
        if ($firstRental) {
            $this->sendStaticNotecard($package->welcome_notecard_id, $avatar);
        }
        $this->recordPayment($avatar, $package, $stream, $amountPaid);
        DetailRequest::create(['rental_id' => $rental->id]);

        $systemOwner = Avatar::find($this->slConfig()->owner_avatar_id);
        $this->set('owner_payment', 0);
        if ($systemOwner === null) {
            $this->failed('Unable to load owner avatar');

            return;
        }
        if (! $this->ownerOverride) {
            $this->set('owner_payment', 1);
            $this->set('owner_payment_amount', $this->ownerShare($amountPaid));
            $this->set('owner_payment_uuid', $systemOwner->avatar_uuid);
        }
        $this->useCredits($systemOwner, $avatar, $package, $stream, $amountPaid);

        app(EventQueue::class)->push('RentalStart', $package, $avatar, $server, $stream, $rental, $amountPaid);
        if ($package->enable_group_invite) {
            app(BotService::class)->inviteToGroup($avatar);
        }
        $this->ok('Details should be with you shortly');
    }

    private function buyer(): ?Avatar
    {
        $uuid = $this->input->uuid('avatarUUID');
        $name = $this->input->string('avatarName');
        if ($uuid === null || $name === null) {
            return null;
        }

        return self::avatarFor($uuid, $name);
    }

    /** The global and per package caps on how many streams one client can rent. */
    private function withinStreamLimits(Avatar $avatar, Package $package): bool
    {
        $config = $this->slConfig();
        if (! $config->limit_streams) {
            return true;
        }
        if (Rental::where('avatar_id', $avatar->id)->count() >= $config->max_total_streams) {
            return false;
        }

        return ! $package->enforce_custom_max_streams
            || Rental::where('avatar_id', $avatar->id)->where('package_id', $package->id)->count() < $package->max_streams_in_package;
    }

    /** Queue a notecard from the notecard server (id 1 is "none"). */
    private function sendStaticNotecard(int $noticeNotecardId, Avatar $avatar): void
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
}
