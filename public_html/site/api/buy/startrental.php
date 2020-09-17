<?php
function not_banned(avatar $avatar) : bool
{
    $banlist = new banlist();
    $banlist->load_by_field("avatar_link",$avatar->get_id());
    if($banlist->is_loaded() == true)
    {
        return false;
    }
    else
    {
        return true;
    }
}
function get_avatar(string $avataruuid,string $avatarname) : ?avatar
{
    $avatar_helper = new avatar_helper();
    $get_av_status = $avatar_helper->load_or_create($avataruuid,$avatarname);
    if($get_av_status == true)
    {
        return $avatar_helper->get_avatar();
    }
    return null;
}
function create_transaction(avatar $avatar,package $package,stream $stream,reseller $reseller,region $region,int $amountpaid) : bool
{
    $transaction = new transactions();
    $uid_transaction = $transaction->create_uid("transaction_uid",8,10);
    if($uid_transaction["status"] == true)
    {
        $transaction->set_avatarlink($avatar->get_id());
        $transaction->set_packagelink($package->get_id());
        $transaction->set_streamlink($stream->get_id());
        $transaction->set_resellerlink($reseller->get_id());
        $transaction->set_regionlink($region->get_id());
        $transaction->set_amount($amountpaid);
        $transaction->set_unixtime(time());
        $transaction->set_transaction_uid($uid_transaction["uid"]);
        $transaction->set_renew(false);
        $create_status = $transaction->create_entry();
        return $create_status["status"];
    }
    return false;
}
function get_package(string $packageuid) : ?package
{
    $package = new package();
    if($package->load_by_field("package_uid",$packageuid) == true)
    {
        return $package;
    }
    return null;
}
function get_unassigned_stream_on_package(package $package) : ?stream
{
    $apirequests_set = new api_requests_set();
    $apirequests_set->loadAll();
    $used_stream_ids = $apirequests_set->get_unique_array("streamlink");
    $where_config = array(
        "fields" => array("rentallink","packagelink","needwork","id"),
        "values" => array(NULL,$package->get_id(),0,$used_stream_ids),
        "types" => array("i","i","i","i"),
        "matches" => array("IS","=","=","NOT IN")
    );
    $stream_set = new stream_set();
    $stream_set->load_with_config($where_config);
    if($stream_set->get_count() > 0)
    {
        $stream_id = $stream_set->get_all_ids()[rand(0,$stream_set->get_count()-1)];
        $streamfinder = $stream_set->get_object_by_id($stream_id);
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
if($package == null) // find package
{
    $status = false;
    $why_failed = $lang["buy.sr.error.1"];
}
if($status == true) // find avatar
{
    $avatar = get_avatar($input->postFilter("avataruuid"),$input->postFilter("avatarname"));
    if($avatar == null)
    {
        $status = false;
        $why_failed = $lang["buy.sr.error.3"];
    }
}
if($status == true) // check banlist
{
    if(not_banned($avatar) == false)
    {
        $status = false;
        $why_failed = $lang["buy.sr.error.2.banned"];
    }
}
if($status == true) // find stream
{
    $stream = get_unassigned_stream_on_package($package);
    if($stream == null)
    {
        $status = false;
        $why_failed = $lang["buy.sr.error.2"];
    }
}
if($status == true) // check payment amount
{
    $amountpaid = $input->postFilter("amountpaid","integer");
    $accepted_payment_amounts = array(
        $package->get_cost()=>1,
        ($package->get_cost()*2)=>2,
        ($package->get_cost()*3)=>3,
        ($package->get_cost()*4)=>4
    );
    if(array_key_exists($amountpaid,$accepted_payment_amounts) == true)
    {
        // get expire unixtime and notice index
        $notice_set = new notice_set();
        $notice_set->loadAll();
        $sorted_linked = $notice_set->get_linked_array("hoursremaining","id");
        ksort($sorted_linked,SORT_NUMERIC);
        $multipler = $accepted_payment_amounts[$amountpaid];
        $hours_remain = ($package->get_days()*24)*$multipler;
        $use_notice_index = 0;
        foreach($sorted_linked as $hours => $index)
        {
            if($hours > $hours_remain) break;
            else
            {
                $use_notice_index = $index;
            }
        }
        $unixtime = time() + ($hours_remain * $unixtime_hour);
    }
    else
    {
        $status = false;
        $why_failed = $lang["buy.sr.error.5"];
    }
}
if($status == true) // create rental
{
    $rental = new rental();
    $uid_rental = $rental->create_uid("rental_uid",8,10);
    $status = $uid_rental["status"];
    if($status == true)
    {
        $rental->set_rental_uid($uid_rental["uid"]);
        $rental->set_avatarlink($avatar->get_id());
        $rental->set_packagelink($stream->get_packagelink());
        $rental->set_streamlink($stream->get_id());
        $rental->set_startunixtime(time());
        $rental->set_expireunixtime($unixtime);
        $rental->set_noticelink($use_notice_index);
        $rental->set_totalamount($amountpaid);
        $status = $rental->create_entry()["status"];
        if($status != true)
        {
            $why_failed = $lang["buy.sr.error.7"];
        }
    }
    else
    {
        $why_failed = $lang["buy.sr.error.6"];
    }
}
if($status == true) // link rental to stream
{
    $stream->set_rentallink($rental->get_id());
    $status = $stream->save_changes()["status"];
    if($status != true)
    {
        $why_failed = $lang["buy.sr.error.8"];
    }
}
if($status == true)
{
    $status = create_transaction($avatar,$package,$stream,$reseller,$region,$amountpaid);
    if($status == false)
    {
        $why_failed = $lang["buy.sr.error.4"];
    }
}
if($status == true) // process reseller cut / owner cut
{
    if($owner_override == false)
    {
        $avatar_system = new avatar();
        if($avatar_system->load($slconfig->get_owner_av()) == true)
        {
            $one_p = floor($amountpaid / 100);
            $reseller_cut = $one_p * $reseller->get_rate();
            $left_over = $amountpaid - $reseller_cut;
            $reply["owner_payment"] = 1;
            $reply["owner_payment_amount"] = $left_over;
            $reply["owner_payment_uuid"] = $avatar_system->get_avataruuid();
        }
        else
        {
            $status = false;
            $why_failed = $lang["buy.sr.error.12"];
        }
    }
    else
    {
        $reply["owner_payment"] = 0;
    }
}
if($status == true)  // event storage engine (to be phased out)
{
    if($slconfig->get_eventstorage() == true)
    {
        $event = new event();
        $event->set_avatar_uuid($avatar->get_avataruuid());
        $event->set_avatar_name($avatar->get_avatarname());
        $event->set_rental_uid($rental->get_rental_uid());
        $event->set_package_uid($package->get_package_uid());
        $event->set_event_new(true);
        $event->set_unixtime(time());
        $event->set_expire_unixtime($rental->get_expireunixtime());
        $event->set_port($stream->get_port());
        $status = $event->create_entry()["status"];
        if($status == false)
        {
            $why_failed = $lang["buy.sr.error.11"];
        }
    }
}
if($status == true) // api storage engine
{
    include("site/api_serverlogic/buy.php");
    $status = $api_serverlogic_reply;
    if($status == true)
    {
        if($no_api_action == true)
        {
            // trigger sending details
            $status = create_pending_api_request($server,$stream,$rental,"core_send_details",$lang["buy.sr.error.10"]);
        }
    }
}
if($status == false) // final output
{
    print $why_failed;
}
else
{
    print $lang["buy.sr.info.1"];
}
?>
