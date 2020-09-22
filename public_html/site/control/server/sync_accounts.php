<?php
$status = false;
$server = new server();
if($server->load($page) == true)
{
    $api = new apis();
    if($api->load($server->get_apilink()) == true)
    {
        if(($server->get_api_sync_accounts() == true) && ($api->get_api_sync_accounts() == true))
        {
            $serverapi_helper = new serverapi_helper();
            if($serverapi_helper->force_set_server($server) == true)
            {
                $oneday_ago = time() - ((60*60)*24);
                $where_config = array(
                    "fields"=>array("serverlink","last_api_sync"),
                    "matches"=>array("=","<="),
                    "values"=>array($server->get_id(),$oneday_ago),
                    "types"=>array("i","i"),
                );
                $limits = array(
                             "page_number" => 0,
                             "max_entrys" => 10
                );
                $stream_set = new stream_set();
                $stream_set->load_with_config($where_config,null,$limits);
                if($stream_set->get_count() > 0)
                {
                    $accounts_found = $serverapi_helper->get_all_accounts(true,$stream_set);
                    if($accounts_found["status"] == true)
                    {
                        $accounts_updated = 0;
                        $accounts_insync = 0;
                        $accounts_missing_global = 0;
                        $accounts_missing_passwords = 0;
                        $all_ok = true;
                        foreach($stream_set->get_all_ids() as $streamid)
                        {
                            $stream = $stream_set->get_object_by_id($streamid);
                            if(in_array($stream->get_adminusername(),$accounts_found["usernames"]) == true)
                            {
                                if(array_key_exists($stream->get_adminusername(),$accounts_found["passwords"]) == true)
                                {
                                    $has_update = false;
                                    if($stream->get_adminpassword() != $accounts_found["passwords"][$stream->get_adminusername()]["admin"])
                                    {
                                        $has_update = true;
                                        $stream->set_adminpassword($accounts_found["passwords"][$stream->get_adminusername()]["admin"]);
                                    }
                                    if($stream->get_djpassword() != $accounts_found["passwords"][$stream->get_adminusername()]["dj"])
                                    {
                                        $has_update = true;
                                        $stream->set_djpassword($accounts_found["passwords"][$stream->get_adminusername()]["dj"]);
                                    }
                                    $stream->set_last_api_sync(time());
                                    if($has_update == true)
                                    {
                                        $update_status = $stream->save_changes();
                                        if($update_status["status"] == true)
                                        {
                                            $accounts_updated++;
                                        }
                                        else
                                        {
                                            $all_ok = false;
                                            echo "failed to sync password to db";
                                            break;
                                        }
                                    }
                                    else
                                    {
                                        $update_status = $stream->save_changes();
                                        if($update_status["status"] == true)
                                        {
                                            $accounts_insync++;
                                        }
                                        else
                                        {
                                            $all_ok = false;
                                            echo "Failed to mark stream as in sync";
                                            break;
                                        }
                                    }
                                }
                                else
                                {
                                    $accounts_missing_passwords++;
                                }
                            }
                            else
                            {
                                $accounts_missing_global++;
                            }
                        }
                        if($all_ok == true)
                        {
                            $server->set_last_api_sync(time());
                            $update_status = $server->save_changes();
                            if($update_status["status"] == true)
                            {
                                $status = true;
                                echo "Updated: ".$accounts_updated." / Ok: ".$accounts_insync."";
                                if($accounts_missing_passwords > 0)
                                {
                                    echo " / Missing PW dataset: ".$accounts_missing_passwords;
                                }
                                if($accounts_missing_global > 0)
                                {
                                    echo " / Account missing: ".$accounts_missing_global;
                                }
                            }
                            else
                            {
                                echo "Unable to update server last sync time";
                            }
                        }
                    }
                    else
                    {
                        echo $server_api_helper->get_message();
                    }
                }
                else
                {
                    echo "Unable to find any streams attached to server or all streamed sync'd in the last 24 hours";
                }
            }
            else
            {
                echo "Unable to attach server to api helper";
            }
        }
        else
        {
            echo "Server or API have sync accounts disabled";
        }
    }
    else
    {
        echo "Unable to find api used by server";
    }
}
else
{
    echo "Unable to find server";
}
?>
