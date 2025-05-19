<?php

namespace App\Endpoint\Secondlifeapi\Renew;

use App\Helpers\AvatarHelper;
use App\Helpers\EventsQHelper;
use App\Helpers\NoticesHelper;
use App\Helpers\TransactionsHelper;
use App\Models\Avatar;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Transactions;
use App\Models\Sets\BanlistSet;
use App\Template\SecondlifeAjax;
use YAPF\Framework\Responses\DbObjects\CreateUidReply;

class Renewnow extends SecondlifeAjax
{
    protected Rental $rental;
    protected ?Stream $stream;
    protected ?Package $package;
    protected Avatar $accountOwnerAvatar;
    protected Avatar $transactionAvatar;
    protected ?Server $server;
    protected Transactions $transaction;
    protected $amountpaid = 0;
    protected $multipler = 0;
    protected ?CreateUidReply $uid_transaction = null;

    protected ?string $rentalUid;
    protected ?string $avatarUUID;
    protected ?string $avatarName;

    protected bool $setupRun = false;

    protected function setup(): void
    {
        $this->rental = new Rental();
        $this->stream = new Stream();
        $this->package = new Package();
        $this->transactionAvatar = new Avatar();
        $this->accountOwnerAvatar = new Avatar();
        $this->transaction = new Transactions();
        $this->server = new Server();
        $this->setupRun = true;
    }

    protected function loadFromPost(): void
    {
        $this->rentalUid = $this->input->post("rentalUid")->asString();
        $this->avatarUUID = $this->input->post("avatarUUID")->isUuid()->asString();
        $this->avatarName = $this->input->post("avatarName")->asString();
        $this->amountpaid = $this->input->post("amountpaid")->asInt();
    }

    protected function load(?Avatar $forceMatchAv = null): bool
    {
        if ($this->rentalUid == null) {
            $this->failed("No rental selected for renewal");
            return false;
        }
        if ($this->rental->loadByRentalUid($this->rentalUid)->status == false) {
            $this->failed("Unable to find rental");
            return false;
        }
        $this->stream = $this->rental->relatedStream()->getFirst();
        $this->server = $this->stream->relatedServer()->getFirst();
        $this->package = $this->rental->relatedPackage()->getFirst();
        $this->accountOwnerAvatar = $this->rental->relatedAvatar()->getFirst();
        $test = [$this->stream, $this->server, $this->package, $this->accountOwnerAvatar];
        if (in_array(null, $test) == true) {
            $this->failed("One or more required objects did not load");
            return false;
        }

        $avatar_helper = new AvatarHelper();
        $get_av_status = $avatar_helper->loadOrCreate($this->avatarUUID, $this->avatarName);
        if ($get_av_status == false) {
            $this->failed("Unable to find avatar");
            return false;
        }
        $this->transactionAvatar = $avatar_helper->getAvatar();

        $banlistSet = new BanlistSet();
        $banlistSet->loadFromAvatarLinks([$this->accountOwnerAvatar->getId(), $this->transactionAvatar->getId()]);
        if ($banlistSet->getCount() > 0) {
            $this->failed("Unable to find avatar");
            return false;
        }

        if ($forceMatchAv == null) {
            return true;
        }
        if (
            ($this->accountOwnerAvatar->getId() == $forceMatchAv->getId()) &&
            ($this->transactionAvatar->getId() == $forceMatchAv->getId())
        ) {
            return true;
        }
        $this->failed("You can not renew other peoples rentals via the hud!");
        return false;
    }

    protected function startTransaction(): bool
    {
        $this->uid_transaction = $this->transaction->createUID("transactionUid", 8);
        if ($this->uid_transaction->status == false) {
            $this->failed("Unable to create transaction uid");
            return false;
        }
        return true;
    }

    protected function limitStreamTime(): bool
    {
        $slconfig = $this->siteConfig->getSlConfig();
        if ($slconfig->getLimitTime() == false) {
            return true;
        }
        $days = $this->rental->getExpireUnixtime() - time();
        if ($days > 0) {
            $days = $days / $this->siteConfig->unixtimeDay();
            if ($days < 0) {
                $days = 1;
            }
        }
        if ($days >= $slconfig->getMaxStreamTimeDays()) {
            $this->failed("payment not accepted (currently to long remaining on rental)");
            return false;
        }
        $unixtime_to_add = (($this->package->getDays() * $this->siteConfig->unixtimeDay()) * $this->multipler);
        $new_expires_time = $this->rental->getExpireUnixtime() + $unixtime_to_add;
        $days = $new_expires_time - time();
        if ($days > 0) {
            $days = $days / $this->siteConfig->unixtimeDay();
            if ($days < 0) {
                $days = 1;
            }
        }
        if ($days >= $slconfig->getMaxStreamTimeDays()) {
            $this->failed("payment not accepted (would go over limit for remaining time on rental)");
            return false;
        }
        return true;
    }

    protected function acceptPaymentAmount(): bool
    {
        $accepted_payment_amounts = [
            ($this->package->getCost()) => 1,
            ($this->package->getCost() * 2) => 2,
            ($this->package->getCost() * 3) => 3,
            ($this->package->getCost() * 4) => 4,
        ];
        if (array_key_exists($this->amountpaid, $accepted_payment_amounts) == false) {
            $this->failed("payment not accepted (Invaild amount)");
            return false;
        }
        $this->multipler = $accepted_payment_amounts[$this->amountpaid];
        return true;
    }

