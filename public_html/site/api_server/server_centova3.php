<?php
class server_centova3_only extends server_public_api
{
    protected function process_centova_api_call(server $server,array $post_data,array $args) : array
    {
        $post_data["f"] = "json";
        $post_data["a[password]"] = "".$server->get_api_username()."|".$server->get_api_password()."";
        foreach($args as $key => $value)
        {
            $post_data["a[".$key."]"] = $value;
        }
        $reply = $this->curl_request($server->get_api_url(),$post_data);
        if($reply["status"] == true)
        {
            $this->last_api_message = "curl ok";
            return array("status"=>true,"data"=>json_decode($reply["message"], true));
        }
        else
        {
            $this->last_api_message = "curl failed with message: ".$reply["message"]."";
            return array("status"=>false,"data"=>array());
        }
    }
    protected function centova_serverclass_api_call(server $server,stream $stream,string $method,array $args=array(),$post_data=array()) : array
    {
        $post_data["xm"]="server.".$method."";
        $post_data["a[username]"]=$stream->get_adminusername();
        return $this->process_centova_api_call($server,$post_data,$args);
    }
    protected function centova_systemclass_api_call(server $server,string $method,array $args=array()) : array
    {
        return $this->process_centova_api_call($server,array("xm"=>"system.".$method.""),$args);
    }
    protected function simple_reply_ok(array $reply,bool $debug=false) : bool
    {
        if($debug == true)
        {
            print_r($reply);
        }
        if(array_key_exists("status",$reply) == true)
        {
            if($reply["status"] == true)
            {
                $this->last_api_message = "Curl ok but badly formated reply";
                if(array_key_exists("data",$reply) == true)
                {
                    if(is_array($reply["data"]) == true)
                    {
                        $this->last_api_message = "Curl connected ok but invaild response from server";
                        if(array_key_exists("response",$reply["data"]) == true)
                        {
                            if(array_key_exists("message",$reply["data"]["response"]) == true)
                            {
                                $this->last_api_message = "Reply from server: ".$reply["data"]["response"]["message"]."";
                            }
                        }
                        if(array_key_exists("type",$reply["data"]) == true)
                        {
                            if($reply["data"]["type"] == "success")
                            {
                                return true;
                            }
                        }

                    }
                }
            }
        }
        return false;
    }
}
class server_centova3 extends server_centova3_only
{
    protected function terminate_account(stream $stream,server $server,string $old_username)  : bool
    {
        return $this->simple_reply_ok($this->centova_systemclass_api_call($server,"terminate",array("username"=>$old_username)));
    }
    protected function create_account(stream $stream,server $server,package $package) : bool
    {
        if($package->get_api_template() != null)
        {
            $servertype = new servertypes();
            if($servertype->load($package->get_servertypelink()) == true)
            {
                $post_data = array(
                    "port" => $stream->get_port(),
                    "maxclients" => $package->get_listeners(),
                    "adminpassword" => $stream->get_adminpassword(),
                    "sourcepassword" => $stream->get_djpassword(),
                    "maxbitrate" => $package->get_bitrate(),
                    "username" => $stream->get_adminusername(),
                    "email" => "port".$stream->get_port()."@noemail.com",
                    "usesource" => 2,
                    "autostart" => 1,
                    "template" => $package->get_api_template(),
                );
                /*
                if($servertype->get_id() == 1)
                {
                    $post_data["servertype"] = "ShoutCast";
                }
                else if($servertype->get_id() == 2)
                {
                    $post_data["servertype"] = "ShoutCast2";
                }
                else if($servertype->get_id() == 3)
                {
                    $post_data["servertype"] = "IceCast";
                }
                */
                if($package->get_autodj() == true)
                {
                    $post_data["autostart"] = 0;
                    $post_data["usesource"] = 1;
                    $post_data["diskquota"] = $package->get_autodj_size()*1000;
                }
                $reply = $this->centova_systemclass_api_call($server,"provision",$post_data);
                if($this->simple_reply_ok($reply) == true)
                {
                    return $this->susspend_server($stream,$server);
                }
            }
            else
            {
                $this->last_api_message = "Unable to find servertype linked to package";
            }
        }
        else
        {
            $this->last_api_message = "Package does not have a template!";
        }
        return false;
    }
    protected function remove_dj(stream $stream,server $server,string $djaccount) : bool
    {
        $reply = $this->centova_serverclass_api_call($server,$stream,"managedj",array("action"=>"terminate","djusername"=>$djaccount));
        return $this->simple_reply_ok($reply);
    }
    protected function dj_list(stream $stream,server $server) : array
    {
        $reply = $this->centova_serverclass_api_call($server,$stream,"managedj",array("action"=>"list"));
        $status = false;
        $list = array();
        if($this->simple_reply_ok($reply) == true)
        {
            $status = true;
            $djlist_data = $reply["data"]["response"]["data"];
            if(is_array($djlist_data) == true)
            {
                foreach($djlist_data as $djentry)
                {
                    $list[] = $djentry["djusername"];
                }
            }
        }
        else
        {
            // Handle broken API [v3.2.12]
            if(array_key_exists("data",$reply) == true)
            {
                $reply = $reply["data"];
                if(array_key_exists("response",$reply) == true)
                {
                    $reply = $reply["response"];
                    if(array_key_exists("message",$reply) == true)
                    {
                        $reply = $reply["message"];
                        if (strpos($reply, "Invalid argument supplied for foreach()") !== false)
                        {
                            $status = true;
                            $this->last_api_message = "No DJ accounts";
                        }
                    }
                }
            }
        }
        return array("status"=>$status,"list"=>$list);
    }
    protected function server_status(server $server) : array
    {
        $status = false;
        $loads = array("1"=>0,"5"=>0,"15"=>0);
        $ram = array("free"=>0,"max"=>0);
        $streams = array("total"=>0,"active"=>0);
        $message = "Unable to fetch status";
        $reply = $this->centova_systemclass_api_call($server,"version");
        if($this->simple_reply_ok($reply) == true)
        {
            $server_status = $reply["data"]["response"]["data"]["web"];
            $status = true;
            $loads = array("1"=>$server_status["other"]["Load (1m)"][1],"5"=>$server_status["other"]["Load (5m)"][1],"15"=>$server_status["other"]["Load (15m)"][1]);
            $streams = array("total"=>$server_status["accounts"],"active"=>$server_status["activeaccounts"]);
            $ram = array("free"=>floor($server_status["memfree"]/1000),"max"=>floor($server_status["memtotal"]/1000));
            $message = "loaded";
        }
        return array("status"=>$status,"loads"=>$loads,"ram"=>$ram,"streams"=>$streams,"message"=>$message);
    }
    protected function account_data(stream $stream,server $server)
    {
        $reply = $this->centova_serverclass_api_call($server,$stream,"getaccount");
        $this->simple_reply_ok($reply);
        if($this->simple_reply_ok($reply) == true)
        {
            return array("status"=>true,"data"=>$reply["data"]["response"]["data"]["account"]);
        }
        return array("status"=>false,"data"=>array());
    }
    protected function account_state(stream $stream,server $server) : array
    {
        $reply = $this->account_data($stream,$server);
        $status = $reply["status"];
        $state = false;
        if($status == true)
        {
            if($reply["data"]["status"] != "disabled")
            {
                $state = true;
            }
        }
        return array("status"=>$status,"state"=>$state);
    }
    protected function stream_state(stream $stream,server $server) : array
    {
        $reply = $this->centova_serverclass_api_call($server,$stream,"getstatus");
        if($this->simple_reply_ok($reply) == true)
        {
            $server_status = $reply["data"]["response"]["data"]["status"];
            return array("status"=>true,"state"=>$server_status["serverstate"],"source"=>$server_status["sourcestate"]);
        }
        return array("status"=>false,"state"=>false,"source"=>false);
    }
    protected function account_name_list(server $server) : array
    {
        $reply = $this->centova_systemclass_api_call($server,"listaccounts",array("start"=>0,"limit"=>1000));
        if($this->simple_reply_ok($reply) == true)
        {
            $current_usernames = array();
            $server_accounts = $reply["data"]["response"]["data"];
            foreach($server_accounts as $entry)
            {
                $current_usernames[] = $entry["username"];
            }
            return array("status"=>true,"usernames"=>$current_usernames);
        }
        return array("status"=>false,"usernames"=>array());
    }

