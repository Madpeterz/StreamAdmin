<?php
$input = new inputFilter();
$domain = $input->postFilter("domain");
$controlpanel_url = $input->postFilter("controlpanel_url");
$failed_on = "";
$redirect = "";
if(strlen($domain) > 100) $failed_on .= $lang["server.up.error.1"];
else if(strlen($domain) < 5) $failed_on .= $lang["server.up.error.2"];
else if(strlen($controlpanel_url) < 5) $failed_on .= $lang["server.up.error.3"];
$status = false;
if($failed_on == "")
{
    $server = new server();
    if($server->load($page) == true)
    {
        $where_fields = array(array("domain"=>"="));
        $where_values = array(array($domain =>"s"));
        $count_check = $sql->basic_count($server->get_table(),$where_fields,$where_values);
        $expected_count = 0;
        if($server->get_domain() == $domain)
        {
            $expected_count = 1;
        }
        if($count_check["status"] == true)
        {
            if($count_check["count"] == $expected_count)
            {
                $server->set_field("domain",$domain);
                $server->set_field("controlpanel_url",$controlpanel_url);
                $update_status = $server->save_changes();
                if($update_status["status"] == true)
                {
                    $status = true;
                    $redirect = "server";
                    echo $lang["server.up.info.1"];
                }
                else
                {
                    echo sprintf($lang["server.up.error.7"],$update_status["message"]);
                }
            }
            else
            {
                echo $lang["server.up.error.6"];
            }
        }
        else
        {
            echo $lang["server.up.error.5"];
        }
    }
    else
    {
        echo $lang["server.up.error.4"];
        $redirect = "server";
    }
}
else
{
    echo $failed_on;
}
?>
