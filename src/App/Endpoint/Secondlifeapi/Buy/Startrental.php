<?php

namespace App\Endpoint\Secondlifeapi\Buy;

use App\Helpers\AvatarHelper;
use App\Helpers\BotHelper;
use App\Helpers\EventsQHelper;
use App\Helpers\NoticesHelper;
use App\Helpers\TransactionsHelper;
use App\Models\Avatar;
use App\Models\Banlist;
use App\Models\Detail;
use App\Models\Notecardmail as ModelNotecardmail;
use App\Models\Noticenotecard;
use App\Models\Set\NoticeSet;
use App\Models\Package;
use App\Models\Refunds;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Set\RentalSet;
use App\Models\Set\StreamSet;
use App\Template\SecondlifeAjax;

class Startrental extends SecondlifeAjax
{
    protected function notBanned(Avatar $avatar): bool
    {
        $banlist = new Banlist();
        $banlist->loadByAvatarLink($avatar->_Id);
        if ($banlist->_Id > 0) {
            return false;
        }
        return true;
    }

    protected function streamLimits(Avatar $avatar, Package $package): bool
    {
        $slconfig = $this->siteConfig->getSlConfig();
        // global hard limit for streams
        if ($slconfig->_LimitStreams == false) {
            return true;
        }
        $rentals = new RentalSet();
        $totalStreams = $rentals->countInDB([
            "fields" => ["avatarLink"],
            "values" => [$avatar->_Id],
        ]);
        if ($totalStreams->status == false) {
            return false;
        }
        if ($totalStreams->items >= $slconfig->_MaxTotalStreams) {
            return false;
        }
        // per package limit for streams
        if ($package->_EnforceCustomMaxStreams == false) {
            return true;
        }
        $totalStreams = $rentals->countInDB([
            "fields" => ["avatarLink", "packageLink"],
            "values" => [$avatar->_Id, $package->_Id],
        ]);
        if ($totalStreams->status == false) {
            return false;
        }
        if ($totalStreams->items >= $package->_MaxStreamsInPackage) {
            return false;
        }
        return true;
    }

    protected function getAvatar(string $avatarUUID, string $avatarName): ?Avatar
    {
        $avatar_helper = new AvatarHelper();
        $get_av_status = $avatar_helper->loadOrCreate($avatarUUID, $avatarName);
        if ($get_av_status == true) {
            return $avatar_helper->getAvatar();
        }
        return null;
    }

    protected function getPackage(string $packageuid): ?Package
    {
        $package = new Package();
        $package->loadByPackageUid($packageuid);
        if ($package->isLoaded() == true) {
            return $package;
        }
        return null;
    }

    protected function getUnassignedStreamOnPackage(package $package): ?Stream
    {
        $whereconfig = [
            "fields" => ["rentalLink", "packageLink", "needWork"],
            "values" => [null, $package->_Id, 0],
            "types" => ["i", "i", "i"],
            "matches" => ["IS", "=", "="],
        ];
        $stream_set = new StreamSet();
        $stream_set->loadWithConfig($whereconfig);
        if ($stream_set->getCount() > 0) {
            $entrys = $stream_set->getAllIds();
            return $stream_set->getObjectByID($entrys[rand(0, count($entrys) - 1)]);
        }
        return null;
    }

    protected function sendStaticNotecard(int $staticNotecardid, Avatar $avatar): void
    {
        if ($staticNotecardid <= 1) {
            return;
        }
        if ($avatar->_Id < 1) {
            return;
        }
        $noticeNotecard = new Noticenotecard();
        if ($noticeNotecard->loadID($staticNotecardid)->status == false) {
            return;
        }
        if ($noticeNotecard->_Missing == true) {
            return;
        }
        $notecardmail = new ModelNotecardmail();
        $notecardmail->_AvatarLink = $avatar->_Id;
        $notecardmail->_NoticeNotecardLink = $noticeNotecard->_Id;
        $notecardmail->createEntry();
    }

