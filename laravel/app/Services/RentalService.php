<?php

namespace App\Services;

use App\Models\Avatar;
use App\Models\DetailRequest;
use App\Models\Notice;
use App\Models\Region;
use App\Models\Rental;
use App\Models\Reseller;
use App\Models\SlConfig;
use App\Models\Stream;
use App\Models\Transaction;
use App\Support\Uid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Staff actions on a rental (port of the 2.0.x Client/Update and Client/Revoke controls).
 */
class RentalService
{
    public function __construct(
        private readonly NoticeLevels $noticeLevels,
        private readonly EventQueue $events,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * Gives a stream to an avatar by hand, with $days of time and no payment (port of the 2.0.x
     * Client/Create control). A free (L$0) transaction is logged against the staff member's own
     * reseller entry, in the "website" region.
     */
    public function create(Avatar $avatar, Stream $stream, int $days): Rental
    {
        if ($stream->rental_id !== null) {
            throw new RuntimeException('Stream already has a rental attached.');
        }
        $stream->loadMissing(['package', 'server']);

        return DB::transaction(function () use ($avatar, $stream, $days) {
            $rental = Rental::create([
                'rental_uid' => Uid::unique(Rental::class, 'rental_uid'),
                'avatar_id' => $avatar->id,
                'package_id' => $stream->package_id,
                'stream_id' => $stream->id,
                'start_unixtime' => time(),
                'expire_unixtime' => time() + $days * 86400,
                'notice_id' => $this->noticeLevels->forHours($days * 24),
            ]);
            $stream->update(['rental_id' => $rental->id, 'need_work' => false]);

            $staffAvatarId = Auth::user()?->avatar_id ?? SlConfig::find(1)?->owner_avatar_id;
            $reseller = Reseller::firstOrCreate(['avatar_id' => $staffAvatarId], ['allowed' => true, 'rate' => 0]);
            Transaction::create([
                'avatar_id' => $avatar->id,
                'package_id' => $stream->package_id,
                'stream_id' => $stream->id,
                'reseller_id' => $reseller->id,
                'region_id' => Region::firstOrCreate(['name' => 'website'])->id,
                'amount' => 0,
                'unixtime' => time(),
                'transaction_uid' => Uid::unique(Transaction::class, 'transaction_uid'),
                'renew' => false,
            ]);
            $this->audit->log('Client', $rental->rental_uid, '+++', $avatar->avatar_name, 'Port: '.$stream->port.' Server: '.$stream->server?->domain);

            return $rental;
        });
    }

    /**
     * Removes expired rentals for good and frees their streams for work (port of the 2.0.x
     * Client/Bulkremove control). Only rentals at the Expired notice level with no details request
     * waiting are touched; the rest are skipped.
     *
     * @param  list<string>  $rentalUids
     * @return array{removed: int, skipped: int}
     */
    public function purgeExpired(array $rentalUids): array
    {
        $removed = 0;
        $skipped = 0;
        $rentals = Rental::with(['avatar', 'package', 'stream.server'])
            ->whereIn('rental_uid', $rentalUids)
            ->get();
        $skipped += count($rentalUids) - $rentals->count();
        foreach ($rentals as $rental) {
            $removable = $rental->notice_id === NoticeLevels::EXPIRED
                && $rental->expire_unixtime->getTimestamp() <= time()
                && $rental->stream !== null
                && ! DetailRequest::where('rental_id', $rental->id)->exists();
            if (! $removable) {
                $skipped++;

                continue;
            }
            DB::transaction(function () use ($rental) {
                $this->events->push('RentalEnd', $rental->package, $rental->avatar, $rental->stream->server, $rental->stream, $rental);
                $rental->stream->update(['rental_id' => null, 'need_work' => true]);
                $rental->noticeOptOuts()->delete();
                $rental->delete();
            });
            $removed++;
        }
        if ($removed > 0) {
            $this->audit->log('Client', null, 'Bulk remove', null, $removed.' expired rentals removed');
        }

        return ['removed' => $removed, 'skipped' => $skipped];
    }

    /**
     * Queues the stream details to be sent to the client again (the details server picks them up).
     *
     * @return bool false when a request is already waiting
     */
    public function requestDetails(Rental $rental): bool
    {
        if (DetailRequest::where('rental_id', $rental->id)->exists()) {
            return false;
        }
        DetailRequest::create(['rental_id' => $rental->id]);
        $this->audit->log('Client', $rental->rental_uid, 'resend details');

        return true;
    }

    /**
     * Adds (positive) or removes (negative) time and notes it at the top of the rental message.
     */
    public function adjustTime(Rental $rental, int $hours): void
    {
        if ($hours === 0) {
            throw new RuntimeException('No adjustment given.');
        }
        $expires = $rental->expire_unixtime->getTimestamp() + $hours * 3600;
        $amount = intdiv(abs($hours), 24) > 0 ? intdiv(abs($hours), 24) : abs($hours);
        $unit = intdiv(abs($hours), 24) > 0 ? 'day' : 'hour';
        $line = sprintf('%s - %s %d %s%s', now()->format('F j, Y, g:i a'), $hours > 0 ? 'Added' : 'Removed', $amount, $unit, $amount > 1 ? 's' : '');

        $this->save($rental, [
            'expire_unixtime' => $expires,
            'notice_id' => $this->noticeLevels->forHours((int) ceil(($expires - time()) / 3600)),
            'message' => trim($line."\n".$rental->message),
        ]);
    }

    public function updateNote(Rental $rental, ?string $note): void
    {
        $this->save($rental, ['message' => $note === '' ? null : $note]);
    }

    public function transfer(Rental $rental, Avatar $to): void
    {
        $from = $rental->avatar;
        $line = sprintf(
            '%s - Transfer to: %s [%s] from %s [%s]',
            now()->format('F j, Y, g:i a'),
            $to->avatar_name,
            $to->avatar_uid,
            $from?->avatar_name,
            $from?->avatar_uid,
        );
        $this->save($rental, ['avatar_id' => $to->id, 'message' => trim($rental->message."\n".$line)]);
    }

    /**
     * Ends the rental now with no refund: the stream is released and marked as needing work.
     */
    public function revoke(Rental $rental): void
    {
        if (DetailRequest::where('rental_id', $rental->id)->exists()) {
            throw new RuntimeException('There is a pending details request for this rental.');
        }
        $stream = $rental->stream;
        if ($stream === null) {
            throw new RuntimeException('Unable to find the stream for this rental.');
        }

        DB::transaction(function () use ($rental, $stream) {
            $this->events->push('RentalEnd', $rental->package, $rental->avatar, $stream->server, $stream, $rental);
            $stream->update(['rental_id' => null, 'need_work' => true]);
            $rental->noticeOptOuts()->delete();
            $rental->delete();
            $this->audit->log('Client', (string) $rental->id, '---', $rental->avatar?->avatar_name);
        });
    }

    /**
     * The notice levels this rental skips (port of the 2.0.x Client/Noticeoptout control). The
     * Active level sends nothing, so it cannot be skipped.
     *
     * @param  list<int>  $noticeIds
     * @return array{added: int, removed: int}
     */
    public function setNoticeOptOuts(Rental $rental, array $noticeIds): array
    {
        $wanted = Notice::whereKey($noticeIds)->whereKeyNot(NoticeLevels::ACTIVE)->pluck('id');
        $current = $rental->noticeOptOuts()->pluck('notice_id');
        $add = $wanted->diff($current);
        $remove = $current->diff($wanted);
        if ($add->isEmpty() && $remove->isEmpty()) {
            return ['added' => 0, 'removed' => 0];
        }

        DB::transaction(function () use ($rental, $add, $remove) {
            $rental->noticeOptOuts()->whereIn('notice_id', $remove)->delete();
            foreach ($add as $noticeId) {
                $rental->noticeOptOuts()->create(['notice_id' => $noticeId]);
            }
            $this->audit->log('Client', $rental->rental_uid, 'updated notice opt-outs', "skipped {$add->count()}", "restored {$remove->count()}");
        });

        return ['added' => $add->count(), 'removed' => $remove->count()];
    }

    /** @param array<string, mixed> $attributes */
    private function save(Rental $rental, array $attributes): void
    {
        DB::transaction(function () use ($rental, $attributes) {
            $rental->update($attributes);
            $this->audit->logChanges('Client', $rental->rental_uid, $rental);
        });
    }
}
