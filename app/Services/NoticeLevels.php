<?php

namespace App\Services;

use App\Models\Notice;

/**
 * Picks the notice level for the hours a rental has left (port of 2.0.x NoticesHelper::getNoticeLevel).
 */
class NoticeLevels
{
    public const EXPIRED = 6;

    public const ACTIVE = 10;

    public function forHours(int $hoursRemaining): int
    {
        if ($hoursRemaining <= 0) {
            return self::EXPIRED;
        }
        $notices = Notice::orderByDesc('hours_remaining')->get();
        if ($notices->isEmpty()) {
            return self::ACTIVE;
        }
        $picked = self::ACTIVE;
        $lastId = self::ACTIVE;
        foreach ($notices as $notice) {
            if ($hoursRemaining < $notice->hours_remaining) {
                $lastId = $notice->id;

                continue;
            }
            if ($hoursRemaining === $notice->hours_remaining) {
                $picked = $notice->id;
                break;
            }
            $picked = $lastId;
            break;
        }

        return $picked;
    }
}
