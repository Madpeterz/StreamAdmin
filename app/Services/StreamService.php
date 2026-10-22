<?php

namespace App\Services;

use App\Models\Rental;
use App\Models\Stream;
use App\Models\Transaction;
use App\Support\Uid;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Staff actions on streams (port of the 2.0.x Stream controls).
 */
class StreamService
{
    public function __construct(
        private readonly NoticeLevels $noticeLevels,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Stream
    {
        return DB::transaction(function () use ($attributes) {
            $stream = Stream::create($attributes + ['stream_uid' => Uid::unique(Stream::class, 'stream_uid')]);
            $this->audit->log('Stream', $stream->stream_uid, '+++', (string) $stream->port, $stream->server?->domain);

            return $stream;
        });
    }

    /**
     * Saving a stream marks it as ready (2.0.x behaviour). A rented stream moved to another
     * package takes its rental with it.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Stream $stream, array $attributes): void
    {
        DB::transaction(function () use ($stream, $attributes) {
            $stream->update($attributes + ['need_work' => false]);
            $this->audit->logChanges('Stream', $stream->stream_uid, $stream);
            if ($stream->rental && $stream->rental->package_id !== $stream->package_id) {
                $stream->rental->update(['package_id' => $stream->package_id]);
            }
        });
    }

    public function delete(Stream $stream): void
    {
        if ($stream->rental_id !== null) {
            throw new RuntimeException('This stream is rented. Revoke the client first.');
        }
        DB::transaction(function () use ($stream) {
            Transaction::where('stream_id', $stream->id)->update(['stream_id' => null]);
            $this->audit->log('Stream', $stream->stream_uid, '---', (string) $stream->port, $stream->server?->domain);
            $stream->delete();
        });
    }

    /**
     * Gives a free stream back to the avatar who last bought it. The restored rental has no time
     * left, so the client renews to keep it.
     */
    public function restore(Stream $stream): Rental
    {
        if ($stream->rental_id !== null) {
            throw new RuntimeException('This stream is already rented.');
        }
        $lastPurchase = Transaction::with('avatar')
            ->where('stream_id', $stream->id)
            ->where('renew', false)
            ->whereNotNull('avatar_id')
            ->latest('unixtime')
            ->first();
        if ($lastPurchase?->avatar === null) {
            throw new RuntimeException('No purchase found for this stream, so there is nobody to restore it to.');
        }

        return DB::transaction(function () use ($stream, $lastPurchase) {
            $rental = Rental::create([
                'avatar_id' => $lastPurchase->avatar_id,
                'stream_id' => $stream->id,
                'package_id' => $stream->package_id,
                'notice_id' => $this->noticeLevels->forHours(0),
                'start_unixtime' => time(),
                'expire_unixtime' => time(),
                'message' => 'Restored rental!',
                'rental_uid' => Uid::unique(Rental::class, 'rental_uid'),
            ]);
            $stream->update(['rental_id' => $rental->id, 'need_work' => false]);
            $this->audit->log('Stream', $stream->stream_uid, 'restored', $lastPurchase->avatar->avatar_name,
                'Port: '.$stream->port.' Server: '.$stream->server?->domain);

            return $rental;
        });
    }

    /**
     * Needs work -> ready after new passwords were set on the streaming server. The DJ password
     * must change; the admin password must change unless the server has none ("none" or "N/A").
     */
    public function markReady(Stream $stream, string $adminPassword, string $djPassword): void
    {
        if ($stream->rental_id !== null || ! $stream->need_work) {
            throw new RuntimeException('Port '.$stream->port.' does not need work.');
        }
        if ($djPassword === $stream->dj_password) {
            throw new RuntimeException('Port '.$stream->port.': the DJ password has not been changed.');
        }
        if ($adminPassword === $stream->admin_password && ! in_array($adminPassword, ['none', 'N/A'], true)) {
            throw new RuntimeException('Port '.$stream->port.': the admin password has not been changed.');
        }
        DB::transaction(function () use ($stream, $adminPassword, $djPassword) {
            $stream->update(['admin_password' => $adminPassword, 'dj_password' => $djPassword, 'need_work' => false]);
            $this->audit->logChanges('Stream', $stream->stream_uid, $stream);
        });
    }
}
