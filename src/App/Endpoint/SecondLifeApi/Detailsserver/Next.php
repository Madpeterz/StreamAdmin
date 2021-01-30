<?php

namespace App\Endpoint\SecondLifeApi\Detailsserver;

use App\R7\Model\Avatar;
use App\R7\Model\Botconfig;
use App\R7\Set\DetailSet;
use App\R7\Model\Notecard;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use App\R7\Model\Template;
use App\Template\SecondlifeAjax;
use bot_helper;
use swapables_helper;

class Next extends SecondlifeAjax
{
    public function process(): void
    {
        $status = false;
        if ($this->owner_override == false) {
            $this->setSwapTag("message", "SystemAPI access only - please contact support");
            return;
        }
        $botconfig = new Botconfig();
        if ($botconfig->loadID(1) != 1) {
            $this->setSwapTag("message", "Unable to load bot config");
            return;
        }
        $botavatar = new Avatar();
        if ($botavatar->loadID($botconfig->getAvatarLink()) == false) {
            $this->setSwapTag("message", "Unable to load bot avatar");
            return;
        }
        $detail_set = new DetailSet();
        $detail_set->loadNewest(1, [], [], "id", "ASC"); // lol loading oldest with newest command ^+^ hax
        if ($detail_set->getCount() == 0) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "nowork");
            return;
        }
        $detail = $detail_set->getFirst();
        $rental = new Rental();
        if ($rental->loadID($detail->getRentalLink()) == false) {
            $this->setSwapTag("message", "Unable to load rental");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadID($rental->getAvatarLink()) == false) {
            $this->setSwapTag("message", "Unable to load avatar");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamLink()) == false) {
            $this->setSwapTag("message", "Unable to load stream");
            return;
        }
        $server = new Server();
        if ($server->loadID($stream->getServerLink()) == false) {
            $this->setSwapTag("message", "Unable to load server");
            return;
        }
        $package = new Package();
        if ($package->loadID($stream->getPackageLink()) == false) {
            $this->setSwapTag("message", "Unable to load package");
            return;
        }
        $template = new Template();
        if ($template->loadID($package->getTemplateLink()) == false) {
            $this->setSwapTag("message", "Unable to load template");
            return;
        }
        $remove_status = $detail->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag("message", "Unable to remove detail request");
            return;
        }
        $bot_helper = new bot_helper();
        $swapables_helper = new swapables_helper();
        $sendmessage = $swapables_helper->get_swapped_text(
            $template->getDetail(),
            $avatar,
            $rental,
            $package,
            $server,
            $stream
        );
        $sendMessage_status = $bot_helper->sendMessage($botconfig, $botavatar, $avatar, $sendmessage, true);
        if ($sendMessage_status["status"] == false) {
            $this->setSwapTag("message", "Unable to put message into mailbox for sending!");
            return;
        }
        if ($botconfig->getNotecards() == true) {
            $notecard = new Notecard();
            $notecard->setRentalLink($rental->getId());
            $create_status = $notecard->createEntry();
            if ($create_status["status"] == false) {
                $this->setSwapTag("message", "Unable to add notecard to be created!");
                return;
            }
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
    }
}
