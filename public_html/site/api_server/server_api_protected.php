<?php
class server_api_protected extends error_logging
{
    protected $last_api_message = "";
    protected $needs_retry = false;
    protected function stream_state(stream $stream,server $server)
    {
        $this->last_api_message = "Skipped stream_state not supported on this api";
        return array("status"=>false,"state"=>false);
    }
    protected function account_name_list(server $server) : array
    {
        return array("status"=>false,"usernames"=>array(),"message"=>"account_name_list supported on this api");
    }
    protected function sync_username(stream $stream,server $server,string $old_username) : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function toggle_autodj(stream $stream,server $server) : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function autodj_next(stream $stream,server $server) : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function stop_server(stream $stream,server $server) : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function start_server(stream $stream,server $server) : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function susspend_server(stream $stream,server $server) : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function un_susspend_server(stream $stream,server $server) : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function change_password(stream $stream,server $server) : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }

    protected function curl_request(string $url,array $post_data) : array
    {
        if(extension_loaded('curl') == true)
        {
            $post_dataset = "";
            $addon = "";
            foreach($post_data as $key => $value)
            {
                $post_dataset .= $addon;
                $post_dataset .= $key."=".$value;
                $addon = "&";
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post_dataset);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $reply  = curl_exec($ch);
            $curl_error  = curl_error($ch);
            $curl_errno  = curl_errno($ch);
            if (is_resource($ch))
            {
                curl_close ($ch);
            }
            if ($curl_errno === 0)
            {
                return array("status"=>true,"message"=>$reply);
            }
            else
            {
                return array("status"=>false,"message"=>$curl_error);
            }
        }
        else
        {
            return array("status"=>false,"message"=>"Curl not enabled");
        }
    }
}
?>