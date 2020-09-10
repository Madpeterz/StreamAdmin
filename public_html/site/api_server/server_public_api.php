<?php
class server_public_api extends server_api_protected
{
    public function get_last_api_message() : string
    {
        if(is_string($this->last_api_message) == false) return "last api message broken";
        return $this->last_api_message;
    }
    public function get_last_api_need_retry() : bool
    {
        return $this->needs_retry;
    }
    public function get_account_name_list(server $server) : array
    {
        return $this->account_name_list($server);
    }
    public function get_server_status(server $server) : array
    {
        return $this->server_status($server);
    }
    public function get_stream_state(stream $stream,server $server) : array
    {
        return $this->stream_state($stream,$server);
    }
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
        $this->last_api_message = "running opt_password_reset";
        $stream_state_check = $this->get_stream_state($stream,$server);
        $server_stopped = true;
        $server_running = false;
        $server_check_ok = false;
        if($stream_state_check["status"] == true)
        {
            $server_check_ok = true;
            $server_running = $stream_state_check["state"];
        }
        if($server_check_ok == true)
        {
            if($server_running == true)
            {
                $server_stopped = $this->stop_server($stream,$server);
            }
            if($server_stopped == true)
            {
                if($this->change_password($stream,$server) == true)
                {
                    if($server_running == true)
                    {
                        if($this->start_server($stream,$server) == true)
                        {
                            $this->last_api_message = "Password changed, server started";
                        }
                        else
                        {
                            $this->last_api_message = "Password changed, unable to start server";
                        }
                    }
                    else
                    {
                        $this->last_api_message = "Password changed, server not running";
                    }
                    return true;
                }
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

    public function event_start_sync_username(stream $stream,server $server,string $old_username) : bool
    {
        return $this->sync_username($stream,$server,$old_username);
    }
}
?>
