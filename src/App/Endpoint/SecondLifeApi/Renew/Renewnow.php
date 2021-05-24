<?php

namespace App\Endpoint\SecondLifeApi\Renew;

use App\Helpers\AvatarHelper;
use App\MediaServer\Logic\ApiLogicRenew;
use App\R7\Model\Avatar;
use App\R7\Model\Banlist;
use App\R7\Set\NoticeSet;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Stream;
use App\R7\Model\Transactions;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Renewnow extends SecondlifeAjax
{
    protected InputFilter $input;
    protected Rental $rental;
    protected Stream $stream;
    protected Package $package;
    protected Avatar $avatar;
    protected Transactions $transaction;
    protected $amountpaid = 0;
    protected $multipler = 0;
    protected array $uid_transaction = [];

    protected function setup(): void
    {
        $this->input = new InputFilter();
        $this->rental = new Rental();
        $this->stream = new Stream();
        $this->package = new Package();
        $this->avatar = new Avatar();
        $this->transaction = new Transactions();
    }

    protected function load(): bool
    {
        $rentalUid = $this->input->postFilter("rentalUid");
        $avatarUUID = $this->input->postFilter("avatarUUID", "uuid");
        $avatarName = $this->input->postFilter("avatarName");
        $this->amountpaid = $this->input->postFilter("amountpaid", "integer");

        if ($this->rental->loadByField("rentalUid", $rentalUid) == false) {
            $this->setSwapTag("message", "Unable to find rental");
            return false;
        }

        if ($this->stream->loadID($this->rental->getStreamLink()) == false) {
            $this->setSwapTag("message", "Unable to find stream");
            return false;
        }

        if ($this->package->loadID($this->stream->getPackageLink()) == false) {
            $this->setSwapTag("message", "Unable to find package");
            return false;
        }

        $avatar_helper = new AvatarHelper();
        $get_av_status = $avatar_helper->loadOrCreate($avatarUUID, $avatarName);
        if ($get_av_status == false) {
            $this->setSwapTag("message", "Unable to find avatar");
            return false;
        }
        $this->avatar = $avatar_helper->getAvatar();

        $banlist = new Banlist();
        if ($banlist->loadByField("avatarLink", $this->avatar->getId()) == true) {
            $this->setSwapTag("message", "Unable to find avatar");
            return false;
        }

        return true;
    }

    protected function startTransaction(): bool
    {
        $this->uid_transaction = $this->transaction->createUID("transactionUid", 8, 10);
        if ($this->uid_transaction["status"] == false) {
            $this->setSwapTag("message", "Unable to create transaction uid");
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
            $this->setSwapTag("message", "payment not accepted (Invaild amount)");
            return false;
        }
        $this->multipler = $accepted_payment_amounts[$this->amountpaid];
        return true;
    }

    protected function finalizeTransaction(): bool
    {
        $this->transaction->setAvatarLink($this->avatar->getId());
        $this->transaction->setPackageLink($this->package->getId());
        $this->transaction->setStreamLink($this->stream->getId());
        $this->transaction->setResellerLink($this->reseller->getId());
        $this->transaction->setRegionLink($this->region->getId());
        $this->transaction->setAmount($this->amountpaid);
        $this->transaction->setUnixtime(time());
        $this->transaction->setTransactionUid($this->uid_transaction["uid"]);
        $this->transaction->setRenew(true);
        if ($this->transaction->createEntry()["status"] == false) {
            $this->setSwapTag("message", "Unable to create transaction");
            return false;
        }
        return true;
    }

    protected function getNoticeLevelIndex(array $sorted_linked, int $hours_remain): int
    {
        $use_notice_index = 0;
        $break_next = false;
        foreach ($sorted_linked as $hours => $index) {
            if ($hours > $hours_remain) {
                if ($break_next == false) {
                    $break_next = true;
                    $use_notice_index = $index;
                } else {
                    break;
                }
            }
        }
        return $use_notice_index;
    }

    protected function setUpdatedRentalDetails(): void
    {
        global $unixtime_day;
        $unixtime_to_add = (($this->package->getDays() * $unixtime_day) * $this->multipler);
        $new_expires_time = $this->rental->getExpireUnixtime() + $unixtime_to_add;
        $this->rental->setExpireUnixtime($new_expires_time);
        $this->rental->setRenewals(($this->rental->getRenewals() + $this->multipler));
        $this->rental->setTotalAmount(($this->rental->getTotalAmount() + $this->amountpaid));
        $unixtime_remain = $new_expires_time - time();
        if ($unixtime_remain <= 0) {
            $this->processNoticeChange();
        }
    }

    protected function processNoticeChange($unixtime_remain): void
    {
        global $unixtime_hour;
        $hours_remain = ceil($unixtime_remain / $unixtime_hour);
        $notice_set = new NoticeSet();
        $notice_set->loadAll();
        $sorted_linked = $notice_set->getLinkedArray("hoursRemaining", "id");
        ksort($sorted_linked, SORT_NUMERIC);
        $use_notice_index = $this->getNoticeLevelIndex($sorted_linked, $hours_remain);
        if ($use_notice_index != 0) {
            if ($this->rental->getNoticeLink() != $use_notice_index) {
                $this->rental->setNoticeLink($use_notice_index);
            }
        }
        return;
    }

    protected function saveRental(): bool
    {
        if ($this->rental->updateEntry()["status"] == false) {
            $this->setSwapTag("message", "Unable to update rental");
            return false;
        }
        return true;
    }

    protected function processResellerCut(): bool
    {
        $this->setSwapTag("owner_payment", 0);
        if ($this->owner_override == false) {
            $avatar_system = new Avatar();
            if ($avatar_system->loadID($this->slconfig->getOwnerAvatarLink()) == false) {
                $this->setSwapTag("message", "Unable to find system owner avatar");
                return false;
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
        }
        return true;
    }

    protected function userMessage(): void
    {
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Payment on account but account is still in arrears");
        if ($this->rental->getExpireUnixtime() < time()) {
            return;
        }
        $this->setSwapTag(
            "message",
            sprintf(
                "Payment accepted there is now: %1\$s remaining you will next need to renew %2\$s",
                timeleftHoursAndDays($this->rental->getExpireUnixtime()),
                date('l jS \of F Y h:i:s A', $this->rental->getExpireUnixtime())
            )
        );
    }

    protected function apiProcess(): void
    {
        $this->setSwapTag("status", false);
        $apilogic = new ApiLogicRenew();
        $apilogic->setStream($this->stream);
        $apilogic->setRental($this->rental);
        $reply = $apilogic->createNextApiRequest();
        if ($reply["status"] == false) {
            $this->setSwapTag("message", "API server logic has failed on ApiLogicRenew: " . $reply["message"]);
            return;
        }
        $this->setSwapTag("status", true);
    }

    public function process(): void
    {
        $this->setup();
        if ($this->load() == false) {
            return;
        }
        if ($this->acceptPaymentAmount() == false) {
            return;
        }
        if ($this->startTransaction() == false) {
            return;
        }

        $this->setUpdatedRentalDetails();

        if ($this->saveRental() == false) {
            return;
        }
        if ($this->finalizeTransaction() == false) {
            return;
        }
        if ($this->processResellerCut() == false) {
            return;
        }

        $this->userMessage();
    }
}
