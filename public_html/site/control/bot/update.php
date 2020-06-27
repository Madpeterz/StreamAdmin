<?php
$status = false;
$redirect = "config";
if($session->get_ownerlevel() == true)
{
    $input = new inputFilter();
    $avataruid = $input->postFilter("avataruid");
    $secret = $input->postFilter("secret");
    $notecards = $input->postFilter("notecards","bool");
    $ims = $input->postFilter("ims","bool");

    $failed_on = "";
    if(strlen($avataruid) != 8) $failed_on .= $lang["bot.up.error.1"];
    else if(strlen($secret) < 8) $failed_on .= $lang["bot.up.error.2"];

    $status = false;
    if($failed_on == "")
    {
        $botconfig = new botconfig();
        if($botconfig->load(1) == true)
        {
            $avatar = new avatar();
            if($avatar->load_by_field("avatar_uid",$avataruid) == true)
            {
                $botconfig->set_field("avatarlink",$avatar->get_id());
                $botconfig->set_field("secret",$secret);
                $botconfig->set_field("notecards",$notecards);
                $botconfig->set_field("ims",$ims);
                $save_changes = $botconfig->save_changes();
                if($save_changes["status"] == true)
                {
                    $status = true;
                    $redirect = "";
                    echo $lang["bot.up.info.1"];
                }
                else
                {
                    echo sprintf($lang["bot.up.error.6"],$save_changes["message"]);
                }
            }
            else
            {
                echo $lang["bot.up.error.5"];
            }
        }
        else
        {
            echo $lang["bot.up.error.4"];
        }
    }
    else
    {
        $redirect = "";
        echo $failed_on;
    }
}
else
{
    echo $lang["bot.up.error.3"];
}
?>