    protected function finalizeTransaction(?string $sltransactionUUID = null): bool
    {
        $this->transaction->setAvatarLink($this->transactionAvatar->getId());
        $this->transaction->setPackageLink($this->package->getId());
        $this->transaction->setStreamLink($this->stream->getId());
        $this->transaction->setResellerLink($this->reseller->getId());
        $this->transaction->setRegionLink($this->region->getId());
        $this->transaction->setAmount($this->amountpaid);
        $this->transaction->setUnixtime(time());
        $this->transaction->setTransactionUid($this->uid_transaction->uid);
        $this->transaction->setRenew(true);
        if ($sltransactionUUID != null) {
            $this->transaction->setViaHud(true);
            $this->transaction->setSLtransactionUUID($sltransactionUUID);
        }
        if ($this->transactionAvatar->getId() != $this->accountOwnerAvatar->getId()) {
            $this->transaction->setTargetAvatar($this->accountOwnerAvatar->getId());
        }
        if ($this->transaction->createEntry()->status == false) {
            $this->setSwapTag("message", "Unable to create transaction");
            return false;
        }
        return true;
    }

    protected function setUpdatedRentalDetails(): void
    {
        $unixtime_to_add = (($this->package->getDays() * $this->siteConfig->unixtimeDay()) * $this->multipler);
        $new_expires_time = $this->rental->getExpireUnixtime() + $unixtime_to_add;
        $this->rental->setExpireUnixtime($new_expires_time);
        $this->rental->setRenewals(($this->rental->getRenewals() + $this->multipler));
        $this->rental->setTotalAmount(($this->rental->getTotalAmount() + $this->amountpaid));
        $unixtime_remain = $new_expires_time - time();
        $old_notice_level = $this->rental->getNoticeLink();
        $this->rental->setNoticeLink(6);
        if ($unixtime_remain > 0) {
            $this->processNoticeChange($unixtime_remain);
        }

        $EventsQHelper = new EventsQHelper();

        $tagEvent = "RentalRenewAny";
        if (($old_notice_level == 6) && ($this->rental->getNoticeLink() != 6) && ($unixtime_remain > 0)) {
            $tagEvent = "RentalRenew";
        }
        $EventsQHelper->addToEventQ(
            $tagEvent,
            $this->package,
            $this->accountOwnerAvatar,
            $this->server,
            $this->stream,
            $this->rental,
            $this->amountpaid,
            $this->transactionAvatar,
        );
    }

    protected function processNoticeChange($unixtime_remain): void
    {
        $hours_remain = ceil($unixtime_remain / $this->siteConfig->unixtimeHour());
        $noticeHelper = new NoticesHelper();
        $use_notice_index = $noticeHelper->getNoticeLevel($hours_remain);
        if ($use_notice_index != 0) {
            if ($this->rental->getNoticeLink() != $use_notice_index) {
                $this->rental->setNoticeLink($use_notice_index);
            }
        }
        return;
    }

    protected function saveRental(): bool
    {
        if ($this->rental->updateEntry()->status == false) {
            $this->failed("Unable to update rental");
            return false;
        }
        return true;
    }

    protected function processResellerCut(): bool
    {
        $this->setSwapTag("owner_payment", 0);
        $avatar_system = new Avatar();
        if ($avatar_system->loadID($this->siteConfig->getSlConfig()->getOwnerAvatarLink())->status == false) {
            $this->failed("Unable to find system owner avatar");
            return false;
        }
        if ($this->transactionAvatar->getId() == $this->accountOwnerAvatar->getId()) {
            // can only use account credits for yourself
            // you can give avatars credits via the market place
            $this->useCredits(
                avatar_system: $avatar_system,
                avatar: $this->transactionAvatar,
                package: $this->package,
                stream: $this->stream,
                amountpaid: $this->amountpaid,
                renewal: true
            );
        }

        if ($this->owner_override == true) {
            return true;
        }
        $left_over = $this->amountpaid;
        if ($this->reseller->getRate() > 0) {
            $one_p = $this->amountpaid / 100;
            $reseller_cut = floor($one_p * $this->reseller->getRate());
            $left_over = $this->amountpaid - $reseller_cut;
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
        return true;
    }

    protected function userMessage(): void
    {
        if ($this->rental->getExpireUnixtime() < time()) {
            $this->ok("Payment on account but account is still in arrears");
            return;
        }
        $this->ok(
            sprintf(
                "Payment accepted there is now: %1\$s remaining you will next need to renew %2\$s",
                $this->timeRemainingHumanReadable($this->rental->getExpireUnixtime()),
                date('l jS \of F Y h:i:s A', $this->rental->getExpireUnixtime())
            )
        );
    }

    protected function startRenewal(?Avatar $forceMatchAv = null, ?string $sltransactionUUID = null): void
    {
        if ($this->load($forceMatchAv) == false) {
            return;
        } elseif ($this->acceptPaymentAmount() == false) {
            return;
        } elseif ($this->limitStreamTime() == false) {
            return;
        } elseif ($this->startTransaction() == false) {
            return;
        }
        $this->setUpdatedRentalDetails();
        if ($this->saveRental() == false) {
            return;
        } elseif ($this->finalizeTransaction($sltransactionUUID) == false) {
            return;
        } elseif ($this->processResellerCut() == false) {
            return;
        }
        $this->userMessage();
    }

    public function process(?Avatar $forceMatchAv = null, ?string $sltransactionUUID = null): void
    {
        $this->setup();
        $this->loadFromPost();
        $this->startRenewal($forceMatchAv, $sltransactionUUID);
    }
}
