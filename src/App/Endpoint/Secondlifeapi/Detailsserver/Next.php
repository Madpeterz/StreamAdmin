<?php

namespace App\Endpoint\Secondlifeapi\Detailsserver;

use App\Helpers\BotHelper;
use App\Helpers\SwapablesHelper;
use App\Models\Avatar;
use App\Models\Sets\DetailSet;
use App\Models\Notecard;
use App\Models\Rental;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    protected DetailSet $detailSet;
    protected function loadDetailsSet(): bool
    {
        $this->detailSet = new DetailSet();

        $markFailed = $this->input->post("failed")->asBool();
        if ($markFailed == null) {
            $markFailed = false;
        }
        $loadAmount = 1;
        if ($markFailed == true) {
            $loadAmount = 20;
        }
        $this->detailSet->loadNewest(limit: $loadAmount, orderDirection: "ASC");
        if ($this->detailSet->getCount() == 0) {
            $this->ok("nowork");
            return false;
        }
        return true;
    }
    public function process(): void
    {
        if ($this->hasAccessOwner() == false) {
            return;
        }
        if ($this->loadDetailsSet() == false) {
            return;
        }

        $ids = $this->detailSet->getAllIds();
        $detail = $this->detailSet->getObjectByID($ids[array_rand($ids)]);
        $this->rental = $detail?->relatedRental()?->getFirst();
        $avatar = $this->rental?->relatedAvatar()?->getFirst();
        $stream = $this->rental?->relatedStream()?->getFirst();
        $server = $stream?->relatedServer()?->getFirst();
        $package = $this->rental?->relatedPackage()?->getFirst();
        $template = $package?->relatedTemplate()?->getFirst();
        $test = [$detail, $this->rental, $avatar, $stream, $server, $package, $template];
        if (in_array(null, $test) == true) {
            $this->failed("One or more required objects failed to load");
            return;
        }
        $remove_status = $detail->removeEntry();
        if ($remove_status->status == false) {
            $this->failed("Unable to remove detail request");
            return;
        }
        $this->botHelper = new BotHelper();
        $swapables_helper = new SwapablesHelper();
        $sendmessage = $swapables_helper->getSwappedText(
            $template->getDetail(),
            $avatar,
            $this->rental,
            $package,
            $server,
            $stream
        );
        if ($this->createBotMessage($avatar, $sendmessage) == false) {
            return;
        }
        if ($this->createNotecardRequest() == false) {
            return;
        }
        $this->ok("ok");
    }

    protected BotHelper $botHelper;
    protected ?Rental $rental = null;

    protected function createBotMessage(Avatar $avatar, string $sendmessage): bool
    {
        $sendMessage_status = $this->botHelper->sendMessage($avatar, $sendmessage, true);
        if ($sendMessage_status->status == false) {
            $this->failed("Unable to put message into mailbox for sending!");
            return false;
        }
        return true;
    }

    protected function createNotecardRequest(): bool
    {
        if ($this->botHelper->getNotecards() == true) {
            $notecard = new Notecard();
            $notecard->setRentalLink($this->rental->getId());
            $create_status = $notecard->createEntry();
            if ($create_status->status == false) {
                $this->failed("Unable to add notecard to be created!");
                return false;
            }
        }
        return true;
    }
}
