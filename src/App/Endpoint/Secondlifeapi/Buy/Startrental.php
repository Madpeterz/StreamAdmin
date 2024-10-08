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
use App\Models\Sets\NoticeSet;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Sets\RentalSet;
use App\Models\Sets\StreamSet;
use App\Template\SecondlifeAjax;

class Startrental extends SecondlifeAjax
{
    protected function notBanned(Avatar $avatar): bool
    {
        $banlist = new Banlist();
        $banlist->loadByAvatarLink($avatar->getId());
        if ($banlist->getId() > 0) {
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
        "fields" => ["rentalLink","packageLink","needWork"],
        "values" => [null,$package->getId(),0],
        "types" => ["i","i","i"],
        "matches" => ["IS","=","="],
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
        if ($avatar->getId() < 1) {
            return;
        }
        $noticeNotecard = new Noticenotecard();
        if ($noticeNotecard->loadID($staticNotecardid)->status == false) {
            return;
        }
        if ($noticeNotecard->getMissing() == true) {
            return;
        }
        $notecardmail = new ModelNotecardmail();
        $notecardmail->setAvatarLink($avatar->getId());
        $notecardmail->setNoticenotecardLink($noticeNotecard->getId());
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
        }

        $stream = $this->getUnassignedStreamOnPackage($package);
        if ($stream == null) {
            $this->failed("Unable to find a unsold stream in that package");
            return;
        }

        $server = new Server();
        if ($server->loadID($stream->getServerLink()) == false) {
            $this->failed("Unable to find the server attached to the stream");
            return;
        }

        $amountpaid = $this->input->post("amountpaid")->checkGrtThanEq(1)->asInt();
        $accepted_payment_amounts = [
        $package->getCost() => 1,
        ($package->getCost() * 2) => 2,
        ($package->getCost() * 3) => 3,
        ($package->getCost() * 4) => 4,
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
        $hours_remain = ($package->getDays() * 24) * $multipler;

        $noticesHelper = new NoticesHelper();
        $use_notice_index = $noticesHelper->getNoticeLevel($hours_remain);
        $unixtime = time() + ($hours_remain * $this->siteConfig->unixtimeHour());

        $rentals = new RentalSet();
        $rentals->loadByAvatarLink($avatar->getId());

        $rental = new Rental();
        $uid_rental = $rental->createUID("rentalUid", 8);
        $status = $uid_rental->status;
        if ($status == false) {
            $this->failed("Unable to create rental uid");
            return;
        }
        $rental->setRentalUid($uid_rental->uid);
        $rental->setAvatarLink($avatar->getId());
        $rental->setPackageLink($stream->getPackageLink());
        $rental->setStreamLink($stream->getId());
        $rental->setStartUnixtime(time());
        $rental->setExpireUnixtime($unixtime);
        $rental->setNoticeLink($use_notice_index);
        $rental->setTotalAmount($amountpaid);
        $status = $rental->createEntry()->status;
        if ($status == false) {
            $this->failed("Unable to create rental");
            return;
        }

        $stream->setRentalLink($rental->getId());
        $status = $stream->updateEntry()->status;
        if ($status == false) {
            $this->failed("Unable to update rental link for stream");
            return;
        }

        $this->sendStaticNotecard($package->getSetupNotecardLink(), $avatar);

        if ($rentals->getCount() == 0) {
            $this->sendStaticNotecard($package->getWelcomeNotecardLink(), $avatar);
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
            $this->failed("Unable to create transaction");
            return;
        }
        $details = new Detail();
        $details->setRentalLink($rental->getId());
        $create = $details->createEntry();
        if ($create->status == false) {
            $this->failed("Unable to create details request");
            return;
        }

        $this->setSwapTag("owner_payment", 0);
        if ($this->owner_override == false) {
            $avatar_system = new Avatar();
            if ($avatar_system->loadID($this->siteConfig->getSlConfig()->getOwnerAvatarLink()) == false) {
                $this->failed("Unable to load owner avatar");
                return;
            }
            $left_over = $amountpaid;
            if ($this->reseller->getRate() > 0) {
                $one_p = $amountpaid / 100;
                $reseller_cut = floor($one_p * $this->reseller->getRate());
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
            $this->setSwapTag("owner_payment_uuid", $avatar_system->getAvatarUUID());
        }

        $EventsQHelper = new EventsQHelper();
        $EventsQHelper->addToEventQ("RentalStart", $package, $avatar, $server, $stream, $rental, $amountpaid);

        if ($package->getEnableGroupInvite() == true) {
            $botHelper = new BotHelper();
            $botHelper->sendBotInvite($avatar);
        }
        $this->ok("Details should be with you shortly");
    }
}
