<?php

function not_banned(avatar $avatar): bool
{
    $banlist = new banlist();
    $banlist->loadByField("avatar_link", $avatar->getId());
    if ($banlist->is_loaded() == true) {
        return false;
    } else {
        return true;
    }
}
function get_avatar(string $avataruuid, string $avatarname): ?avatar
{
    $avatar_helper = new avatar_helper();
    $get_av_status = $avatar_helper->load_or_create($avataruuid, $avatarname);
    if ($get_av_status == true) {
        return $avatar_helper->get_avatar();
    }
    return null;
}
function create_transaction(avatar $avatar, package $package, stream $stream, reseller $reseller, region $region, int $amountpaid): bool
{
    $transaction = new transactions();
    $uid_transaction = $transaction->createUID("transaction_uid", 8, 10);
    if ($uid_transaction["status"] == true) {
        $transaction->setAvatarlink($avatar->getId());
        $transaction->setPackagelink($package->getId());
        $transaction->setStreamlink($stream->getId());
        $transaction->set_resellerlink($reseller->getId());
        $transaction->set_regionlink($region->getId());
        $transaction->set_amount($amountpaid);
        $transaction->set_unixtime(time());
        $transaction->set_transaction_uid($uid_transaction["uid"]);
        $transaction->set_renew(false);
        $create_status = $transaction->createEntry();
        return $create_status["status"];
    }
    return false;
}
function get_package(string $packageuid): ?package
{
    $package = new package();
    if ($package->loadByField("package_uid", $packageuid) == true) {
        return $package;
    }
    return null;
}
function get_unassigned_stream_on_package(package $package): ?stream
{
    $apirequests_set = new api_requests_set();
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
    $stream_set = new stream_set();
    $stream_set->loadWithConfig($where_config);
    if ($stream_set->getCount() > 0) {
        $stream_id = $stream_set->getAllIds()[rand(0, $stream_set->getCount() - 1)];
        $streamfinder = $stream_set->getObjectByID($stream_id);
        return $streamfinder;
    }
    return null;
}

$input = new inputFilter();
$why_failed = "";
$package = null;
$stream = null;
$avatar = null;
$hours_remain = 0;
$amountpaid = 0;
$use_notice_index = 0;

$status = true;
$package = get_package($input->postFilter("packageuid"));
if ($package == null) { // find package
    $status = false;
    $why_failed = $lang["buy.sr.error.1"];
}
if ($status == true) { // find avatar
    $avatar = get_avatar($input->postFilter("avataruuid"), $input->postFilter("avatarname"));
    if ($avatar == null) {
        $status = false;
        $why_failed = $lang["buy.sr.error.3"];
    }
}
if ($status == true) { // check banlist
    if (not_banned($avatar) == false) {
        $status = false;
        $why_failed = $lang["buy.sr.error.2.banned"];
    }
}
if ($status == true) { // find stream
    $stream = get_unassigned_stream_on_package($package);
    if ($stream == null) {
        $status = false;
        $why_failed = $lang["buy.sr.error.2"];
    }
}
if ($status == true) { // check payment amount
    $amountpaid = $input->postFilter("amountpaid", "integer");
    $accepted_payment_amounts = [
        $package->getCost() => 1,
        ($package->getCost() * 2) => 2,
        ($package->getCost() * 3) => 3,
        ($package->getCost() * 4) => 4,
    ];
    if (array_key_exists($amountpaid, $accepted_payment_amounts) == true) {
        // get expire unixtime and notice index
        $notice_set = new notice_set();
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
    } else {
        $status = false;
        $why_failed = $lang["buy.sr.error.5"];
    }
}
if ($status == true) { // create rental
    $rental = new rental();
    $uid_rental = $rental->createUID("rental_uid", 8, 10);
    $status = $uid_rental["status"];
    if ($status == true) {
        $rental->setRental_uid($uid_rental["uid"]);
        $rental->setAvatarlink($avatar->getId());
        $rental->setPackagelink($stream->getPackagelink());
        $rental->setStreamlink($stream->getId());
        $rental->setStartunixtime(time());
        $rental->setExpireunixtime($unixtime);
        $rental->setNoticelink($use_notice_index);
        $rental->set_totalamount($amountpaid);
        $status = $rental->createEntry()["status"];
        if ($status != true) {
            $why_failed = $lang["buy.sr.error.7"];
        }
    } else {
        $why_failed = $lang["buy.sr.error.6"];
    }
}
if ($status == true) { // link rental to stream
    $stream->setRentallink($rental->getId());
    $status = $stream->updateEntry()["status"];
    if ($status != true) {
        $why_failed = $lang["buy.sr.error.8"];
    }
}
if ($status == true) {
    $status = create_transaction($avatar, $package, $stream, $reseller, $region, $amountpaid);
    if ($status == false) {
        $why_failed = $lang["buy.sr.error.4"];
    }
}
if ($status == true) { // process reseller cut / owner cut
    if ($owner_override == false) {
        $avatar_system = new avatar();
        if ($avatar_system->loadID($slconfig->getOwner_av()) == true) {
            $left_over = $amountpaid;
            if ($reseller->getRate() > 0) {
                $one_p = $amountpaid / 100;
                $reseller_cut = floor($one_p * $reseller->getRate());
                $left_over = $amountpaid - $reseller_cut;
                if ($reseller_cut < 1) {
                    if ($left_over >= 2) {
                        $left_over--;
                        $reseller_cut++;
                    }
                }
            }
            $reply["owner_payment"] = 1;
            $reply["owner_payment_amount"] = $left_over;
            $reply["owner_payment_uuid"] = $avatar_system->getAvataruuid();
        } else {
            $status = false;
            $why_failed = $lang["buy.sr.error.12"];
        }
    } else {
        $reply["owner_payment"] = 0;
    }
}
if ($status == true) {  // event storage engine (to be phased out)
    if ($slconfig->get_eventstorage() == true) {
        $event = new event();
        $event->set_avatar_uuid($avatar->getAvataruuid());
        $event->set_avatar_name($avatar->getAvatarname());
        $event->setRental_uid($rental->getRental_uid());
        $event->setPackage_uid($package->getPackage_uid());
        $event->set_event_new(true);
        $event->set_unixtime(time());
        $event->set_expire_unixtime($rental->getExpireunixtime());
        $event->set_port($stream->getPort());
        $status = $event->createEntry()["status"];
        if ($status == false) {
            $why_failed = $lang["buy.sr.error.11"];
        }
    }
}
if ($status == true) { // api storage engine
    include "shared/media_server_apis/logic/buy.php";
    $status = $api_serverlogic_reply;
    if ($status == true) {
        if ($no_api_action == true) {
            // trigger sending details
            $status = create_pending_api_request($server, $stream, $rental, "core_send_details", $lang["buy.sr.error.10"]);
        }
    }
}
if ($status == false) { // final output
    echo $why_failed;
} else {
    echo $lang["buy.sr.info.1"];
}
