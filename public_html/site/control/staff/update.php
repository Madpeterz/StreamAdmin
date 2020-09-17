<?php
$status = false;
$redirect = "staff";
if($session->get_ownerlevel() == true)
{
    $staff = new staff();
    $avatar = new avatar();

    $input = new inputFilter();
    $username = $input->postFilter("username");
    $email = $input->postFilter("email");
    $failed_on = "";
    $bits = explode("@",$email);
    if(strlen($username) < 5) $failed_on .= $lang["staff.up.error.1"];
    else if(strlen($username) > 40) $failed_on .= $lang["staff.up.error.2"];
    else if(count($bits) != 2) $failed_on .= $lang["staff.up.error.3"];
    else if($staff->load_by_field("username",$username) == true) $failed_on = $lang["staff.up.error.4"];
    else if(strlen($email) > 100) $failed_on .= $lang["staff.up.error.5"];
    if($failed_on == "")
    {
        $staff = new staff();
        if($staff->load($page) == true)
        {
            $where_fields = array(array("avataruuid"=>"="));
            $where_values = array(array($avataruuid=>"s"));
            $count_check = $sql->basic_count($avatar->get_table(),$where_fields,$where_values);
            $expected_count = 0;
            if($staff->get_email() == $email)
            {
                $expected_count = 1;
            }
            if($count_check["status"] == true)
            {
                if($count_check["count"] == $expected_count)
                {
                    $staff->set_username($username);
                    $staff->set_email($email);
                    $staff->set_phash(sha1("phash install".microtime()."".$username));
                    $staff->set_lhash(sha1("lhash install".microtime()."".$username));
                    $staff->set_psalt(sha1("psalt install".microtime()."".$username));
                    $update_status = $staff->save_changes();
                    if($update_status["status"] == true)
                    {
                        $status = true;
                        print "staff member updated";
                    }
                    else
                    {
                        print sprintf($lang["staff.cr.error.10"],$update_status["message"]);
                    }
                }
            }
        }
        else
        {
            print $lang["staff.up.error.7"];
        }
    }
    else
    {
        print $failed_on;
    }
}
else
{
    print $lang["staff.up.error.6"];
}
?>
