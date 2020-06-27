<?php
$package = new package();
$server = new server();
$input = new inputFilter();

$port = $input->postFilter("port","integer");
$packagelink = $input->postFilter("packagelink","integer");
$serverlink = $input->postFilter("serverlink","integer");
$mountpoint = $input->postFilter("mountpoint");
$adminusername = $input->postFilter("adminusername");
$adminpassword = $input->postFilter("adminpassword");
$djpassword = $input->postFilter("djpassword");
$needswork = $input->postFilter("needswork","bool");
$failed_on = "";
if($port < 1) $failed_on .= $lang["stream.cr.error.1"];
else if($port > 99999) $failed_on .= $lang["stream.cr.error.2"];
else if($package->load($packagelink) == false) $failed_on .= $lang["stream.cr.error.3"];
else if($server->load($serverlink) == false) $failed_on .= $lang["stream.cr.error.4"];
else if(strlen($adminusername) < 3) $failed_on .= $lang["stream.cr.error.5"];
else if(strlen($adminusername) > 20) $failed_on .= $lang["stream.cr.error.6"];
else if(strlen($adminpassword) < 4) $failed_on .= $lang["stream.cr.error.7"];
else if(strlen($adminpassword) > 20) $failed_on .= $lang["stream.cr.error.8"];
else if(strlen($djpassword) < 4) $failed_on .= $lang["stream.cr.error.9"];
else if(strlen($djpassword) > 20) $failed_on .= $lang["stream.cr.error.10"];
$status = false;
if($failed_on == "")
{
    $stream = new stream();
    $uid = $stream->create_uid("stream_uid",8,10);
    if($uid["status"] == true)
    {
        $where_fields = array(array("port"=>"="),array("serverlink"=>"="));
        $where_values = array(array($port=>"i"),array($serverlink=>"i"));
        $count_check = $sql->basic_count($stream->get_table(),$where_fields,$where_values);
        if($count_check["status"] == true)
        {
            if($count_check["count"] == 0)
            {
                $stream->set_field("stream_uid",$uid["uid"]);
                $stream->set_field("packagelink",$packagelink);
                $stream->set_field("serverlink",$serverlink);
                $stream->set_field("port",$port);
                $stream->set_field("needwork",$needswork);
                $stream->set_field("adminusername",$adminusername);
                $stream->set_field("adminpassword",$adminpassword);
                $stream->set_field("djpassword",$djpassword);
                $stream->set_field("mountpoint",$mountpoint);
                $create_status = $stream->create_entry();
                if($create_status["status"] == true)
                {
                    $status = true;
                    echo $lang["stream.cr.info.1"];
                    $redirect = "stream";
                }
                else
                {
                    echo sprintf($lang["stream.cr.error.14"],$create_status["message"]);
                }
            }
            else
            {
                echo $lang["stream.cr.error.13"];
            }
        }
        else
        {
            echo $lang["stream.cr.error.12"];
        }
    }
    else
    {
        echo $lang["stream.cr.error.11"];
    }
}
else
{
    echo $failed_on;
}
?>