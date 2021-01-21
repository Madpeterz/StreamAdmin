<?php

use App\Models\NoticeSet;
use App\Models\Rental;

$notice_set = new NoticeSet();
$notice_set->loadAll();
$client_expired = 0;
$client_expires_soon = 0;
$client_ok = 0;
$rental = new Rental();
$group_count = $sql->group_count($rental->getTable(), "noticeLink");
if ($group_count["status"] == true) {
    foreach ($group_count["dataset"] as $key => $count) {
        $notice = $notice_set->getObjectByID($key);
        if ($notice->getHoursRemaining() <= 0) {
            $client_expired += $count;
        } elseif ($notice->getHoursRemaining() > 24) {
            $client_ok += $count;
        } else {
            $client_expires_soon += $count;
        }
    }
}