    protected function sync_username(stream $stream,server $server,string $old_username) : bool
    {
        $reply = $this->centova_systemclass_api_call($server,"rename",array("username"=>$old_username,"newusername"=>$stream->get_adminusername()));
        if($this->simple_reply_ok($reply) == true)
        {
            return true;
        }
        return false;
    }
    protected function toggle_autodj(stream $stream,server $server) : bool
    {
        $reply = $this->centova_serverclass_api_call($server,$stream,"getstatus",array("mountpoints"=>"all"));
        if($this->simple_reply_ok($reply) == true)
        {
            $server_status = $reply["data"]["response"]["data"]["status"];
            if($server_status["serverstate"] == 1)
            {
                // server up
                if($server_status["sourcestate"] == 0)
                {
                    // Nothing connected start autoDJ
                    return $this->simple_reply_ok($this->centova_serverclass_api_call($server,$stream,"switchsource",array("state"=>"up")));
                }
                else
                {
                    // somthing connected
                    $autodj_source_types = array("liquidsoap","icescc");
                    if(in_array($server_status["sourcetype"],$autodj_source_types) == true)
                    {
                        // autoDJ connected stop it
                        return $this->simple_reply_ok($this->centova_serverclass_api_call($server,$stream,"switchsource",array("state"=>"down")));
                    }
                    else
                    {
                        $this->last_api_message = "DJ connected unable to start autoDJ";
                        return true;
                    }
                }
            }
            else
            {
                // server down
                return $this->start_server($stream,$server);
            }
        }
        return false;
    }
    protected function autodj_next(stream $stream,server $server) : bool
    {
        return $this->simple_reply_ok($this->centova_serverclass_api_call($server,$stream,"nextsong"));
    }
    protected function stop_server(stream $stream,server $server) : bool
    {
        $streamstate = $this->stream_state($stream,$server);
        if(($streamstate["status"] == true) && ($streamstate["state"] == true))
        {
            return $this->simple_reply_ok($this->centova_serverclass_api_call($server,$stream,"stop"));
        }
        else
        {
            $this->last_api_message = "Skipped server is already stopped";
            return true;
        }
    }
    protected function start_server(stream $stream,server $server,int $skip_auto_dj=0) : bool
    {
        $streamstate = $this->stream_state($stream,$server);
        if(($streamstate["status"] == true) && ($streamstate["state"] == false))
        {
            return $this->simple_reply_ok($this->centova_serverclass_api_call($server,$stream,"start",array("noapps"=>$skip_auto_dj)));
        }
        else
        {
            $this->last_api_message = "Skipped server is already up";
            return true;
        }
    }
    protected function susspend_server(stream $stream,server $server) : bool
    {
        return $this->simple_reply_ok($this->centova_systemclass_api_call($server,"setstatus",array("username"=>$stream->get_adminusername(),"status"=>"disabled")));
    }
    protected function un_susspend_server(stream $stream,server $server) : bool
    {
        return $this->simple_reply_ok($this->centova_systemclass_api_call($server,"setstatus",array("username"=>$stream->get_adminusername(),"status"=>"enabled")));
    }
    protected function change_password(stream $stream,server $server) : bool
    {
        return $this->simple_reply_ok($this->centova_serverclass_api_call($server,$stream,"reconfigure",array("adminpassword"=>$stream->get_adminpassword(),"sourcepassword"=>$stream->get_djpassword())));
    }
    protected function change_title_now(stream $stream,server $server,string $newtitle="Not set") : bool
    {
        return $this->simple_reply_ok($this->centova_serverclass_api_call($server,$stream,"reconfigure",array("title"=>$title)));
    }
}
?>
