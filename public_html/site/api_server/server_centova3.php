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
    protected function simple_reply_ok(array $reply) : bool
    {
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
    protected function stream_state(stream $stream,server $server) : array
    {
        $reply = $this->centova_serverclass_api_call($server,$stream,"getstatus");
        if($this->simple_reply_ok($reply) == true)
        {
            $server_status = $reply["data"]["response"]["data"]["status"];
            if($server_status["serverstate"] == 1)
            {
                return array("status"=>true,"state"=>true);
            }
            else
            {
                return array("status"=>true,"state"=>false);
            }
        }
        return array("status"=>false,"state"=>true);
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
                    $autodj_source_types = array("liquidsoap");
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
        return $this->simple_reply_ok($this->centova_serverclass_api_call($server,$stream,"stop"));
    }
    protected function start_server(stream $stream,server $server,int $skip_auto_dj=0) : bool
    {
        return $this->simple_reply_ok($this->centova_serverclass_api_call($server,$stream,"start",array("noapps"=>$skip_auto_dj)));
    }
    protected function susspend_server(stream $stream,server $server) : bool
    {
        return $this->simple_reply_ok($this->centova_systemclass_api_call($server,"setstatus",array("username"=>$old_username,"status"=>"disabled")));
    }
    protected function un_susspend_server(stream $stream,server $server) : bool
    {
        return $this->simple_reply_ok($this->centova_systemclass_api_call($server,"setstatus",array("username"=>$old_username,"status"=>"enabled")));
    }
    protected function change_password(stream $stream,server $server) : bool
    {
        return $this->simple_reply_ok($this->centova_serverclass_api_call($server,$stream,"reconfigure",array("adminpassword"=>$stream->get_adminpassword(),"sourcepassword"=>$stream->get_djpassword())));
    }
}
?>