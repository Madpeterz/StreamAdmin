<?php

namespace App\Endpoints\SecondLifeApi\Buy;

use App\Models\ApirequestsSet;
use App\Models\Avatar;
use App\Models\Banlist;
use App\Models\NoticeSet;
use App\Models\Package;
use App\Models\Region;
use App\Models\Rental;
use App\Models\Reseller;
use App\Models\Stream;
use App\Models\StreamSet;
use App\Models\Transactions;
use App\Template\SecondlifeAjax;
use avatar_helper;
use YAPF\InputFilter\InputFilter;

class Startrental extends SecondlifeAjax
{
    protected function notBanned(Avatar $avatar): bool
    {
        $banlist = new Banlist();
        $banlist->loadByField("avatar_link", $avatar->getId());
        if ($banlist->getId() > 0) {
            return false;
        }
        return true;
    }

    protected function getAvatar(string $avataruuid, string $avatarname): ?Avatar
    {
        $avatar_helper = new avatar_helper();
        $get_av_status = $avatar_helper->loadOrCreate($avataruuid, $avatarname);
        if ($get_av_status == true) {
            return $avatar_helper->get_avatar();
        }
        return null;
    }

    protected function createTransaction(
        Avatar $avatar,
        Package $package,
        Stream $stream,
        Reseller $reseller,
        Region $region,
        int $amountpaid
    ): bool {
        $transaction = new Transactions();
        $uid_transaction = $transaction->createUID("transaction_uid", 8, 10);
        if ($uid_transaction["status"] == false) {
            return false;
        }
        $transaction->setAvatarlink($avatar->getId());
        $transaction->setPackagelink($package->getId());
        $transaction->setStreamlink($stream->getId());
        $transaction->setResellerlink($reseller->getId());
        $transaction->setRegionlink($region->getId());
        $transaction->setAmount($amountpaid);
        $transaction->setUnixtime(time());
        $transaction->setTransaction_uid($uid_transaction["uid"]);
        $transaction->setRenew(false);
        $create_status = $transaction->createEntry();
        return $create_status["status"];
    }

    protected function getPackage(string $packageuid): ?Package
    {
        $package = new Package();
        if ($package->loadByField("package_uid", $packageuid) == true) {
            return $package;
        }
        return null;
    }

    protected function getUnassignedStreamOnPackage(package $package): ?Stream
    {
        $apirequests_set = new ApirequestsSet();
        $apirequests_set->loadAll();
        $used_stream_ids = $apirequests_set->getUniqueArray("streamlink");
        $where_config = [
            "fields" => ["rentallink","packagelink","needwork"],
            "values" => [null,$package->getId(),0],
            "types" => ["i","i","i"],
            "matches" => ["IS","=","="],
        ];
        if (count($used_stream_ids) > 0) {
            $whereconfig["fields"][] = "id";
            $whereconfig["matches"][] = "NOT IN";
            $whereconfig["values"][] = $used_stream_ids;
            $whereconfig["types"][] = "i";
        }
        $stream_set = new StreamSet();
        $stream_set->loadWithConfig($where_config);
        if ($stream_set->getCount() > 0) {
            $stream_id = $stream_set->getAllIds()[rand(0, $stream_set->getCount() - 1)];
            return $stream_set->getObjectByID($stream_id);
        }
        return null;
    }

    public function process(): void
    {
        global $unixtime_hour;
        $input = new InputFilter();
        $package = null;
        $stream = null;
        $avatar = null;
        $hours_remain = 0;
        $amountpaid = 0;
        $use_notice_index = 0;

        $package = $this->getPackage($input->postFilter("packageuid"));
        if ($package == null) { // find package
            $this->setSwapTag("message", "Unable to find");
            return;
        }

        $avatar = $this->getAvatar($input->postFilter("avataruuid"), $input->postFilter("avatarname"));
        if ($avatar == null) {
            $this->setSwapTag("message", "Unable to attach avatar");
            return;
        }

        if ($this->notBanned($avatar) == false) {
            $this->setSwapTag("message", "Unable to attach avatar");
            return;
        }

        $stream = $this->getUnassignedStreamOnPackage($package);
        if ($stream == null) {
            $this->setSwapTag("message", "Unable to find a unsold stream in that package");
            return;
        }

        $amountpaid = $input->postFilter("amountpaid", "integer");
        $accepted_payment_amounts = [
            $package->getCost() => 1,
            ($package->getCost() * 2) => 2,
            ($package->getCost() * 3) => 3,
            ($package->getCost() * 4) => 4,
        ];
        if (array_key_exists($amountpaid, $accepted_payment_amounts) == false) {
            $this->setSwapTag("message", "Payment amount not accepted");
            return;
        }
        // get expire unixtime and notice index
        $notice_set = new NoticeSet();
        $notice_set->loadAll();
        $sorted_linked = $notice_set->getLinkedArray("hoursremaining", "id");
        ksort($sorted_linked, SORT_NUMERIC);
        $multipler = $accepted_payment_amounts[$amountpaid];
        $hours_remain = ($package->getDays() * 24) * $multipler;
        $use_notice_index = 0;
        foreach ($sorted_linked as $hours => $index) {
            if ($hours > $hours_remain) {
                break;
            } else {
                $use_notice_index = $index;
            }
        }
        $unixtime = time() + ($hours_remain * $unixtime_hour);

        $rental = new Rental();
        $uid_rental = $rental->createUID("rental_uid", 8, 10);
        $status = $uid_rental["status"];
        if ($status == false) {
            $this->setSwapTag("message", "Unable to create rental uid");
            return;
        }

        $rental->setRental_uid($uid_rental["uid"]);
        $rental->setAvatarlink($avatar->getId());
        $rental->setPackagelink($stream->getPackagelink());
        $rental->setStreamlink($stream->getId());
        $rental->setStartunixtime(time());
        $rental->setExpireunixtime($unixtime);
        $rental->setNoticelink($use_notice_index);
        $rental->setTotalamount($amountpaid);
        $status = $rental->createEntry()["status"];
        if ($status == false) {
            $this->setSwapTag("message", "Unable to create rental");
            return;
        }

        $stream->setRentallink($rental->getId());
        $status = $stream->updateEntry()["status"];
        if ($status == false) {
            $this->setSwapTag("message", "Unable to update rental link for stream");
            return;
        }

        $status = $this->createTransaction($avatar, $package, $stream, $this->reseller, $this->region, $amountpaid);
        if ($status == false) {
            $this->setSwapTag("message", "Unable to create transaction");
            return;
        }

        $this->setSwapTag("owner_payment", 0);
        if ($this->owner_override == false) {
            $avatar_system = new Avatar();
            if ($avatar_system->loadID($this->slconfig->getOwner_av()) == false) {
                $this->setSwapTag("message", "Unable to load owner avatar");
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
            $this->setSwapTag("owner_payment_uuid", $avatar_system->getAvataruuid());
        }

        include "shared/media_server_apis/logic/buy.php";
        $status = $api_serverlogic_reply;
        if ($status == true) {
            if ($no_api_action == true) {
                // trigger sending details
                $status = create_pending_api_request(
                    $server,
                    $stream,
                    $rental,
                    "core_send_details",
                    "Unable to create pending api request"
                );
            }
        }

        $this->setSwapTag("status", $status);
    }
}
