<?php

namespace App\Endpoint\Secondlifeapi\Detailsserver;

use App\Helpers\BotHelper;
use App\Helpers\SwapablesHelper;
use App\Models\Sets\DetailSet;
use App\Models\Notecard;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    /*
        this change should not trigger build
    */
    public function process(): void
    {
        if ($this->owner_override == false) {
            $this->failed("SystemAPI access only - please contact support");
            return;
        }
        $detail_set = new DetailSet();

        $markFailed = $this->input->post("failed")->asBool();
        if ($markFailed == null) {
            $markFailed = false;
        }
        $loadAmount = 1;
        if ($markFailed == true) {
            $loadAmount = 20;
        }
        $detail_set->loadNewest(limit:$loadAmount, orderDirection:"ASC");
        if ($detail_set->getCount() == 0) {
            $this->ok("nowork");
            return;
        }
        $ids = $detail_set->getAllIds();
        $detail = $detail_set->getObjectByID($ids[array_rand($ids)]);
        $rental = $detail?->relatedRental()?->getFirst();
        $avatar = $rental?->relatedAvatar()?->getFirst();
        $stream = $rental?->relatedStream()?->getFirst();
        $server = $stream?->relatedServer()?->getFirst();
        $package = $rental?->relatedPackage()?->getFirst();
        $template = $package?->relatedTemplate()?->getFirst();
        $test = [$detail,$rental,$avatar,$stream,$server,$package,$template];
        if (in_array(null, $test) == true) {
            $this->failed("One or more required objects failed to load");
            return;
        }
        $remove_status = $detail->removeEntry();
        if ($remove_status->status == false) {
            $this->failed("Unable to remove detail request");
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
        if ($sendMessage_status->status == false) {
            $this->failed("Unable to put message into mailbox for sending!");
            return;
        }
        if ($bot_helper->getNotecards() == true) {
            $notecard = new Notecard();
            $notecard->setRentalLink($rental->getId());
            $create_status = $notecard->createEntry();
            if ($create_status->status == false) {
                $this->failed("Unable to add notecard to be created!");
                return;
            }
        }
        $this->ok("ok");
    }
}
