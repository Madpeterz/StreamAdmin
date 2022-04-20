<?php

namespace App\Endpoint\SecondLifeApi\Renew;

use App\Models\Sets\ApirequestsSet;
use App\Models\Avatar;
use App\Models\Banlist;
use App\Models\Sets\RentalSet;
use App\Models\Sets\StreamSet;
use App\Template\SecondlifeAjax;

class Details extends SecondlifeAjax
{
    /**
     * getRentalDetailsForAvatar
     * see output for data
     */
    public function getRentalDetailsForAvatar(Avatar $avatar): void
    {
        $banlist = new Banlist();
        if ($banlist->loadByField("avatarLink", $avatar->getId()) == true) {
            $this->failed("Unable to find avatar");
            return;
        }
        $rental_set = new RentalSet();
        $rental_set->loadByAvatarLink($avatar->getId());
        if ($rental_set->getCount() < 1) {
            $this->failed("Unable to find any active rentals");
            return;
        }
        $stream_set = $rental_set->relatedStream();
        if ($stream_set->getCount() < 1) {
            $this->failed("Unable to find any streams linked to rentals, Please note if "
            . "a stream is busy with an API request it will be hidden from this list!");
            return;
        }
        $reply_dataset = [];
        foreach ($rental_set as $rental) {
            $stream = $stream_set->getObjectByID($rental->getStreamLink());
            if ($stream == null) {
                continue;
            }
            $reply_dataset[] = "" . $rental->getRentalUid() . "|||"
            . $stream->getPort() . "|||"
            . $this->timeRemainingHumanReadable($rental->getExpireUnixtime());
        }
        if (count($reply_dataset) < 1) {
            $this->failed("Unable to find any streams linked to rentals, Please note if "
            . "a stream is busy with an API request it will be hidden from this list!");
            return;
        }
        $this->setSwapTag("dataset_count", count($reply_dataset));
        $this->setSwapTag("dataset", $reply_dataset);
        $this->ok(sprintf("Client account: %1\$s", $avatar->getAvatarName()));
    }
    public function process(): void
    {
        $avatarUUID = $this->input->post("avatarUUID")->isUuid()->asString();
        $avatar = new Avatar();
        $this->setSwapTag("dataset_count", 0);
        if ($avatar->loadByField("avatarUUID", $avatarUUID) == false) {
            $this->setSwapTag("message", "Unable to find avatar");
            return;
        }
        $this->getRentalDetailsForAvatar($avatar);
    }
}
