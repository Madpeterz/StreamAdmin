<?php
$input = new inputFilter();
$rental_uid = $input->postFilter("rental_uid");
$avataruuid = $input->postFilter("avataruuid");
$avatarname = $input->postFilter("avatarname");
$amountpaid = $input->postFilter("amountpaid","integer");
$status = false;
$rental = new rental();
if($rental->load_by_field("rental_uid",$rental_uid) == true)
{
    $rental_id = $rental->get_id();
    $stream = new stream();
    if($stream->load($rental->get_streamlink()) == true)
    {
        $package = new package();
        if($package->load($stream->get_packagelink()) == true)
        {
            $accepted_payment_amounts = array(($package->get_cost())=>1,($package->get_cost()*2)=>2,($package->get_cost()*3)=>3,($package->get_cost()*4)=>4);
            if(array_key_exists($amountpaid,$accepted_payment_amounts) == true)
            {
                $multipler = $accepted_payment_amounts[$amountpaid];
                $transaction = new transactions();
                $uid_transaction = $transaction->create_uid("transaction_uid",8,10);
                if($uid_transaction["status"] == true)
                {
                    $unixtime_to_add = (($package->get_days() * $unixtime_day)*$multipler);
                    $new_expires_time = $rental->get_expireunixtime() + $unixtime_to_add;
                    $rental->set_field("expireunixtime",$new_expires_time);
                    $rental->set_field("renewals",($rental->get_renewals()+1));
                    $unixtime_remain = $new_expires_time - time();
                    if($unixtime_remain > 0)
                    {
                        $update_notice_status = true;
                        $hours_remain = ceil($unixtime_remain/$unixtime_hour);

                        $notice_set = new notice_set();
                        $notice_set->loadAll();
                        $sorted_linked = $notice_set->get_linked_array("hoursremaining","id");
                        ksort($sorted_linked,SORT_NUMERIC);
                        $use_notice_index = 0;
                        $break_next = false;
                        foreach($sorted_linked as $hours => $index)
                        {
                            if($hours > $hours_remain)
                            {
                                if($break_next == false)
                                {
                                    $break_next = true;
                                    $use_notice_index = $index;
                                }
                                else break;
                            }
                        }
                        if($use_notice_index != 0)
                        {
                            if($rental->get_noticelink() != $use_notice_index)
                            {
                                $rental->set_field("noticelink",$use_notice_index);
                            }
                        }
                    }
                    $save_changes = $rental->save_changes();
                    if($save_changes["status"] == true)
                    {
                        $avatar_helper = new avatar_helper();
                        $get_av_status = $avatar_helper->load_or_create($avataruuid,$avatarname);
                        if($get_av_status == true)
                        {
                            $avatar = $avatar_helper->get_avatar();
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
                                        echo $lang["renew.rn.error.10"];
                                    }
                                }
                                else
                                {
                                    $status = true;
                                    $reply["owner_payment"] = 0;
                                }
                                if($status == true)
                                {
                                    if($rental->get_expireunixtime() > time())
                                    {
                                        $all_ok = true;
                                        if($slconfig->get_eventstorage() == true)
                                        {
                                            $event = new event();
                                            $event->set_field("avatar_uuid",$avatar->get_avataruuid());
                                            $event->set_field("avatar_name",$avatar->get_avatarname());
                                            $event->set_field("rental_uid",$rental->get_rental_uid());
                                            $event->set_field("package_uid",$package->get_package_uid());
                                            $event->set_field("event_renew",true);
                                            $event->set_field("unixtime",time());
                                            $event->set_field("expire_unixtime",$rental->get_expireunixtime());
                                            $event->set_field("port",$stream->get_port());
                                            $create_status = $event->create_entry();
                                            if($create_status["status"] == false)
                                            {
                                                $status = false;
                                                $all_ok = false;
                                                echo $lang["renew.rn.error.9"];
                                            }
                                        }
                                        if($all_ok == true)
                                        {
                                            echo sprintf($lang["renew.rn.info.2"],timeleft_hours_and_days($rental->get_expireunixtime()));
                                        }
                                    }
                                    else
                                    {
                                        echo $lang["renew.rn.info.1"];
                                    }
                                }
                            }
                            else
                            {
                                echo $lang["renew.rn.error.8"];
                            }
                        }
                        else
                        {
                            echo $lang["renew.rn.error.7"];
                        }
                    }
                    else
                    {
                        echo $lang["renew.rn.error.6"];
                    }
                }
                else
                {
                    echo $lang["renew.rn.error.5"];
                }
            }
            else
            {
                echo $lang["renew.rn.error.4"];
            }
        }
        else
        {
            echo $lang["renew.rn.error.3"];
        }
    }
    else
    {
        echo $lang["renew.rn.error.2"];
    }
}
else
{
    echo $lang["renew.rn.error.1"];
}
?>