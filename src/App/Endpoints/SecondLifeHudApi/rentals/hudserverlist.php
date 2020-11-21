<?php

$status = true;
$rentals_set = new rental_set();
$rentals_set->loadByField("avatarlink", $object_owner_avatar->getId());
if ($rentals_set->getCount() > 0) {
    $reply["ports"] = [];
    $reply["uids"] = [];
    $reply["states"] = [];
    $stream_set = new stream_set();
    $stream_set->loadIds($rentals_set->getUniqueArray("streamlink"));
    $oneday = time() + ((60 * 60) * 24);
    if ($stream_set->getCount() > 0) {
        foreach ($stream_set->getAllIds() as $streamid) {
            $stream = $stream_set->getObjectByID($streamid);
            $rental = $rentals_set->getObjectByID($stream->getRentallink());
            $reply["ports"][] = $stream->getPort();
            $reply["uids"][] = $rental->getRental_uid();
            $timeleft = $rental->getExpireunixtime();
            if ($timeleft < time()) {
                $reply["states"][] = 0;
            } elseif ($timeleft < $oneday) {
                $reply["states"][] = 1;
            } else {
                $reply["states"][] = 2;
            }
        }
        echo "ok - " . count($reply["states"]);
    } else {
        echo "none";
    }
} else {
    echo "none";
}
