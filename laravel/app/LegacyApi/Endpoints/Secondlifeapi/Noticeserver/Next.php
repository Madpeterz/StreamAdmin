<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Noticeserver;

use App\LegacyApi\SlEndpoint;
use App\Models\Notecard;
use App\Models\Notice;
use App\Models\NoticeNotecard;
use App\Models\Rental;
use App\Models\RentalNoticeOptOut;
use App\Services\BotService;
use App\Services\EventQueue;
use App\Support\Swaps;

/**
 * The owner's notice server object moves one rental to its next notice level per call and sends
 * that notice (unless the rental opted out of it). Expired rentals get the Expired notice first.
 */
class Next extends SlEndpoint
{
    public function process(): void
    {
        if (! $this->hasAccessOwner()) {
            return;
        }
        $notices = Notice::orderBy('id')->get();
        /** @var array<int, int> $byHours hours remaining => notice id, ascending */
        $byHours = $notices->pluck('id', 'hours_remaining')->all();
        ksort($byHours, SORT_NUMERIC);
        $hours = array_keys($byHours);
        $maxHours = $hours[count($hours) - 2] ?? 0; // the highest level below "Active" (999)
        $expired = $notices->firstWhere('hours_remaining', 0);

        $rentals = Rental::with(['package', 'stream.server', 'avatar'])
            ->where('expire_unixtime', '<=', time() + $maxHours * 3600)
            ->where('notice_id', '!=', $expired?->id)
            ->orderBy('id')->get();
        $this->set('status', true);
        $this->set('message', 'nowork');
        foreach ($rentals as $rental) {
            $next = $this->nextNotice($rental, $notices, $byHours, $expired);
            if ($next !== null) {
                $this->changeNotice($rental, $next);
                break;
            }
        }
    }

    /** @param array<int, int> $byHours */
    private function nextNotice(Rental $rental, $notices, array $byHours, ?Notice $expired): ?Notice
    {
        if ($rental->expire_unixtime->getTimestamp() < time()) {
            return $expired;
        }
        $hoursLeft = (int) ceil(($rental->expire_unixtime->getTimestamp() - time()) / 3600);
        $current = $notices->firstWhere('id', $rental->notice_id);
        if ($current === null) {
            return null;
        }
        $use = $byHours[$current->hours_remaining];
        foreach ($byHours as $hours => $noticeId) {
            if ($hours > 0 && $hours < 999 && $hours > $hoursLeft) {
                if ($hours < $current->hours_remaining) {
                    $use = $noticeId;
                }
                break;
            }
        }

        return $use === $current->id ? null : $notices->firstWhere('id', $use);
    }

    private function changeNotice(Rental $rental, Notice $notice): void
    {
        $this->set('message', 'Processing notice change');
        $optedOut = RentalNoticeOptOut::where('rental_id', $rental->id)->where('notice_id', $notice->id)->exists();
        $bot = app(BotService::class);
        if (! $optedOut) {
            $bot->sendMessage($rental->avatar, Swaps::forRental($notice->im_message, $rental));
        }
        $rental->update(['notice_id' => $notice->id]);
        if ($notice->hours_remaining === 0) {
            app(EventQueue::class)->push('RentalExpire', $rental->package, $rental->avatar, $rental->stream->server, $rental->stream, $rental);
        }
        $this->ok('ok');
        if ($optedOut) {
            return;
        }
        if ($notice->send_notecard && $bot->config()?->notecards) {
            Notecard::create(['rental_id' => $rental->id, 'as_notice' => true, 'notice_id' => $notice->id]);
        }
        if ($notice->notice_notecard_id <= 1) {
            return;
        }
        $static = NoticeNotecard::find($notice->notice_notecard_id);
        if ($static === null) {
            $this->failed('Unable to find static notecard!');

            return;
        }
        if (! $static->missing) {
            $this->set('send_static_notecard', $static->name);
            $this->set('send_static_notecard_to', $rental->avatar->avatar_uuid);
        }
    }
}
