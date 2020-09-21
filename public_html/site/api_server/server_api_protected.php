<?php
class server_api_protected extends error_logging
{
    protected $last_api_message = "";
    protected $needs_retry = false;
    protected function stream_state(stream $stream,server $server) : array
    {
        $this->last_api_message = "Skipped stream_state not supported on this api";
        return array("status"=>false,"state"=>false,"source"=>false);
    }
    protected function terminate_account(stream $stream,server $server,string $old_username)  : bool
    {
        $this->last_api_message = "Skipped terminate_account not supported on this api";
        return true;
    }
    protected function create_account(stream $stream,server $server,package $package) : bool
    {
        $this->last_api_message = "Skipped create_account not supported on this api";
        return true;
    }
    protected function dj_list(stream $stream,server $server) : array
    {
        $this->last_api_message = "Skipped dj_list not supported on this api";
        return array("status"=>true,"list"=>array());
    }
    protected function remove_dj(stream $stream,server $server,string $djaccount) : bool
    {
        $this->last_api_message = "Skipped remove_dj not supported on this api";
        return true;
    }
    protected function account_state(stream $stream,server $server) : array
    {
        $this->last_api_message = "Skipped account_state not supported on this api";
        return array("status"=>false,"state"=>false);
    }
    protected function account_name_list(server $server,bool $include_passwords=false,stream_set $stream_set=null) : array
    {
        return array("status"=>false,"usernames"=>array(),"message"=>"account_name_list supported on this api");
    }
    protected function sync_username(stream $stream,server $server,string $old_username) : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function server_status(server $server) : array
    {
        return array("status"=>false,"loads"=>array("1"=>0,"5"=>0,"15"=>0),"ram"=>array("free"=>0,"max"=>0),"streams"=>array("total"=>0,"active"=>0),"message"=>"This api does not support server status");
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
    protected function change_title_now(stream $stream,server $server,string $newtitle) : bool
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
