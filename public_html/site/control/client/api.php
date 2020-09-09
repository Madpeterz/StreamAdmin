<?php
$status = false;
$rental = new rental();
if($rental->load_by_field("rental_uid",$page) == true)
{
    $stream = new stream();
    if($stream->load($rental->get_streamlink()) == true)
    {
        $package = new package();
        if($package->load($stream->get_packagelink()) == true)
        {
            $server = new server();
            if($server->load($stream->get_serverlink()) == true)
            {
                $api = new apis();
                if($api->load($server->get_apilink()) == true)
                {
                    $server_api_name = "server_".$api->get_name()."";
                    $server_api = new $server_api_name();
                    if(($optional == "start") || ($optional == "stop"))
                    {
                        $status_flag = false;
                        if($optional == "start") $status_flag = true;
                        if(($api->get_opt_toggle_status() == 1) && ($server->get_opt_toggle_status() == 1))
                        {
                            $status = $server_api->opt_toggle_status($stream,$server,$status_flag);
                            if($status == true) echo $lang["client.api.passed"]
                            else $lang["client.api.failed"];
                        }
                        else
                        {
                            echo $lang["client.api.error.9"];
                        }
                    }
                    else if($optional == "autodj_next")
                    {
                        if(($api->get_opt_autodj_next() == 1) && ($server->get_opt_autodj_next() == 1))
                        {
                            if($package->get_autodj() == true)
                            {
                                $status = $server_api->opt_autodj_next($stream,$server);
                                if($status == true) echo $lang["client.api.passed"]
                                else $lang["client.api.failed"];
                            }
                        }
                        else
                        {
                            echo $lang["client.api.error.8"];
                        }
                    }
                    else if($optional == "autodj_toggle")
                    {
                        if(($api->get_opt_toggle_autodj() == 1) && ($server->get_opt_toggle_autodj() == 1))
                        {
                            if($package->get_autodj() == true)
                            {
                                $status = $server_api->opt_toggle_autodj($stream,$server);
                                if($status == true) echo $lang["client.api.passed"]
                                else $lang["client.api.failed"];
                            }
                        }
                        else
                        {
                            echo $lang["client.api.error.7"];
                        }
                    }
                    else
                    {
                        echo $lang["client.api.error.6"];
                    }
                }
                else
                {
                    echo $lang["client.api.error.5"];
                }
            }
            else
            {
                echo $lang["client.api.error.4"];
            }
        }
        else
        {
            echo $lang["client.api.error.3"];
        }
    }
    else
    {
        echo $lang["client.api.error.2"];
    }
}
else
{
    echo $lang["client.api.error.1"];
}
?>