    public function process(): void
    {
        $package = null;
        $stream = null;
        $avatar = null;
        $hours_remain = 0;
        $amountpaid = 0;
        $use_notice_index = 0;

        $avatar = $this->getAvatar(
            $this->input->post("avatarUUID")->isUuid()->asString(),
            $this->input->post("avatarName")->asString()
        );
        $package = $this->getPackage($this->input->post("packageuid")->asString());
        if ($package == null) {
            $this->failed("Unable to find package");
            return;
        } elseif ($avatar == null) {
            $this->failed("Unable to attach avatar");
            return;
        } elseif ($this->notBanned($avatar) == false) {
            $this->failed("Unable to attach avatar");
            return;
        } elseif ($this->streamLimits($avatar, $package) == false) {
            $this->failed("Server setup rejected extra streams");
            return;
        }

        $stream = $this->getUnassignedStreamOnPackage($package);
        if ($stream == null) {
            $this->failed("Unable to find a unsold stream in that package");
            return;
        }

        $server = new Server();
        if ($server->loadID($stream->_ServerLink) == false) {
            $this->failed("Unable to find the server attached to the stream");
            return;
        }

        $amountpaid = $this->input->post("amountpaid")->checkGrtThanEq(1)->asInt();
        $accepted_payment_amounts = [
            $package->_Cost => 1,
            ($package->_Cost * 2) => 2,
            ($package->_Cost * 3) => 3,
            ($package->_Cost * 4) => 4,
        ];
        if (array_key_exists($amountpaid, $accepted_payment_amounts) == false) {
            $this->failed("Payment amount not accepted");
            return;
        }
        // get expire unixtime and notice index
        $notice_set = new NoticeSet();
        $notice_set->loadAll();
        $sorted_linked = $notice_set->getLinkedArray("hoursRemaining", "id");
        ksort($sorted_linked, SORT_NUMERIC);
        $multipler = $accepted_payment_amounts[$amountpaid];
        $hours_remain = ($package->_Days * 24) * $multipler;

        $noticesHelper = new NoticesHelper();
        $use_notice_index = $noticesHelper->getNoticeLevel($hours_remain);
        $unixtime = time() + ($hours_remain * $this->siteConfig->unixtimeHour());

        $rentals = new RentalSet();
        $rentals->loadByAvatarLink($avatar->_Id);

        $rental = new Rental();
        $uid_rental = $rental->createUID("rentalUid", 8);
        $status = $uid_rental->status;
        if ($status == false) {
            $this->failed("Unable to create rental uid");
            return;
        }
        $rental->_RentalUid = $uid_rental->uid;
        $rental->_AvatarLink = $avatar->_Id;
        $rental->_PackageLink = $stream->_PackageLink;
        $rental->_StreamLink = $stream->_Id;
        $rental->_StartUnixtime = time();
        $rental->_ExpireUnixtime = $unixtime;
        $rental->_NoticeLink = $use_notice_index;
        $rental->_TotalAmount = $amountpaid;
        $status = $rental->createEntry()->status;
        if ($status == false) {
            $this->failed("Unable to create rental");
            return;
        }

        $stream->_RentalLink = $rental->_Id;
        $status = $stream->updateEntry()->status;
        if ($status == false) {
            $this->failed("Unable to update rental link for stream");
            return;
        }

        $this->sendStaticNotecard($package->_SetupNotecardLink, $avatar);

        if ($rentals->getCount() == 0) {
            $this->sendStaticNotecard($package->_WelcomeNotecardLink, $avatar);
        }


        $TransactionsHelper = new TransactionsHelper();

        $status = $TransactionsHelper->createTransaction(
            $avatar,
            $package,
            $stream,
            $this->reseller,
            $this->region,
            $amountpaid
        );
        if ($status == false) {
            $this->failed("Unable to create transaction: " . $TransactionsHelper->whyfailed);
            return;
        }
        $details = new Detail();
        $details->_RentalLink = $rental->_Id;
        $create = $details->createEntry();
        if ($create->status == false) {
            $this->failed("Unable to create details request");
            return;
        }
        $avatar_system = new Avatar();
        $this->setSwapTag("owner_payment", 0);
        if ($avatar_system->loadID($this->siteConfig->getSlConfig()->_OwnerAvatarLink) == false) {
            $this->failed("Unable to load owner avatar");
            return;
        }
        if ($this->owner_override == false) {
            $left_over = $amountpaid;
            if ($this->reseller->_Rate > 0) {
                $one_p = $amountpaid / 100;
                $reseller_cut = floor($one_p * $this->reseller->_Rate);
                $left_over = $amountpaid - $reseller_cut;
                if ($reseller_cut < 1) {
                    if ($left_over >= 2) {
                        $left_over--;
                        $reseller_cut++;
                    }
                }
            }
            $this->setSwapTag("owner_payment", 1);
            $this->setSwapTag("owner_payment_amount", $left_over);
            $this->setSwapTag("owner_payment_uuid", $avatar_system->_AvatarUUID);
        }

        if ($this->useCredits($avatar_system, $avatar, $package, $stream, $amountpaid) == false) {
            return;
        }

        $EventsQHelper = new EventsQHelper();
        $EventsQHelper->addToEventQ("RentalStart", $package, $avatar, $server, $stream, $rental, $amountpaid);

        if ($package->_EnableGroupInvite == true) {
            $botHelper = new BotHelper();
            $botHelper->sendBotInvite($avatar);
        }
        $this->ok("Details should be with you shortly");
    }
}
