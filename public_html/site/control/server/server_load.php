<?php
$status = true;
$server = new server();
if($server->load($page) == true)
{
    if($server->get_api_serverstatus() == 1)
    {
        $serverapi_helper = new serverapi_helper();
        $serverapi_helper->force_set_server($server);
        $apireply = $serverapi_helper->api_serverstatus();
        //print_r($apireply);
        if($apireply["status"] == true)
        {
            $addon = "";
            if($apireply["loads"]["1"] > 0.0)
            {
                print "CPU: <span class=\"text-light\">".$apireply["loads"]["5"]."</span>";
                $addon = " | ";
            }
            if($apireply["ram"]["max"] > 0)
            {
                print $addon;
                $percent = round((($apireply["ram"]["max"]-$apireply["ram"]["free"])/$apireply["ram"]["max"])*100,2);
                $kb = $apireply["ram"]["free"];
                $mb = floor($kb / 1000);
                $kb -= $mb * 1000;
                $gb = floor($mb / 1000);
                $mb -= $gb * 100;
                $freeram = "/";
                if($gb > 0)
                {
                    $freeram = "".$gb.".".round($mb/1000)." gb";
                }
                else if($mb > 0)
                {
                    $freeram = "".$mb.".".round($kb/1000)." mb";
                }
                else if($kb > 0)
                {
                    $freeram = "".round($kb/1000)." mb";
                }
                $text_color = "text-light";
                if($percent > 80)
                {
                    $text_color = "text-danger";
                }
                else if($percent > 60)
                {
                    $text_color = "text-warning";
                }
                else if($percent > 40)
                {
                    $text_color = "text-info";
                }
                print "Ram: <span class=\"".$text_color."\">".$percent." %</span>";
                $addon = " | ";
            }
            if($apireply["streams"]["total"] > 0)
            {
                print $addon;
                $percent = 100 - round((($apireply["streams"]["total"]-$apireply["streams"]["active"])/$apireply["streams"]["total"])*100,2);
                $text_color = "text-light";
                if($percent < 40)
                {
                    $text_color = "text-danger";
                }
                else if($percent < 60)
                {
                    $text_color = "text-warning";
                }
                else if($percent < 80)
                {
                    $text_color = "text-info";
                }
                print "Str: <span class=\"".$text_color."\">".$percent." %</span>";
            }
        }
    }
}
else
{
    print "Unable to find server";
}
?>
