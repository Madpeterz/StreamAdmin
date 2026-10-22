<?php

namespace App\Livewire\Streams;

use App\Models\Stream;
use App\Services\StreamService;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

/**
 * Bulk "needs work -> ready" after a rental ends (port of the 2.0.x Stream/Bulkupdate page):
 * enter the new passwords set on the streaming server for each stream.
 */
#[Title('Needs work')]
class NeedsWork extends Component
{
    /** @var array<int, array{admin: string, dj: string}> keyed by stream id */
    public array $passwords = [];

    public function generate(int $streamId): void
    {
        $this->passwords[$streamId] = ['admin' => Str::password(12, symbols: false), 'dj' => Str::password(12, symbols: false)];
    }

    public function markReady(StreamService $streams): void
    {
        $done = 0;
        $problems = [];
        foreach ($this->passwords as $streamId => $new) {
            if (trim($new['dj'] ?? '') === '') {
                continue;
            }
            $stream = Stream::find($streamId);
            if ($stream === null) {
                continue;
            }
            $admin = trim($new['admin'] ?? '') !== '' ? trim($new['admin']) : $stream->admin_password;
            try {
                $streams->markReady($stream, $admin, trim($new['dj']));
                unset($this->passwords[$streamId]);
                $done++;
            } catch (RuntimeException $e) {
                $problems[] = $e->getMessage();
            }
        }

        if ($done > 0) {
            Flux::toast(variant: 'success', text: $done.' '.Str::plural('stream', $done).' marked ready.');
        }
        if ($problems !== []) {
            Flux::toast(variant: 'warning', text: implode(' ', $problems), duration: 8000);
        }
        if ($done === 0 && $problems === []) {
            Flux::toast(text: 'Enter a new DJ password for the streams you have updated.');
        }
    }

    public function render()
    {
        $streams = Stream::with(['server', 'package'])
            ->whereNull('rental_id')
            ->where('need_work', true)
            ->orderBy('port')
            ->get();

        return view('livewire.streams.needs-work', ['groups' => $streams->groupBy(fn (Stream $s) => $s->server?->domain ?? 'Unknown server')->sortKeys()]);
    }
}
