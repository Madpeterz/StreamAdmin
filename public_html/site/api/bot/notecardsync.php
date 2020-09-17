<?php
$status = false;
$why_failed = "";
$all_ok = true;
$changes = 0;
if($owner_override == true)
{
    $botconfig = new botconfig();
    if($botconfig->load(1) == true)
    {
        $botavatar = new avatar();
        if($botavatar->load($botconfig->get_avatarlink()) == true)
        {
            if($botconfig->get_notecards() == true)
            {
                $notecard = new notecard();
                $where_fields = array(array("id"=>">"));
                $where_values = array(array(0 => "i"));
                $count_data = $sql->basic_count($notecard->get_table(),$where_fields,$where_values);
                if($count_data["status"] == true)
                {
                    $status = true;
                    if($count_data["count"] > 0)
                    {
                        $reply["hassyncmessage"] = 1;
                        $reply["avataruuid"] = $botavatar->get_avataruuid();
                        $bot_helper = new bot_helper();
                        print $bot_helper->send_bot_command($botconfig,"fetchnextnotecard",[$template_parts["url_base"],$slconfig->get_http_inbound_secret()]);
                    }
                    else
                    {
                        $reply["hassyncmessage"] = 0;
                        print "No work";
                    }
                }
                else
                {
                    $status = false;
                    print $lang["bot.ncs.error.5"];
                }
            }
            else
            {
                $status = true;
                $reply["hassyncmessage"] = 2;
                print $lang["bot.ncs.error.4"];
            }
        }
        else
        {
            print $lang["bot.ncs.error.3"];
        }
    }
    else
    {
        print $lang["bot.ncs.error.2"];
    }
}
else
{
    print $lang["bot.ncs.error.1"];
}
?>
