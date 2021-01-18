<?php

namespace App\Endpoints\SecondLifeHudApi\Rentals;

use App\Models\RentalSet;
use App\Models\StreamSet;
use App\Template\SecondlifeAjax;

class Hudserverlist extends SecondlifeAjax
{
    public function process(): void
    {
        $this->setSwapTag("status", "true");
        $rentals_set = new RentalSet();
        $rentals_set->loadByField("avatarlink", $this->object_owner_avatar->getId());
        if ($rentals_set->getCount() < 1) {
            $this->setSwapTag("message", "none");
            return;
        }

        $stream_set = new StreamSet();
        $stream_set->loadIds($rentals_set->getUniqueArray("streamlink"));
        $oneday = time() + ((60 * 60) * 24);
        if ($stream_set->getCount() < 1) {
            $this->setSwapTag("message", "none");
            return;
        }
        $reply = [];
        $reply["ports"] = [];
        $reply["uids"] = [];
        $reply["states"] = [];
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
        $this->setSwapTag("message", "ok - " . count($reply["states"]));
        foreach ($reply as $key => $value) {
            $this->setSwapTag($key, $value);
        }
    }
}
