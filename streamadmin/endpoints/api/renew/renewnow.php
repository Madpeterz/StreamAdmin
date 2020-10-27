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
                    $rental->set_expireunixtime($new_expires_time);
                    $rental->set_renewals(($rental->get_renewals()+$multipler));
                    $rental->set_totalamount(($rental->get_totalamount()+$amountpaid));
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
                                $rental->set_noticelink($use_notice_index);
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
                            $banlist = new banlist();
                            if($banlist->load_by_field("avatar_link",$avatar->get_id()) == false)
                            {
                                $transaction->set_avatarlink($avatar->get_id());
                                $transaction->set_packagelink($package->get_id());
                                $transaction->set_streamlink($stream->get_id());
                                $transaction->set_resellerlink($reseller->get_id());
                                $transaction->set_regionlink($region->get_id());
                                $transaction->set_amount($amountpaid);
                                $transaction->set_unixtime(time());
                                $transaction->set_transaction_uid($uid_transaction["uid"]);
                                $transaction->set_renew(1);
                                $create_status = $transaction->create_entry();
                                if($create_status["status"] == true)
                                {
                                    if($owner_override == false)
                                    {
                                        $avatar_system = new avatar();
                                        if($avatar_system->load($slconfig->get_owner_av()) == true)
                                        {
                                            $status = true;
                                            $left_over = $amountpaid;
                                            if($reseller->get_rate() > 0)
                                            {
                                                $one_p = $amountpaid / 100;
                                                $reseller_cut = floor($one_p * $reseller->get_rate());
                                                $left_over = $amountpaid - $reseller_cut;
                                                if($reseller_cut < 1)
                                                {
                                                    if($left_over >= 2)
                                                    {
                                                        $left_over--;
                                                        $reseller_cut++;
                                                    }
                                                }
                                            }
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
                                            // Event storage engine
                                            if($slconfig->get_eventstorage() == true)
                                            {
                                                $event = new event();
                                                $event->set_avatar_uuid($avatar->get_avataruuid());
                                                $event->set_avatar_name($avatar->get_avatarname());
                                                $event->set_rental_uid($rental->get_rental_uid());
                                                $event->set_package_uid($package->get_package_uid());
                                                $event->set_event_renew(true);
                                                $event->set_unixtime(time());
                                                $event->set_expire_unixtime($rental->get_expireunixtime());
                                                $event->set_port($stream->get_port());
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
                                                // Server API support
                                                include("shared/media_server_apis/logic/renew.php");
                                                $all_ok = $api_serverlogic_reply;
                                            }
                                            if($all_ok == true)
                                            {
                                                echo sprintf($lang["renew.rn.info.2"],timeleft_hours_and_days($rental->get_expireunixtime()),date('l jS \of F Y h:i:s A',$rental->get_expireunixtime()));
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
                                echo $lang["renew.rn.error.6.banned"];
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
