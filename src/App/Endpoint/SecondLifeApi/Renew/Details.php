<?php

namespace App\Endpoint\SecondLifeApi\Renew;

use App\R7\Set\ApirequestsSet;
use App\R7\Model\Avatar;
use App\R7\Model\Banlist;
use App\R7\Set\RentalSet;
use App\R7\Set\StreamSet;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Details extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $avatarUUID = $input->postFilter("avatarUUID");
        $avatar = new Avatar();
        $this->setSwapTag("dataset_count", 0);
        if ($avatar->loadByField("avatarUUID", $avatarUUID) == false) {
            $this->setSwapTag("message", "Unable to find avatar");
            return;
        }

        $banlist = new Banlist();
        if ($banlist->loadByField("avatarLink", $avatar->getId()) == true) {
            $this->setSwapTag("message", "Unable to find avatar");
            return;
        }

        $this->setSwapTag("status", true);
        $rental_set = new RentalSet();
        $rental_set->loadOnField("avatarLink", $avatar->getId());
        if ($rental_set->getCount() < 1) {
            $this->setSwapTag("message", "Unable to find any active rentals");
            return;
        }
        $stream_set = new StreamSet();
        $stream_set->loadIds($rental_set->getAllByField("streamLink"));
        if ($stream_set->getCount() < 1) {
            $this->setSwapTag("message", "Unable to find any streams linked to rentals");
            return;
        }
        $apirequests_set = new ApirequestsSet();
        $apirequests_set->loadAll();
        $used_stream_ids = $apirequests_set->getUniqueArray("streamLink");
        $reply_dataset = [];
        foreach ($rental_set->getAllIds() as $rental_id) {
            $rental = $rental_set->getObjectByID($rental_id);
            $stream = $stream_set->getObjectByID($rental->getStreamLink());
            if ($stream != null) {
                if (in_array($stream->getId(), $used_stream_ids) == false) {
                    $reply_dataset[] = "" . $rental->getRentalUid() . "|||" . $stream->getPort() . "";
                }
            }
        }
        if (count($reply_dataset) < 1) {
            $this->setSwapTag("status", false);
            $this->setSwapTag("message", "Unable to build reply dataset");
            return;
        }
        $this->setSwapTag("dataset_count", count($reply_dataset));
        $this->setSwapTag("dataset", $reply_dataset);
        $this->setSwapTag("message", sprintf("Client account: %1\$s", $avatar->getAvatarName()));
    }
}
