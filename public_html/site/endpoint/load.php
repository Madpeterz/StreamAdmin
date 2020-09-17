<?php
include("site/framework/loader_light.php");
$input = new inputFilter();
$senttoken = $input->postFilter("token");
$timewindow = 30;
$timeloop = 0;
$vaild = false;
$now = time();
$reply = array();
while(($timeloop < $timewindow) && ($vaild == false))
{
    $servertokencheckA = sha1("".($now+$timeloop)."".$module."".$area."".$slconfig->get_http_inbound_secret()."");
    $servertokencheckB = sha1("".($now-$timeloop)."".$module."".$area."".$slconfig->get_http_inbound_secret()."");
    if(($servertokencheckA == $senttoken) || ($servertokencheckB == $senttoken))
    {
        $vaild = true;
        break;
    }
    $timeloop++;
}
if($vaild == true)
{
    $testfile = "site/endpoint/".$module."/".$area.".php";
    if(file_exists($testfile) == true)
    {
        $reply = array("status"=>false,"message"=>"Failed: endpoint not processed");
        include($testfile);
    }
    else
    {
        $reply = array("status"=>false,"message"=>"Failed: endpoint does not support that request");
    }
}
else
{
    $reply = array("status"=>false,"message"=>"Failed: Invaild token");
}
echo json_encode($reply);
?>
