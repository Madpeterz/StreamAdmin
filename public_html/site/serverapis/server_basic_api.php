<?php
class server_basic_api extends error_logging
{
    public function event_disable_expire(stream $stream,server $server) : bool
    {
        if($this->stop_server($stream,$server) == true)
        {
            return $this->susspend_server($stream,$server);
        }
        return false;
    }
    public function event_disable_revoke(stream $stream,server $server) : bool
    {
        if($this->stop_server($stream,$server) == true)
        {
            if($this->change_password($stream,$server) == true)
            {
                return $this->susspend_server($stream,$server);
            }
        }
        return false;
    }
    public function event_enable_start(stream $stream,server $server) : bool
    {
        if($this->un_susspend_server($stream,$server) == true)
        {
            return $this->start_server($stream,$server);
        }
        return false;
    }
    public function event_reset_password_revoke(stream $stream,server $server) : bool
    {
        if($this->stop_server($stream,$server) == true)
        {
            return $this->change_password($stream,$server);
        }
        return false;
    }
    public function opt_autodj_next(stream $stream,server $server) : bool
    {
        return $this->autodj_next($stream,$server);
    }
    public function opt_password_reset(stream $stream,server $server) : bool
    {
        if($this->stop_server($stream,$server) == true)
        {
            if($this->change_password($stream,$server) == true)
            {
                return $this->start_server($stream,$server);
            }
        }
        return false;
    }
    public function opt_toggle_autodj(stream $stream,server $server) : bool
    {
        return $this->toggle_autodj($stream,$server);
    }

    public function opt_toggle_status(stream $stream,server $server,bool $status=false) : bool
    {
        if($status == true) return $this->start_server($stream,$server);
        else return $this->stop_server($stream,$server);
    }

    protected function toggle_autodj(stream $stream,server $server) : bool
    {
        return true;
    }
    protected function autodj_next(stream $stream,server $server) : bool
    {
        return true;
    }
    protected function stop_server(stream $stream,server $server) : bool
    {
        return true;
    }
    protected function start_server(stream $stream,server $server) : bool
    {
        return true;
    }
    protected function susspend_server(stream $stream,server $server) : bool
    {
        return true;
    }
    protected function un_susspend_server(stream $stream,server $server) : bool
    {
        return true;
    }
    protected function change_password(stream $stream,server $server) : bool
    {
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
