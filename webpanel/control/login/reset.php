<?php
sleep(1);
$input = new inputFilter();
$slusername = $input->postFilter("slusername");
$bits = explode("@",$slusername);
$contact_via = "sl";
$staff = new staff();
if(count($bits) == 2)
{
    $contact_via = "email";
    $staff->load_by_field("email",$slusername);
}
else
{
    $username_bits = explode(" ",$slusername);
    if(count($username_bits) == 1)
    {
        $username_bits[] = "Resident";
    }
    $slusername = implode(" ",$username_bits);
    $avatar = new avatar();
    $status = false;
    if($avatar->load_by_field("avatarname",$slusername) == true)
    {
        $staff->load_by_field("avatarlink",$avatar->get_id());
    }
}
if($staff->get_id() > 0)
{
    $uid = $staff->create_uid("email_reset_code",8,10);
    if($uid["status"] == true)
    {
        $reset_url = $template_parts["url_base"]."login/resetwithtoken/".$uid["uid"];
        $staff->set_email_reset_code($uid["uid"]);
        $staff->set_email_reset_expires((time()+$unixtime_hour));

        $update_status = $staff->save_changes();
        if($update_status["status"] == true)
        {
            if($contact_via == "email")
            {
                $email_helper = new email_helper();
                $status_reply = $email_helper->send_email($staff->get_email(),$lang["login.rs.email.title"],sprintf($lang["login.rs.email.message"],$uid["uid"],$reset_url));
                if($status_reply["status"] == true)
                {
                    $status = true;
                    $ajax_reply->set_swap_tag_string("message",$lang["login.rs.info.2"]);
                    $ajax_reply->set_swap_tag_string("redirect","here");
                }
            }
            else
            {
                $message = new message();
                $message->set_avatarlink($avatar->get_id());
                $message->set_message(sprintf($lang["login.rs.sl.message"],$reset_url));
                $add_status = $message->create_entry();
                if($add_status["status"] == true)
                {
                    $status = true;
                    $ajax_reply->set_swap_tag_string("message",$lang["login.rs.info.1"]);
                    $ajax_reply->set_swap_tag_string("redirect","here");
                }
            }
        }
    }
}
if($status == false)
{
    $ajax_reply->set_swap_tag_string("message",$lang["login.rs.error.1"]);
}
?>
