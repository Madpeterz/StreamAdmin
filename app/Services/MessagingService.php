<?php

namespace App\Services;

use App\Models\Avatar;
use App\Models\Notice;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Template;
use App\Support\Swaps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Staff actions for templates, notice levels and bulk messages
 * (port of the 2.0.x Template, Notice and Outbox controls).
 */
class MessagingService
{
    /** messages.message column size */
    public const MAX_MESSAGE = 900;

    public function __construct(private readonly AuditLogger $audit, private readonly BotService $bot) {}

    /** @param array{name: string, detail: string, notecard_detail: string} $attributes */
    public function saveTemplate(?Template $template, array $attributes): Template
    {
        return DB::transaction(function () use ($template, $attributes) {
            if ($template === null) {
                $template = Template::create($attributes);
                $this->audit->log('Template', (string) $template->id, '+++', null, $template->name);

                return $template;
            }
            $template->update($attributes);
            $this->audit->logChanges('Template', (string) $template->id, $template);

            return $template;
        });
    }

    public function deleteTemplate(Template $template): void
    {
        $used = Package::where('template_id', $template->id)->count();
        if ($used > 0) {
            throw new RuntimeException("This template is used by {$used} ".str('package')->plural($used).'; move them to another template first.');
        }
        DB::transaction(function () use ($template) {
            $this->audit->log('Template', (string) $template->id, '---', $template->name);
            $template->delete();
        });
    }

    /** @param array<string, mixed> $attributes */
    public function saveNotice(?Notice $notice, array $attributes): Notice
    {
        return DB::transaction(function () use ($notice, $attributes) {
            if ($notice === null) {
                $notice = Notice::create($attributes);
                $this->audit->log('Notice', (string) $notice->id, '+++', null, $notice->name);

                return $notice;
            }
            // the Expired and Active levels keep their trigger hours
            if ($this->isProtected($notice)) {
                unset($attributes['hours_remaining']);
            }
            $notice->update($attributes);
            $this->audit->logChanges('Notice', (string) $notice->id, $notice);

            return $notice;
        });
    }

    public function isProtected(Notice $notice): bool
    {
        return in_array($notice->id, [NoticeLevels::EXPIRED, NoticeLevels::ACTIVE], true);
    }

    /**
     * Rentals at the removed level move to $transferTo. Clients' opt-outs of the removed level go
     * with it (2.0.x refused to remove a level anyone had opted out of).
     *
     * @return int rentals moved
     */
    public function deleteNotice(Notice $notice, Notice $transferTo): int
    {
        if ($this->isProtected($notice)) {
            throw new RuntimeException('The Expired and Active notice levels cannot be removed.');
        }
        if ($transferTo->is($notice)) {
            throw new RuntimeException('Choose a different notice level to move clients to.');
        }
        $pending = DB::table('notecards')->where('notice_id', $notice->id)->count();
        if ($pending > 0) {
            throw new RuntimeException("{$pending} notice ".str('notecard')->plural($pending).' for this level are still waiting to be sent; try again once the outbox has sent them.');
        }

        return DB::transaction(function () use ($notice, $transferTo) {
            $moved = $notice->rentals()->update(['notice_id' => $transferTo->id]);
            DB::table('rental_notice_opt_outs')->where('notice_id', $notice->id)->delete();
            $this->audit->log('Notice', (string) $notice->id, '---', $notice->name, $moved > 0 ? "{$moved} clients moved to {$transferTo->name}" : null);
            $notice->delete();

            return $moved;
        });
    }

    /**
     * Who a bulk message goes to: one rental per avatar (their oldest matching one, whose stream,
     * package and server fill the swap tags), banned avatars left out.
     *
     * @return Collection<int, Rental>
     */
    public function recipients(string $source, ?int $sourceId = null): Collection
    {
        $query = Rental::query()->whereDoesntHave('avatar.ban')->with(['avatar', 'package', 'stream.server'])->orderBy('id');
        match ($source) {
            'clients' => null,
            'notice' => $query->where('notice_id', $sourceId),
            'package' => $query->where('package_id', $sourceId),
            'server' => $query->whereHas('stream', fn (Builder $stream) => $stream->where('server_id', $sourceId)),
            'rental' => $query->whereKey($sourceId),
            default => throw new RuntimeException('Unknown recipient source.'),
        };

        return $query->get()->unique('avatar_id')->values();
    }

    /** $message for one avatar: their oldest rental fills the swap tags, or only the name tags without one. */
    public function textForAvatar(string $message, Avatar $avatar): string
    {
        $rental = $avatar->rentals()->orderBy('id')->first();

        return $rental ? Swaps::forRental($message, $rental) : Swaps::forAvatar($message, $avatar);
    }

    /** Send $message (with swap tags) to one chosen avatar, rental or not. */
    public function sendToAvatar(string $message, Avatar $avatar): void
    {
        if ($avatar->ban()->exists()) {
            throw new RuntimeException($avatar->avatar_name.' is banned; banned avatars are not sent messages.');
        }
        $text = $this->textForAvatar($message, $avatar);
        if (mb_strlen($text) > self::MAX_MESSAGE) {
            throw new RuntimeException('With the swap tags filled in, the message is '.mb_strlen($text).' characters; the most that can be sent is '.self::MAX_MESSAGE.'.');
        }
        DB::transaction(function () use ($avatar, $text) {
            $this->bot->sendMessage($avatar, $text);
            $this->audit->log('Outbox', null, 'send mail', 'total avatars', '1');
        });
    }

    /**
     * Send $message (with swap tags) to the given rentals' avatars. All or nothing: every message is
     * built and checked before any is queued.
     *
     * @param  Collection<int, Rental>  $rentals
     * @return int avatars messaged
     */
    public function sendBulk(string $message, Collection $rentals): int
    {
        $outgoing = $rentals->unique('avatar_id')->map(fn (Rental $rental) => [$rental, Swaps::forRental($message, $rental)]);
        foreach ($outgoing as [$rental, $text]) {
            if (mb_strlen($text) > self::MAX_MESSAGE) {
                throw new RuntimeException('With the swap tags filled in, the message for '.$rental->avatar->avatar_name.' is '.mb_strlen($text).' characters; the most that can be sent is '.self::MAX_MESSAGE.'.');
            }
        }

        return DB::transaction(function () use ($outgoing) {
            foreach ($outgoing as [$rental, $text]) {
                $this->bot->sendMessage($rental->avatar, $text);
            }
            $this->audit->log('Outbox', null, 'send mail', 'total avatars', (string) $outgoing->count());

            return $outgoing->count();
        });
    }
}
