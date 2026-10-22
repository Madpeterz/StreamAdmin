<?php

namespace App\Endpoint\Secondlifeapi\Detailsserver;

use App\Helpers\BotHelper;
use App\Helpers\SwapablesHelper;
use App\Models\Detail;
use App\Models\Notecard;
use App\Models\Set\AvatarSet;
use App\Models\Set\DetailSet;
use App\Models\Set\PackageSet;
use App\Models\Set\RentalSet;
use App\Models\Set\ServerSet;
use App\Models\Set\StreamSet;
use App\Models\Set\TemplateSet;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    protected BotHelper $botHelper;
    protected function processDetailRequest(Detail $detail): bool
    {
        $rental = $this->rentals->getObjectByID($detail->_RentalLink);
        $avatar = $this->avatars->getObjectByID($rental->_AvatarLink);
        $stream = $this->streams->getObjectByID($rental->_StreamLink);
        $server = $this->servers->getObjectByID($stream->_ServerLink);
        $package = $this->packages->getObjectByID($stream->_PackageLink);
        $template = $this->templates->getObjectByID($package->_TemplateLink);
        $test = [$detail, $rental, $avatar, $stream, $server, $package, $template];
        if (in_array(null, $test) == true) {
            $this->failed("One or more required objects failed to load");
            return false;
        }
        $swapables_helper = new SwapablesHelper();
        $sendmessage = $swapables_helper->getSwappedText(
            $template->_Detail,
            $avatar,
            $rental,
            $package,
            $server,
            $stream
        );
        if ($this->botHelper->sendMessage($avatar, $sendmessage)->status == false) {
            return false;
        }
        if ($this->botconfig->_Notecards == true) {
            $notecard = new Notecard();
            $notecard->_AsNotice = false;
            $notecard->_RentalLink = $rental->_Id;
            $create = $notecard->createEntry();
            if ($create->status == false) {
                $this->failed("Unable to request dynamic notecard");
                return false;
            }
            $addtoQ = $this->botHelper->sendBotNextNotecard($this->siteConfig->getSiteURL(), $this->siteConfig->getSlConfig()->_HttpInboundSecret);
            if ($addtoQ->status == false) {
                $this->failed("Unable to request bot to collect next dynamic notecard");
                return false;
            }
        }
        return true;
    }

    protected RentalSet $rentals;
    protected AvatarSet $avatars;
    protected StreamSet $streams;
    protected ServerSet $servers;
    protected PackageSet $packages;
    protected TemplateSet $templates;
    public function process(): void
    {
        if ($this->hasAccessOwner() == false) {
            return;
        }
        $detailsRequests = new DetailSet();
        $detailsRequests->loadAll();
        if ($detailsRequests->getCount() == 0) {
            $this->ok("nowork");
            return;
        }
        $this->rentals = $detailsRequests->relatedRental();
        $this->avatars = $this->rentals->relatedAvatar();
        $this->streams = $this->rentals->relatedStream();
        $this->servers = $this->streams->relatedServer();
        $this->packages = $this->streams->relatedPackage();
        $this->templates = $this->packages->relatedTemplate();
        $this->setupBot();
        $allok = true;
        $this->botHelper = new BotHelper();
        if ($this->botconfig != null) {
            $this->botHelper->attachBotSetup($this->bot, $this->botconfig);
        }
        foreach ($detailsRequests as $detailObject) {
            $allok = $this->processDetailRequest($detailObject);
            if ($allok == false) {
                break;
            }
        }
        if ($allok == false) {
            $this->failed("Failed to unpack detail requests");
            return;
        }
        $purge = $detailsRequests->purgeCollection();
        if ($purge->status == false) {
            $this->failed("Failed to remove processed detail requests");
            return;
        }
        $this->ok("ok");
    }
}
