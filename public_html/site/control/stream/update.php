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
$original_adminusername = $input->postFilter("original_adminusername");
$failed_on = "";
if($port < 1) $failed_on .= $lang["stream.up.error.1"];
else if($port > 99999) $failed_on .= $lang["stream.up.error.2"];
else if($package->load($packagelink) == false) $failed_on .= $lang["stream.up.error.3"];
else if($server->load($serverlink) == false) $failed_on .= $lang["stream.up.error.4"];
else if(strlen($adminusername) < 3) $failed_on .= $lang["stream.up.error.5"];
else if(strlen($adminusername) > 20) $failed_on .= $lang["stream.up.error.6"];
else if(strlen($adminpassword) < 4) $failed_on .= $lang["stream.up.error.7"];
else if(strlen($adminpassword) > 20) $failed_on .= $lang["stream.up.error.8"];
else if(strlen($djpassword) < 4) $failed_on .= $lang["stream.up.error.9"];
else if(strlen($djpassword) > 20) $failed_on .= $lang["stream.up.error.10"];
else if(strlen($original_adminusername) < 3) $failed_on .= $lang["stream.up.error.5"];
else if(strlen($original_adminusername) > 20) $failed_on .= $lang["stream.up.error.6"];

$status = false;
if($failed_on == "")
{
    $stream = new stream();
    if($stream->load_by_field("stream_uid",$page) == true)
    {
        $where_fields = array(array("port"=>"="),array("serverlink"=>"="));
        $where_values = array(array($port=>"i"),array($serverlink=>"i"));
        $count_check = $sql->basic_count($stream->get_table(),$where_fields,$where_values);
        $expected_count = 0;
        if($stream->get_port() == $port)
        {
            if($stream->get_serverlink() == $serverlink)
            {
                $expected_count = 1;
            }
        }
        if($count_check["status"] == true)
        {
            if($count_check["count"] == $expected_count)
            {
                $stream->set_packagelink($packagelink);
                $stream->set_serverlink($serverlink);
                $stream->set_port($port);
                $stream->set_needwork(0);
                $stream->set_adminusername($adminusername);
                $stream->set_adminpassword($adminpassword);
                $stream->set_djpassword($djpassword);
                $stream->set_mountpoint($mountpoint);
                if($original_adminusername == "sync")
                {
                    $stream->set_original_adminusername($adminusername);
                }
                else
                {
                    $stream->set_original_adminusername($original_adminusername);
                }

                $update_status = $stream->save_changes();
                if($update_status["status"] == true)
                {
                    $status = true;
                    echo $lang["stream.up.info.1"];
                    $redirect = "stream";
                }
                else
                {
                    echo sprintf($lang["stream.up.error.14"],$update_status["message"]);
                }
            }
            else
            {
                echo $lang["stream.up.error.13"];
            }
        }
        else
        {
            echo $lang["stream.up.error.12"];
        }
    }
    else
    {
        echo $lang["stream.up.error.11"];
    }
}
else
{
    echo $failed_on;
}
?>
