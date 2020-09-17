<?php
$input = new inputFilter();
$avatarname = $input->postFilter("avatarname");
$avataruuid = $input->postFilter("avataruuid");
$failed_on = "";
if(count(explode(" ",$avatarname)) == 1) $avatarname .= " Resident";
if(strlen($avatarname) < 5) $failed_on .= $lang["av.ud.error.1"];
else if(strlen($avatarname) > 125) $failed_on .= $lang["av.ud.error.2"];
else if(strlen($avataruuid) != 36) $failed_on .= $lang["av.ud.error.3"];
$redirect = "avatar";
$status = false;
if($failed_on == "")
{
    $avatar = new avatar();
    if($avatar->load_by_field("avatar_uid",$page) == true)
    {
        $where_fields = array(array("avataruuid"=>"="));
        $where_values = array(array($avataruuid=>"s"));
        $count_check = $sql->basic_count($avatar->get_table(),$where_fields,$where_values);
        $expected_count = 0;
        if($avatar->get_avataruuid() == $avataruuid)
        {
            $expected_count = 1;
        }
        if($count_check["status"] == true)
        {
            if($count_check["count"] == $expected_count)
            {
                $avatar->set_avatarname($avatarname);
                $avatar->set_avataruuid($avataruuid);
                $update_status = $avatar->save_changes();
                if($update_status["status"] == true)
                {
                    $status = true;
                    echo $lang["av.ud.info.1"];
                }
                else
                {
                    echo sprintf($lang["av.ud.error.7"],$update_status["message"]);
                }
            }
            else
            {
                echo $lang["av.ud.error.6"];
            }
        }
        else
        {
            echo $lang["av.ud.error.5"];
        }
    }
    else
    {
        echo $lang["av.ud.error.4"];
    }
}
else
{
    $redirect = null;
    $status = false;
    echo $failed_on;
}
?>
