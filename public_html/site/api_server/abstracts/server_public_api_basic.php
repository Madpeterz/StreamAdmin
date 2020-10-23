<?php
abstract class server_public_api_basic extends server_api_protected
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
    public function get_account_name_list(server $server,bool $include_passwords=false,stream_set $stream_set=null) : array
    {
        return $this->account_name_list($server,$include_passwords,$stream_set);
    }
    public function get_server_status(server $server) : array
    {
        return $this->server_status($server);
    }
    public function get_stream_state(stream $stream,server $server) : array
    {
        return $this->stream_state($stream,$server);
    }
    public function get_dj_list(stream $stream,server $server) : array
    {
        return $this->dj_list($stream,$server);
    }
    public function purge_dj_account(stream $stream,server $server,string $djaccount) : bool
    {
        return $this->remove_dj($stream,$server,$djaccount);
    }
    public function set_account_state(stream $stream,server $server,bool $state) : bool
    {
        $package = new package();
        if($package->load($stream->get_packagelink()) == true)
        {
            $account_state = $this->account_state($stream,$server);
            if($account_state["status"] == true)
            {
                if($account_state["state"] != $state)
                {
                    if($state == true)
                    {
                        if($this->un_susspend_server($stream,$server) == true)
                        {
                            if($package->get_autodj() == false)
                            {
                                return $this->start_server($stream,$server);
                            }
                            else
                            {
                                return true;
                            }
                        }
                    }
                    else
                    {
                        return $this->susspend_server($stream,$server);
                    }
                }
                else
                {
                    $this->last_api_message = "No action required";
                    return true;
                }
            }
            else
            {
                $this->last_api_message = "Unable to get account state";
            }
        }
        else
        {
            $this->last_api_message = "Unable to get package";
        }
        return false;
    }
    public function remove_account(stream $stream,server $server,string $old_username) : bool
    {
        return $this->terminate_account($stream,$server,$old_username);
    }
    public function recreate_account(stream $stream,server $server,package $package) : bool
    {
        return $this->create_account($stream,$server,$package);
    }
}
?>
