<?php

namespace App\Services;

use App\Api\ApiException;
use App\Api\Signature;
use App\Models\Avatar;
use App\Models\BotCommand;
use App\Models\Notecard;
use App\Models\NotecardMail;
use App\Models\Notice;
use App\Models\NoticeNotecard;
use App\Models\Package;
use App\Models\Rental;
use App\Models\RentalNoticeOptOut;
use App\Models\SlConfig;
use App\Support\Swaps;

/**
 * Notices and notecards for the owner's notice server object and the bot (port of the 2.0.x
 * Noticeserver and Bot endpoints).
 */
class NoticeService
{
    public function __construct(private readonly BotService $bot, private readonly EventQueue $events) {}

    /**
     * Moves one rental to its next notice level and sends that notice (unless the rental opted out
     * of it). Expired rentals get the Expired notice first.
     *
     * @return array{message: string, data: array<string, mixed>}
     */
    public function nextNotice(): array
    {
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
        foreach ($rentals as $rental) {
            $next = $this->noticeFor($rental, $notices, $byHours, $expired);
            if ($next !== null) {
                return $this->changeNotice($rental, $next);
            }
        }

        return ['message' => 'nowork', 'data' => ['changed' => false]];
    }

    /**
     * The oldest static notecard to give. Entries that can never be sent are dropped for good and
     * reported, so the queue never stalls on them.
     *
     * @return array{message: string, data: array<string, mixed>}
     */
    public function nextNotecardMail(): array
    {
        $mail = NotecardMail::with('avatar')->orderBy('id')->first();
        if ($mail === null) {
            return ['message' => 'nowork', 'data' => ['notecard' => null, 'recheck' => false]];
        }
        $notecard = NoticeNotecard::find($mail->notice_notecard_id);
        $problem = match (true) {
            $notecard === null => 'Unable to load notecard from db',
            $notecard->missing => 'Missing notecard please recheck',
            $notecard->id <= 1 => 'Invalid notecard please recheck',
            $mail->avatar === null => 'Unable to load avatar from db',
            default => null,
        };
        $mail->delete();
        if ($problem !== null) {
            return ['message' => $problem, 'data' => ['notecard' => null, 'recheck' => true, 'dropped' => true]];
        }

        return ['message' => 'send', 'data' => ['notecard' => ['name' => $notecard->name, 'avatarKey' => $mail->avatar->avatar_uuid], 'recheck' => true]];
    }

    /**
     * The notecards in the notice server's inventory. Known ones are marked found or missing, new
     * ones added, and notecards that were already missing and are not used by a notice or queued
     * are removed (packages fall back to none).
     *
     * @param  list<string>  $inventory
     * @return array{message: string, data: array<string, mixed>}
     */
    public function syncNotecards(array $inventory): array
    {
        // found again
        NoticeNotecard::whereKeyNot(1)->where('missing', true)->whereIn('name', $inventory)->update(['missing' => false]);

        // remove notecards that were missing before this sync and are not used
        $used = Notice::pluck('notice_notecard_id')->merge(NotecardMail::pluck('notice_notecard_id'))->unique();
        $purge = NoticeNotecard::whereKeyNot(1)->where('missing', true)->whereNotIn('id', $used)->pluck('id');
        if ($purge->isNotEmpty()) {
            Package::whereIn('welcome_notecard_id', $purge)->update(['welcome_notecard_id' => 1]);
            Package::whereIn('setup_notecard_id', $purge)->update(['setup_notecard_id' => 1]);
            NoticeNotecard::whereKey($purge)->delete();
        }

        // missing now
        NoticeNotecard::whereKeyNot(1)->whereNotIn('name', $inventory)->update(['missing' => true]);

        // new
        $known = NoticeNotecard::whereKeyNot(1)->pluck('name')->all();
        foreach (array_unique($inventory) as $name) {
            if ($name !== 'none' && $name !== '' && ! in_array($name, $known, true)) {
                NoticeNotecard::create(['name' => $name, 'missing' => false]);
                $known[] = $name;
            }
        }

        return ['message' => 'ok', 'data' => ['purged' => $purge->count()]];
    }

    /**
     * Whether the bot should fetch a dynamic notecard: queues the fetch request when notecards are
     * waiting and none is queued yet.
     *
     * @return array{message: string, data: array<string, mixed>}
     */
    public function syncBotNotecards(): array
    {
        $config = $this->bot->config();
        if (! $config?->notecards) {
            throw ApiException::disabled('Notecards are not enabled on the bot');
        }
        if (Avatar::find($config->avatar_id)?->avatar_uuid === null) {
            throw ApiException::rejected('Unable to load the bot avatar');
        }
        if (strlen((string) SlConfig::findOrFail(1)->http_inbound_secret) < Signature::MIN_SECRET_LENGTH) {
            throw ApiException::rejected('The bot key is too short, reissue the keys');
        }
        if (! Notecard::exists()) {
            return ['message' => 'nowork', 'data' => ['state' => 'nowork']];
        }
        $queued = BotCommand::where('command', 'FetchNextNotecard')->exists();
        $this->bot->fetchNextNotecard();

        return ['message' => $queued ? 'already requested' : 'requested', 'data' => ['state' => 'requested']];
    }

    /** @param array<int, int> $byHours */
    private function noticeFor(Rental $rental, $notices, array $byHours, ?Notice $expired): ?Notice
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

    /** @return array{message: string, data: array<string, mixed>} */
    private function changeNotice(Rental $rental, Notice $notice): array
    {
        $optedOut = RentalNoticeOptOut::where('rental_id', $rental->id)->where('notice_id', $notice->id)->exists();
        if (! $optedOut) {
            $this->bot->sendMessage($rental->avatar, Swaps::forRental($notice->im_message, $rental));
        }
        $rental->update(['notice_id' => $notice->id]);
        if ($notice->hours_remaining === 0) {
            $this->events->push('RentalExpire', $rental->package, $rental->avatar, $rental->stream->server, $rental->stream, $rental);
        }
        $data = ['changed' => true, 'rentalUid' => $rental->rental_uid, 'notice' => $notice->name, 'staticNotecard' => null];
        if ($optedOut) {
            return ['message' => 'ok', 'data' => $data];
        }
        if ($notice->send_notecard && $this->bot->config()?->notecards) {
            Notecard::create(['rental_id' => $rental->id, 'as_notice' => true, 'notice_id' => $notice->id]);
        }
        if ($notice->notice_notecard_id > 1) {
            $static = NoticeNotecard::find($notice->notice_notecard_id)
                ?? throw ApiException::rejected('Unable to find the static notecard');
            if (! $static->missing) {
                $data['staticNotecard'] = ['name' => $static->name, 'avatarKey' => $rental->avatar->avatar_uuid];
            }
        }

        return ['message' => 'ok', 'data' => $data];
    }
}
