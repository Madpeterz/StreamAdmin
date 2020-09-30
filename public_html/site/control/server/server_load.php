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
                echo "CPU: <span class=\"text-light\">".$apireply["loads"]["5"]."</span>";
                $addon = " | ";
            }
            if($apireply["ram"]["max"] > 0)
            {
                echo $addon;
                $pcent = $apireply["ram"]["max"] / 100;
                $dif = $apireply["ram"]["max"] - $apireply["ram"]["free"];
                $pcents = 0;
                while($dif > $pcent)
                {
                    $pcents++;
                    $dif -= $pcent;
                }
                $usage = $apireply["ram"]["max"] - $apireply["ram"]["free"];
                $mbmax = ($apireply["ram"]["max"] / 1000)/1000;
                $mbusage = ($usage / 1000)/1000;
                $max = round($mbmax,2);
                $used = round($mbusage,2);

                $text_color = "text-light";
                if($pcents > 80)
                {
                    $text_color = "text-danger";
                }
                else if($pcents > 60)
                {
                    $text_color = "text-warning";
                }
                else if($pcents > 40)
                {
                    $text_color = "text-info";
                }
                echo "Ram: <span class=\"".$text_color."\">".$used."/".$max." [".$pcents." %]</span>";
                $addon = " | ";
            }
            if($apireply["streams"]["total"] > 0)
            {
                echo $addon;
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
                echo "Str: <span class=\"".$text_color."\">".$percent." %</span>";
            }
        }
    }
}
else
{
    echo "Unable to find server";
}
?>
