<?php

namespace App\Endpoint\Secondlifeapi\Detailsserver;

use App\Helpers\BotHelper;
use App\Helpers\SwapablesHelper;
use App\Models\Detail;
use App\Models\Notecard;
use App\Models\Sets\AvatarSet;
use App\Models\Sets\DetailSet;
use App\Models\Sets\PackageSet;
use App\Models\Sets\RentalSet;
use App\Models\Sets\ServerSet;
use App\Models\Sets\StreamSet;
use App\Models\Sets\TemplateSet;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    protected BotHelper $botHelper;
    protected function processDetailRequest(Detail $detail): bool
    {
        $rental = $this->rentals->getObjectByID($detail->getRentalLink());
        $avatar = $this->avatars->getObjectByID($rental->getAvatarLink());
        $stream = $this->streams->getObjectByID($rental->getStreamLink());
        $server = $this->servers->getObjectByID($stream->getServerLink());
        $package = $this->packages->getObjectByID($stream->getPackageLink());
        $template = $this->templates->getObjectByID($package->getTemplateLink());
        $test = [$detail, $rental, $avatar, $stream, $server, $package, $template];
        if (in_array(null, $test) == true) {
            $this->failed("One or more required objects failed to load");
            return false;
        }
        $swapables_helper = new SwapablesHelper();
        $sendmessage = $swapables_helper->getSwappedText(
            $template->getDetail(),
            $avatar,
            $rental,
            $package,
            $server,
            $stream
        );
        if ($this->botHelper->sendMessage($avatar, $sendmessage)->status == false) {
            return false;
        }
        if ($this->botconfig->getNotecards() == true) {
            $notecard = new Notecard();
            $notecard->setAsNotice(false);
            $notecard->setRentalLink($rental->getId());
            $create = $notecard->createEntry();
            if ($create->status == false) {
                $this->failed("Unable to request dynamic notecard");
                return false;
            }
            $addtoQ = $this->botHelper->sendBotNextNotecard($this->siteConfig->getSiteURL(), $this->siteConfig->getSlConfig()->getHttpInboundSecret());
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
