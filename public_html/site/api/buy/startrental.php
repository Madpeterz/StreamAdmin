<?php
$input = new inputFilter();
$packageuid = $input->postFilter("packageuid");
$avataruuid = $input->postFilter("avataruuid");
$avatarname = $input->postFilter("avatarname");
$amountpaid = $input->postFilter("amountpaid","integer");
$status = false;
$package = new package();
if($package->load_by_field("package_uid",$packageuid) == true)
{
    $where_config = array(
        "fields" => array("rentallink","packagelink"),
        "values" => array(NULL,$package->get_id()),
        "types" => array("i","i"),
        "matches" => array("IS","=")
    );
    $stream_set = new stream_set();
    $stream_set->load_with_config($where_config);
    if($stream_set->get_count() > 0)
    {
        $avatar_helper = new avatar_helper();
        $get_av_status = $avatar_helper->load_or_create($avataruuid,$avatarname);
        if($get_av_status == true)
        {
            $avatar = $avatar_helper->get_avatar();
            $transaction = new transactions();
            $uid_transaction = $transaction->create_uid("transaction_uid",8,10);
            if($uid_transaction["status"] == true)
            {
                $accepted_payment_amounts = array(($package->get_cost())=>1,($package->get_cost()*2)=>2,($package->get_cost()*3)=>3,($package->get_cost()*4)=>4);
                if(array_key_exists($amountpaid,$accepted_payment_amounts) == true)
                {
                    $multipler = $accepted_payment_amounts[$amountpaid];
                    $notice_set = new notice_set();
                    $notice_set->loadAll();
                    $sorted_linked = $notice_set->get_linked_array("hoursremaining","id");
                    ksort($sorted_linked,SORT_NUMERIC);
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
                    $rental = new rental();
                    $uid_rental = $rental->create_uid("rental_uid",8,10);
                    if($uid_rental["status"] == true)
                    {
                        $stream_id = $stream_set->get_all_ids()[rand(0,$stream_set->get_count()-1)];
                        $stream = $stream_set->get_object_by_id($stream_id);
                        $rental->set_field("rental_uid",$uid_rental["uid"]);
                        $rental->set_field("avatarlink",$avatar->get_id());
                        $rental->set_field("packagelink",$stream->get_packagelink());
                        $rental->set_field("streamlink",$stream->get_id());
                        $rental->set_field("startunixtime",time());
                        $rental->set_field("expireunixtime",$unixtime);
                        $rental->set_field("noticelink",$use_notice_index);
                        $rental->set_field("totalamount",$amountpaid);
                        $create_status = $rental->create_entry();
                        if($create_status["status"] == true)
                        {
                            $stream->set_field("rentallink",$rental->get_id());
                            $update_status = $stream->save_changes();
                            if($update_status["status"] == true)
                            {
                                $transaction->set_field("avatarlink",$avatar->get_id());
                                $transaction->set_field("packagelink",$package->get_id());
                                $transaction->set_field("streamlink",$stream->get_id());
                                $transaction->set_field("resellerlink",$reseller->get_id());
                                $transaction->set_field("regionlink",$region->get_id());
                                $transaction->set_field("amount",$amountpaid);
                                $transaction->set_field("unixtime",time());
                                $transaction->set_field("transaction_uid",$uid_transaction["uid"]);
                                $create_status = $transaction->create_entry();
                                if($create_status["status"] == true)
                                {
                                    $detail = new detail();
                                    $detail->set_field("rentallink",$rental->get_id());
                                    $create_status = $detail->create_entry();
                                    if($create_status["status"] == true)
                                    {
                                        if($owner_override == false)
                                        {
                                            $avatar_system = new avatar();
                                            if($avatar_system->load($slconfig->get_owner_av()) == true)
                                            {
                                                $status = true;
                                                $one_p = floor($amountpaid / 100);
                                                $reseller_cut = $one_p * $reseller->get_rate();
                                                $left_over = $amountpaid - $reseller_cut;
                                                $reply["owner_payment"] = 1;
                                                $reply["owner_payment_amount"] = $left_over;
                                                $reply["owner_payment_uuid"] = $avatar_system->get_avataruuid();
                                            }
                                            else
                                            {
                                                echo $lang["buy.sr.error.12"];
                                            }
                                        }
                                        else
                                        {
                                            $status = true;
                                            $reply["owner_payment"] = 0;
                                        }
                                        if($status == true)
                                        {
                                            $all_ok = true;
                                            if($slconfig->get_eventstorage() == true)
                                            {
                                                $event = new event();
                                                $event->set_field("avatar_uuid",$avatar->get_avataruuid());
                                                $event->set_field("avatar_name",$avatar->get_avatarname());
                                                $event->set_field("rental_uid",$rental->get_rental_uid());
                                                $event->set_field("package_uid",$package->get_package_uid());
                                                $event->set_field("event_new",true);
                                                $event->set_field("unixtime",time());
                                                $event->set_field("expire_unixtime",$rental->get_expireunixtime());
                                                $event->set_field("port",$stream->get_port());
                                                $create_status = $event->create_entry();
                                                if($create_status["status"] == false)
                                                {
                                                    $status = false;
                                                    $all_ok = false;
                                                    echo $lang["buy.sr.error.11"];
                                                }
                                                else
                                                {
                                                    echo $lang["buy.sr.info.2"];
                                                }
                                            }
                                            else
                                            {
                                                echo $lang["buy.sr.info.1"];
                                            }
                                        }
                                    }
                                    else
                                    {
                                        echo $lang["buy.sr.error.10"];
                                    }
                                }
                                else
                                {
                                    echo $lang["buy.sr.error.9"];
                                }
                            }
                            else
                            {
                                echo $lang["buy.sr.error.8"];
                            }
                        }
                        else
                        {
                            echo $lang["buy.sr.error.7"];
                        }
                    }
                    else
                    {
                        echo $lang["buy.sr.error.6"];
                    }
                }
                else
                {
                    echo $lang["buy.sr.error.5"];
                }
            }
            else
            {
                echo $lang["buy.sr.error.4"];
            }
        }
        else
        {
            echo $lang["buy.sr.error.3"];
        }
    }
    else
    {
        echo $lang["buy.sr.error.2"];
    }
}
else
{
    echo $lang["buy.sr.error.1"];
}
?>
