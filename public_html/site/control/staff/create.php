<?php
$status = false;
$redirect = "staff";
if($session->get_ownerlevel() == true)
{
    $staff = new staff();
    $avatar = new avatar();

    $input = new inputFilter();
    $avataruid = $input->postFilter("avataruid");
    $username = $input->postFilter("username");
    $email = $input->postFilter("email");
    $bits = explode("@",$email);

        $failed_on = "";
        if(strlen($username) < 5) $failed_on .= $lang["staff.cr.error.1"];
        else if(strlen($username) > 40) $failed_on .= $lang["staff.cr.error.2"];
        else if(strlen($avataruid) != 8) $failed_on .= $lang["staff.cr.error.3"];
        else if($staff->load_by_field("username",$username) == true) $failed_on .= $lang["staff.cr.error.4"];
        else if($avatar->load_by_field("avatar_uid",$avataruid) == false) $failed_on .= $lang["staff.cr.error.5"];
        else if(count($bits) != 2) $failed_on .= $lang["staff.cr.error.8"];
        else if($staff->load_by_field("email",$email) == true) $failed_on .= $lang["staff.cr.error.6"];
	    else if(strlen($email) > 100) $failed_on .= $lang["staff.cr.error.7"];

        if($failed_on == "")
        {
            $staff = new staff();
            $staff->set_username($username);
            $staff->set_avatarlink($avatar->get_id());
            $staff->set_email($email);
            $staff->set_phash(sha1("phash install".microtime()."".$username));
            $staff->set_lhash(sha1("lhash install".microtime()."".$username));
            $staff->set_psalt(sha1("psalt install".microtime()."".$username));
            $staff->set_ownerlevel(false);
            $create_status = $staff->create_entry();
            if($create_status["status"] == true)
            {
                $status = true;
                print $lang["staff.cr.info.1"];
            }
            else
            {
                print sprintf($lang["staff.cr.error.10"],$create_status["message"]);
            }
        }
        else
        {
            print $failed_on;
        }
}
else
{
    $redirect = "";
    print $lang["staff.cr.error.9"];
}
?>
