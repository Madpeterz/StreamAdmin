<?php

namespace App\Helpers;

use App\R7\Set\NoticeSet;

class NoticesHelper
{
    protected ?NoticeSet $notices = null;
    protected function loadNotices(): void
    {
        if ($this->notices == null) {
            $this->notices = new NoticeSet();
            $this->notices->loadAll("hoursRemaining", "DESC");
        }
        if ($this->notices->getCount() == 0) {
            $this->notices = null;
        }
    }
    public function getNoticeLevel(int $hoursRemaining): int
    {
        if ($hoursRemaining <= 0) {
            return 6; // expired;
        }
        $this->loadNotices();
        if ($this->notices == null) {
            return 10; // unable to load notices default to 10 [active]
        }
        $picked_notice = 10;
        $last_id = 10;
        foreach ($this->notices as $notice) {
            if ($hoursRemaining < $notice->getHoursRemaining()) {
                $last_id = $notice->getId();
                continue;
            }
            if ($hoursRemaining == $notice->getHoursRemaining()) {
                $picked_notice = $notice->getId();
                break;
            }
            $picked_notice = $last_id;
            break;
        }
        return $picked_notice;
    }
}
