<?php

namespace App\Endpoints\SecondLifeApi\Detailsserver;

use App\Models\Avatar;
use App\Models\Botconfig;
use App\Models\DetailSet;
use App\Models\Notecard;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Template;
use App\Template\SecondlifeAjax;
use bot_helper;
use swapables_helper;

class Next extends SecondlifeAjax
{
    public function process(): void
    {
        $status = false;
        if ($this->owner_override == false) {
            $this->output->setSwapTagString("message", "SystemAPI access only - please contact support");
            return;
        }
        $botconfig = new Botconfig();
        if ($botconfig->loadID(1) != 1) {
            $this->output->setSwapTagString("message", "Unable to load bot config");
            return;
        }
        $botavatar = new Avatar();
        if ($botavatar->loadID($botconfig->getAvatarlink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load bot avatar");
            return;
        }
        $detail_set = new DetailSet();
        $detail_set->loadNewest(1, [], [], "id", "ASC"); // lol loading oldest with newest command ^+^ hax
        if ($detail_set->getCount() == 0) {
            $this->output->setSwapTagString("status", "true");
            $this->output->setSwapTagString("message", "nowork");
            return;
        }
        $detail = $detail_set->getFirst();
        $rental = new Rental();
        if ($rental->loadID($detail->getRentallink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load rental");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadID($rental->getAvatarlink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load avatar");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamlink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load stream");
            return;
        }
        $server = new Server();
        if ($server->loadID($stream->getServerlink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load server");
            return;
        }
        $package = new Package();
        if ($package->loadID($stream->getPackagelink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load package");
            return;
        }
        $template = new Template();
        if ($template->loadID($package->getTemplatelink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load template");
            return;
        }
        $remove_status = $detail->removeEntry();
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to remove detail request");
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
        $send_message_status = $bot_helper->send_message($botconfig, $botavatar, $avatar, $sendmessage, true);
        if ($send_message_status["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to put message into mailbox for sending!");
            return;
        }
        if ($botconfig->getNotecards() == true) {
            $notecard = new Notecard();
            $notecard->setRentallink($rental->getId());
            $create_status = $notecard->createEntry();
            if ($create_status["status"] == false) {
                $this->output->setSwapTagString("message", "Unable to add notecard to be created!");
                return;
            }
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "ok");
    }
}
