<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$redirect ="server";
$status = false;
if($accept == "Accept")
{
    $server = new server();
    if($server->load($page) == true)
    {
        $stream_set = new stream_set();
        $load_status = $stream_set->load_on_field("serverlink",$server->get_id());
        if($load_status["status"] == true)
        {
            if($stream_set->get_count() == 0)
            {
                $remove_status = $server->remove_me();
                if($remove_status["status"] == true)
                {
                    $status = true;
                    echo $lang["server.rm.info.1"];
                }
                else
                {
                    echo sprintf($lang["server.rm.error.3"],$remove_status["message"]);
                }
            }
            else
            {
                echo sprintf($lang["server.rm.error.5"],$stream_set->get_count());
            }
        }
        else
        {
            echo $lang["server.rm.error.4"];
        }
    }
    else
    {
        echo $lang["server.rm.error.2"];
    }
}
else
{
    echo $lang["server.rm.error.1"];
    $redirect ="server/manage/".$page."";
}
?>
