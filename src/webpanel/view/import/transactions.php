<?php
$current_sql = $sql;
$old_sql = new mysqli_controler();
$old_sql->sqlStart_test($r4_db_username,$r4_db_pass,$r4_db_name,false,$r4_db_host);

$sql = $old_sql; // switch to r4

$r4_sales_tracking_set = new r4_sales_tracking_set();
$r4_sales_tracking_set->loadAll();

$sql = $current_sql; // swtich back to r7

$avatars = new avatar_set();
$avatars->loadAll();

$avatar_name_to_id = $avatars->get_linked_array("avatarname","id");

$all_ok = true;
$transactions_created = 0;

foreach($r4_sales_tracking_set->get_all_ids() as $r4_sales_tracking_id)
{
    $r4_sales_tracking = $r4_sales_tracking_set->get_object_by_id($r4_sales_tracking_id);

    $avatar_id = 1;
    if(array_key_exists($r4_sales_tracking->get_SLname(),$avatar_name_to_id) == true)
    {
        $avatar_id = $avatar_name_to_id[$r4_sales_tracking->get_SLname()];
    }

    $transaction = new transactions();
    $uid_transaction = $transaction->create_uid("transaction_uid",8,10);
    if($uid_transaction["status"] == true)
    {
        $date = explode("/",$r4_sales_tracking->get_date());
        $time = explode(":",$r4_sales_tracking->get_time());
        $unixtime = time();
        if(count($date) == 3)
        {
            if(count($time) != 3)
            {
                $time = array(0,0,1);
            }
            $unixtime = mktime($time[0],$time[1],$time[2],$date[1],$date[2],$date[0]);
        }
        $transaction->set_avatarlink($avatar_id);
        $transaction->set_packagelink(null);
        $transaction->set_streamlink(null);
        $transaction->set_resellerlink(null);
        $transaction->set_regionlink(null);
        $transaction->set_amount($r4_sales_tracking->get_amount());
        $transaction->set_unixtime($unixtime);
        $transaction->set_transaction_uid($uid_transaction["uid"]);
        $transaction->set_renew($r4_sales_tracking->get_salemode());
        $create_status = $transaction->create_entry();
        if($create_status["status"] == true)
        {
            $transactions_created++;
        }
        else
        {
            $view_reply->add_swap_tag_string("page_content","Unable to create transaction because: ".$create_status["message"]);
            $all_ok = false;
            break;
        }
    }
    else
    {
        $view_reply->add_swap_tag_string("page_content","Unable to create transaction because: unable to assign it a uid");
        $all_ok = false;
        break;
    }
}
if($all_ok == true)
{
    $view_reply->add_swap_tag_string("page_content","Created: ".$transactions_created." transactions <br/> <a href=\"[[url_base]]import\">Back to menu</a>");
}
else
{
    $sql->flagError();
}
?>
