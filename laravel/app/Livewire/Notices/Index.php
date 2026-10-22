<?php

namespace App\Livewire\Notices;

use App\Models\Notice;
use App\Services\NoticeLevels;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Notice levels: the reminders sent as a rental runs down, ordered by when they trigger.
 */
#[Title('Notices')]
class Index extends Component
{
    /** "7 days before expiry", "5 hours before expiry", "On expiry" or "While active". */
    public static function when(Notice $notice): string
    {
        return match (true) {
            $notice->id === NoticeLevels::ACTIVE => 'While active',
            $notice->id === NoticeLevels::EXPIRED => 'On expiry',
            $notice->hours_remaining % 24 === 0 => ($notice->hours_remaining / 24).' '.str('day')->plural($notice->hours_remaining / 24).' before expiry',
            default => $notice->hours_remaining.' '.str('hour')->plural($notice->hours_remaining).' before expiry',
        };
    }

    public function render()
    {
        return view('livewire.notices.index', [
            'notices' => Notice::withCount('rentals')->with('noticeNotecard')->orderByDesc('hours_remaining')->get(),
        ]);
    }
}
