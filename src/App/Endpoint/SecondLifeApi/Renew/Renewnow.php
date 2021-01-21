<?php

namespace App\Endpoint\SecondLifeApi\Renew;

use App\Models\Avatar;
use App\Models\Banlist;
use App\Models\NoticeSet;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Stream;
use App\Models\Transactions;
use App\Template\SecondlifeAjax;
use avatar_helper;
use YAPF\InputFilter\InputFilter;

class Renewnow extends SecondlifeAjax
{
    public function process(): void
    {
        global $unixtime_day, $unixtime_hour;
        $input = new InputFilter();
        $rentalUid = $input->postFilter("rentalUid");
        $avatarUUID = $input->postFilter("avatarUUID", "uuid");
        $avatarName = $input->postFilter("avatarName");
        $amountpaid = $input->postFilter("amountpaid", "integer");
        $rental = new Rental();
        if ($rental->loadByField("rentalUid", $rentalUid) == false) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamLink()) == false) {
            $this->setSwapTag("message", "Unable to find stream");
            return;
        }
        $package = new Package();
        if ($package->loadID($stream->getPackageLink()) == false) {
            $this->setSwapTag("message", "Unable to find package");
            return;
        }
        $accepted_payment_amounts = [
            ($package->getCost()) => 1,
            ($package->getCost() * 2) => 2,
            ($package->getCost() * 3) => 3,
            ($package->getCost() * 4) => 4,
        ];
        if (array_key_exists($amountpaid, $accepted_payment_amounts) == false) {
            $this->setSwapTag("message", "payment not accepted (Invaild amount)");
            return;
        }
        $multipler = $accepted_payment_amounts[$amountpaid];
        $transaction = new Transactions();
        $uid_transaction = $transaction->createUID("transactionUid", 8, 10);
        if ($uid_transaction["status"] == false) {
            $this->setSwapTag("message", "Unable to create transaction uid");
            return;
        }

        $unixtime_to_add = (($package->getDays() * $unixtime_day) * $multipler);
        $new_expires_time = $rental->getExpireUnixtime() + $unixtime_to_add;
        $rental->setExpireUnixtime($new_expires_time);
        $rental->setRenewals(($rental->getRenewals() + $multipler));
        $rental->setTotalAmount(($rental->getTotalAmount() + $amountpaid));
        $unixtime_remain = $new_expires_time - time();
        if ($unixtime_remain > 0) {
            $hours_remain = ceil($unixtime_remain / $unixtime_hour);
            $notice_set = new NoticeSet();
            $notice_set->loadAll();
            $sorted_linked = $notice_set->getLinkedArray("hoursRemaining", "id");
            ksort($sorted_linked, SORT_NUMERIC);
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
            if ($use_notice_index != 0) {
                if ($rental->getNoticeLink() != $use_notice_index) {
                    $rental->setNoticeLink($use_notice_index);
                }
            }
        }
        if ($rental->updateEntry()["status"] == false) {
            $this->setSwapTag("message", "Unable to update rental");
            return;
        }
        $avatar_helper = new avatar_helper();
        $get_av_status = $avatar_helper->loadOrCreate($avatarUUID, $avatarName);
        if ($get_av_status == false) {
            $this->setSwapTag("message", "Unable to find avatar");
            return;
        }
        $avatar = $avatar_helper->get_avatar();
        $banlist = new Banlist();
        if ($banlist->loadByField("avatarLink", $avatar->getId()) == true) {
            $this->setSwapTag("message", "Unable to find avatar");
            return;
        }
        $transaction->setAvatarLink($avatar->getId());
        $transaction->setPackageLink($package->getId());
        $transaction->setStreamLink($stream->getId());
        $transaction->setResellerLink($this->reseller->getId());
        $transaction->setRegionLink($this->region->getId());
        $transaction->setAmount($amountpaid);
        $transaction->setUnixtime(time());
        $transaction->setTransactionUid($uid_transaction["uid"]);
        $transaction->setRenew(true);
        if ($transaction->createEntry()["status"] == false) {
            $this->setSwapTag("message", "Unable to create transaction");
            return;
        }
        $this->setSwapTag("owner_payment", 0);
        if ($this->owner_override == false) {
            $avatar_system = new Avatar();
            if ($avatar_system->loadID($this->slconfig->getOwnerAvatarLink()) == false) {
                $this->setSwapTag("message", "Unable to find system owner avatar");
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
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "Payment account but account is still in arrears");
        if ($rental->getExpireUnixtime() > time()) {
            $all_ok = true;
            if ($all_ok == true) {
                // Server API support
                include "shared/media_server_apis/logic/renew.php";
                $all_ok = $api_serverlogic_reply;
            }
            if ($all_ok == true) {
                $this->setSwapTag(
                    "message",
                    sprintf(
                        "Payment accepted there is now: %1\$s remaining you will next need to renew %2\$s",
                        timeleft_hours_and_days($rental->getExpireUnixtime()),
                        date('l jS \of F Y h:i:s A', $rental->getExpireUnixtime())
                    )
                );
            }
        }
    }
}
