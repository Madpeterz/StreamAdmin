<?php
$input = new inputFilter();
// adjustment
$adjustment_days = $input->postFilter("adjustment_days","integer");
$adjustment_hours = $input->postFilter("adjustment_hours","integer");
$adjustment_dir = $input->postFilter("adjustment_dir","bool"); // array(false=>"Remove",true=>"Add")
// transfer
$transfer_avataruid = $input->postFilter("transfer_avataruid");
// message
$message = $input->postFilter("message");
if(strlen($message) < 1) $message = null;

$actions_taken = "";
$status = false;
$redirect = "";

$rental = new rental();
$issues = "";
if($rental->load_by_field("rental_uid",$page) == true)
{
    if(strlen($transfer_avataruid) == 8)
    {
        // Transfer
        $avatar = new avatar();
        if($avatar->load_by_field("avatar_uid",$transfer_avataruid) == true)
        {
            $avatar_from = new avatar();
            if($avatar_from->load($rental->get_avatarlink()) == true)
            {
                $rental->set_avatarlink($avatar->get_id());
                $actions_taken .= $lang["client.up.info.2"];
                $message .= sprintf($lang["client.up.info.1"],date($lang["client.up.datetime.format"],time()),$avatar->get_avatarname(),$avatar->get_avatar_uid(),$avatar_from->get_avatarname(),$avatar_from->get_avatar_uid());
            }
            else
            {
                $issues .= $lang["client.up.error.5"];
            }
        }
    }
    if(($adjustment_days > 0) || ($adjustment_hours > 0))
    {
        $total_adjust_hours = 0;
        if($adjustment_hours > 0) $total_adjust_hours += $adjustment_hours;
        if($adjustment_days > 0) $total_adjust_hours += ($adjustment_days * 24);
        if($total_adjust_hours > 0)
        {
            $adjustment_unixtime = $unixtime_hour * $total_adjust_hours;
            $adjustment_text = $lang["client.up.info.ad.text"];
            if($adjustment_dir == false)
            {
                $adjustment_text = $lang["client.up.info.rm.text"];
                $new_unixtime = $rental->get_expireunixtime() - $adjustment_unixtime;
            }
            else
            {
                $new_unixtime = $rental->get_expireunixtime() + $adjustment_unixtime;
            }
            $add_days = 0;
            while($total_adjust_hours >= 24)
            {
                $add_days+=1;
                $total_adjust_hours-=24;
            }

            $adjustment_amount = $total_adjust_hours;
            $adjustment_type = $lang["client.up.info.adj.hour"];
            $adjustment_multi = "";
            if($add_days > 0)
            {
                $adjustment_amount = $add_days;
                $adjustment_type = $lang["client.up.info.adj.day"];
            }
            if($adjustment_amount > 1)
            {
                $adjustment_multi = $lang["client.up.info.adj.multiple"];
            }

            $adjustment_message = sprintf($lang["client.up.info.adjustment"],
                date($lang["client.up.datetime.format"],time()),
                $adjustment_text,
                $adjustment_amount,
                $adjustment_type,
                $adjustment_multi
            );
            $message = "".$adjustment_message."".$message."";


            $update_notice_status = true;

            $notice_set = new notice_set();
            $notice_set->loadAll();
            $dif_array = array();
            foreach($notice_set->get_all_ids() as $notice_id)
            {
                $notice = $notice_set->get_object_by_id($notice_id);
                if($notice->get_hoursremaining() > 0)
                {
                    $dif_array[$notice->get_id()] = (time() + ($notice->get_hoursremaining() * $unixtime_hour));
                }
            }
            $closest_diff = null;
            $closest_diff_index = 0;
            foreach($dif_array as $key => $value)
            {
                $diff = abs($new_unixtime - $value);
                if($closest_diff == null)
                {
                    $closest_diff = $diff;
                    $closest_diff_index = $key;
                }
                else
                {
                    if($diff < $closest_diff)
                    {
                        $closest_diff = $diff;
                        $closest_diff_index = $key;
                    }
                }
            }
            if($closest_diff_index != 0)
            {
                if($rental->get_noticelink() != $closest_diff_index)
                {
                    $rental->set_noticelink($closest_diff_index);
                }
            }
            $rental->set_expireunixtime($new_unixtime);
        }
        else
        {
            $issues .= $lang["client.up.error.4"];
        }
    }
    if($message != $rental->get_message())
    {
        $rental->set_message($message);
        $actions_taken .= $lang["client.up.info.5"];
    }
    if($actions_taken != "")
    {
        if($issues == "")
        {
            $change_status = $rental->save_changes();
            if($change_status["status"] == true)
            {
                $status = true;
                $redirect = "client/manage/".$page;
                echo $lang["client.up.info.6"];
            }
            else
            {
                echo sprintf($lang["client.up.error.3"],$change_status["message"]);
            }
        }
        else
        {
            echo $issues;
        }
    }
    else
    {
        echo $lang["client.up.error.2"];
    }
}
else
{
    $redirect = "client";
    echo $lang["client.up.error.1"];
}
?>
