<?php
class server_centova3 extends server_basic_api
{
    // does nothing ^+^
    protected function process_centova_api_call(server $server,stream $stream,array $post_data,array $args) : array
    {
        $post_data["f"] = "json";
        $post_data["a[password]"] = "".$server->get_api_username()."|".$server->get_api_password()."";
        $post_data["a[username]"] = $stream->get_adminusername();
        foreach($args as $key => $value)
        {
            $post_data["a[".$key."]"] = $value;
        }
        $reply = $this->curl_request($server->api_url(),$post_data);
        if($reply["status"] == true)
        {
            return array("status"=>true,"message"=>"ok","data"=>json_decode($reply["message"], true));
        }
        else
        {
            return array("status"=>false,"message"=>$reply["message"],"data"=>array());
        }
    }
    protected function centova_serverclass_api_call(server $server,stream $stream,string $method,array $args=array()) : array
    {
        return $this->process_centova_api_call($server,$stream,array("xm"=>"server.".$method.""),$args);
    }
    protected function centova_systemclass_api_call(server $server,stream $stream,string $method,array $args=array()) : array
    {
        return $this->process_centova_api_call($server,$stream,array("xm"=>"system.".$method.""),$args);
    }
    protected function toggle_autodj(stream $stream,server $server) : bool
    {
        $dataset = $this->centova_serverclass_api_call($server,$stream,"getstatus",array("mountpoints"=>"all"));
        if($dataset["status"] == true)
        {
            if($dataset["data"]["status"]["serverstate"] == 1)
            {
                if($this->stop_server($stream,$server) == true)
                {
                    return $this->start_server($stream,$server,$dataset["data"]["status"]["sourcestate"]);
                }
            }
            else
            {
                return $this->start_server($stream,$server,0);
            }
        }
        return false;
    }
    protected function autodj_next(stream $stream,server $server) : bool
    {
        return $this->centova_serverclass_api_call($server,$stream,"nextsong")["status"];
    }
    protected function stop_server(stream $stream,server $server) : bool
    {
        return $this->centova_serverclass_api_call($server,$stream,"stop")["status"];
    }
    protected function start_server(stream $stream,server $server,int $skip_auto_dj=0) : bool
    {
        return $this->centova_serverclass_api_call($server,$stream,"start",array("noapps"=>$skip_auto_dj))["status"];
    }
    protected function susspend_server(stream $stream,server $server) : bool
    {
        return $this->centova_systemclass_api_call($server,$stream,"setstatus",array("status"=>"disabled"))["status"];
    }
    protected function un_susspend_server(stream $stream,server $server) : bool
    {
        return $this->centova_systemclass_api_call($server,$stream,"setstatus",array("status"=>"enabled"))["status"];
    }
    protected function change_password(stream $stream,server $server) : bool
    {
        return $this->centova_serverclass_api_call($server,$stream,"reconfigure",array("adminpassword"=>$stream->get_adminpassword(),"sourcepassword"=>$stream->get_djpassword()))["status"];
    }
}
?>
