<?php

namespace App\Endpoint\SecondLifeApi\Detailsserver;

use App\Helpers\BotHelper;
use App\Helpers\SwapablesHelper;
use App\Models\Avatar;
use App\Models\Sets\DetailSet;
use App\Models\Notecard;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Template;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    /*
        this change should not trigger build
    */
    public function process(): void
    {
        if ($this->owner_override == false) {
            $this->setSwapTag("message", "SystemAPI access only - please contact support");
            return;
        }
        $detail_set = new DetailSet();

        $markFailed = $this->input->post("failed");
        if ($markFailed == null) {
            $markFailed = false;
        }
        $loadAmount = 1;
        if ($markFailed == true) {
            $loadAmount = 20;
        }
        $detail_set->loadNewest($loadAmount, [], [], "id", "ASC"); // lol loading oldest with newest command ^+^ hax
        if ($detail_set->getCount() == 0) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "nowork");
            return;
        }
        $ids = $detail_set->getAllIds();
        $detail = $detail_set->getObjectByID($ids[array_rand($ids)]);
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
        $bot_helper = new BotHelper();
        $swapables_helper = new SwapablesHelper();
        $sendmessage = $swapables_helper->getSwappedText(
            $template->getDetail(),
            $avatar,
            $rental,
            $package,
            $server,
            $stream
        );
        $sendMessage_status = $bot_helper->sendMessage($avatar, $sendmessage, true);
        if ($sendMessage_status["status"] == false) {
            $this->setSwapTag("message", "Unable to put message into mailbox for sending!");
            return;
        }
        if ($bot_helper->getNotecards() == true) {
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
