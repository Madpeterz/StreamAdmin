<?php

namespace App\Livewire\Outbox;

use App\Models\BotCommand;
use App\Models\DetailRequest;
use App\Models\Message;
use App\Models\Notecard;
use App\Models\NotecardMail;
use App\Models\QueuedEvent;
use Carbon\CarbonInterface;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * What is waiting to go out to Second Life, queue by queue (port of the 2.0.x Outbox status pages).
 */
#[Title('Outbox')]
class Index extends Component
{
    use WithPagination;

    /**
     * Queue => label, what it holds, and seconds per item when the in-world objects poll (`norm`)
     * and when the scheduler also works the queue (`cron`), as 2.0.x estimated them.
     */
    public const QUEUES = [
        'mail' => ['label' => 'Mail', 'about' => 'IMs for the mail server object', 'model' => Message::class, 'norm' => 15, 'cron' => 15],
        'notecards' => ['label' => 'Notecards', 'about' => 'Stream detail and notice notecards for the bot to write', 'model' => Notecard::class, 'norm' => 20, 'cron' => 8],
        'docs' => ['label' => 'Docs', 'about' => 'Static notecards for the notecard server to give', 'model' => NotecardMail::class, 'norm' => 20, 'cron' => 20],
        'details' => ['label' => 'Details', 'about' => 'Stream details clients asked to be sent again', 'model' => DetailRequest::class, 'norm' => 15, 'cron' => 8],
        'events' => ['label' => 'Events', 'about' => 'Rental events for your event hook', 'model' => QueuedEvent::class, 'norm' => 30, 'cron' => 30],
        'bot' => ['label' => 'Bot', 'about' => 'Commands for the SL bot', 'model' => BotCommand::class, 'norm' => 30, 'cron' => 8],
    ];

    #[Url]
    public string $queue = 'mail';

    public function updatedQueue(): void
    {
        $this->resetPage();
    }

    /** "about 3 mins" for $seconds, or null when nothing is waiting */
    public static function timeToClear(int $seconds): ?string
    {
        return $seconds > 0 ? now()->addSeconds($seconds)->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE) : null;
    }

    public function render()
    {
        $counts = collect(self::QUEUES)->map(fn (array $queue) => $queue['model']::count());
        $queue = array_key_exists($this->queue, self::QUEUES) ? $this->queue : 'mail';

        $items = match ($queue) {
            'mail' => Message::with('avatar'),
            'notecards' => Notecard::with(['rental.avatar', 'notice']),
            'docs' => NotecardMail::with(['avatar', 'noticeNotecard']),
            'details' => DetailRequest::with('rental.avatar'),
            'events' => QueuedEvent::query()->select(['id', 'event_name', 'event_unixtime']),
            'bot' => BotCommand::query(),
        };

        return view('livewire.outbox.index', [
            'counts' => $counts,
            'current' => $queue,
            'items' => $items->orderBy('id')->paginate(25),
        ]);
    }
}
