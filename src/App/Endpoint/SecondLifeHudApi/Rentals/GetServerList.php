<?php

namespace App\Endpoint\SecondLifeHudApi\Rentals;

use App\R7\Set\RentalSet;
use App\R7\Set\StreamSet;
use App\Template\SecondlifeHudAjax;

class GetServerList extends SecondlifeHudAjax
{
    public function process(): void
    {
        $this->setSwapTag("status", true);
        $rentals_set = new RentalSet();
        $rentals_set->loadByField("avatarLink", $this->Object_OwnerAvatar->getId());
        if ($rentals_set->getCount() < 1) {
            $this->setSwapTag("message", "none");
            return;
        }

        $stream_set = new StreamSet();
        $stream_set->loadIds($rentals_set->getUniqueArray("streamLink"));
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
            $rental = $rentals_set->getObjectByID($stream->getRentalLink());
            $reply["ports"][] = $stream->getPort();
            $reply["uids"][] = $rental->getRentalUid();
            $timeleft = $rental->getExpireUnixtime();
            if ($timeleft < time()) {
                $reply["states"][] = 0;
            } elseif ($timeleft < $oneday) {
                $reply["states"][] = 1;
            } else {
                $reply["states"][] = 2;
            }
        }
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("count", count($reply["states"]));
        foreach ($reply as $key => $value) {
            $this->setSwapTag($key, $value);
        }
    }
}