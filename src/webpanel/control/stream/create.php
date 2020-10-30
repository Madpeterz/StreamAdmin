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
$api_uid_1 = $input->postFilter("api_uid_1");
$api_uid_2 = $input->postFilter("api_uid_2");
$api_uid_3 = $input->postFilter("api_uid_3");
$api_create = $input->postFilter("api_create","integer");

$failed_on = "";
if($port < 1) $failed_on .= $lang["stream.cr.error.1"];
else if($port > 99999) $failed_on .= $lang["stream.cr.error.2"];
else if($package->load($packagelink) == false) $failed_on .= $lang["stream.cr.error.3"];
else if($server->load($serverlink) == false) $failed_on .= $lang["stream.cr.error.4"];
else if(strlen($adminusername) < 3) $failed_on .= $lang["stream.cr.error.5"];
else if(strlen($adminusername) >= 50) $failed_on .= $lang["stream.cr.error.6"];
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
        $where_fields = array(array("port"=>">="),array("serverlink"=>"="));
        $where_values = array(array($port=>"i"),array($serverlink=>"i"));
        $count_check = $sql->basic_count($stream->get_table(),$where_fields,$where_values);
        if($count_check["status"] == true)
        {
            if($count_check["count"] == 0)
            {
                $stream->set_stream_uid($uid["uid"]);
                $stream->set_packagelink($packagelink);
                $stream->set_serverlink($serverlink);
                $stream->set_port($port);
                $stream->set_needwork($needswork);
                $stream->set_adminusername($adminusername);
                $stream->set_adminpassword($adminpassword);
                $stream->set_original_adminusername($adminusername);
                $stream->set_djpassword($djpassword);
                $stream->set_mountpoint($mountpoint);
                $stream->set_api_uid_1($api_uid_1);
                $stream->set_api_uid_2($api_uid_2);
                $stream->set_api_uid_3($api_uid_3);
                $create_status = $stream->create_entry();
                if($create_status["status"] == true)
                {
                    $status = true;
                    if($api_create == 1)
                    {
                        include "shared/media_server_apis/logic/create.php";
                        $all_ok = $api_serverlogic_reply;
                    }
                    if($status != true)
                    {
                        $ajax_reply->set_swap_tag_string("message",$why_failed);
                    }
                    else
                    {
                        $ajax_reply->set_swap_tag_string("message",$lang["stream.cr.info.1"]);
                        $ajax_reply->set_swap_tag_string("redirect","stream");
                    }
                }
                else
                {
                    $ajax_reply->set_swap_tag_string("message",sprintf($lang["stream.cr.error.14"],$create_status["message"]));
                }
            }
            else
            {
                $ajax_reply->set_swap_tag_string("message",$lang["stream.cr.error.13"]);
            }
        }
        else
        {
            $ajax_reply->set_swap_tag_string("message",$lang["stream.cr.error.12"]);
        }
    }
    else
    {
        $ajax_reply->set_swap_tag_string("message",$lang["stream.cr.error.11"]);
    }
}
else
{
    $ajax_reply->set_swap_tag_string("message",$failed_on);
}
?>
