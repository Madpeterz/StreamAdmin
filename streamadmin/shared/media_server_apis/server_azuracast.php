<?php
class server_azuracast extends server_public_api
{
    // $this->last_api_message
    protected function terminate_account(string $old_username)  : bool
    {

    }
    protected function create_account() : bool
    {

    }
    protected function remove_dj(string $djaccount) : bool
    {

    }
    protected function dj_list() : array
    {

    }
    protected function server_status() : array
    {
        $reply = $this->rest_get("status");
        if($reply["status"] == true)
        {
            $json = json_decode($reply["message"]);
            return array("status"=>$json->online,"loads"=>array("1"=>0,"5"=>0,"15"=>0),"ram"=>array("free"=>0,"max"=>0),"streams"=>array("total"=>0,"active"=>0),"message"=>"Limited reply");
        }
        else
        {
            return array("status"=>false,"loads"=>array("1"=>0,"5"=>0,"15"=>0),"ram"=>array("free"=>0,"max"=>0),"streams"=>array("total"=>0,"active"=>0),"message"=>"Limited reply");
        }
    }
    protected function account_data()
    {

    }
    protected function account_state() : array
    {
        //array("status"=>$status,"state"=>$state);
        return array("status"=>true,"state"=>true);
    }
    protected function stream_state() : array
    {
        //array("status"=>false,"state"=>false,"source"=>false);

    }
    protected function account_name_list(bool $include_passwords=false,stream_set $stream_set=null) : array
    {
        //array("status"=>$all_ok,"usernames"=>$current_usernames,"passwords"=>$current_passwords);
    }

    protected function sync_username(string $old_username) : bool
    {

    }

    protected function toggle_autodj() : bool
    {

    }
    protected function autodj_next() : bool
    {
        if($this->package->get_autodj() == true)
        {
            $reply = $this->rest_post("station/".$this->stream->get_api_uid_1()."/backend/skip");
            if($reply["status"] == true)
            {
                $json = json_decode($reply["message"]);
                $this->last_api_message = "Skip accepted";
                return $json->success;
            }
            else
            {
                $this->last_api_message = "Failed to skip track";
            }
        }
        else
        {
            $this->last_api_message = "No auto DJ";
        }
        return false;
    }
    protected function stop_server() : bool
    {
        $stopped_auto_dj = true;
        $this->last_api_message = "";
        if($this->package->get_autodj() == true)
        {
            // stop auto DJ
            $this->last_api_message = "Unable to stop autoDJ";
            $reply = $this->rest_post("station/".$this->stream->get_api_uid_1()."/backend/stop");
            if($reply["status"] == true)
            {
                $json = json_decode($reply["message"]);
                $stopped_auto_dj = $json->success;
            }
        }
        if($stopped_auto_dj == true)
        {
            $this->last_api_message = "Unable to stop stream";
            // stop stream
            $reply = $this->rest_post("station/".$this->stream->get_api_uid_1()."/frontend/stop");
            if($reply["status"] == true)
            {
                $json = json_decode($reply["message"]);
                if($json->success == true)
                {
                    $this->last_api_message = "server stopped";
                    return true;
                }
            }

        }
        return false;
    }
    protected function start_server(int $skip_auto_dj=0) : bool
    {
        $this->last_api_message = "Unable to start stream";
        // stop stream
        $reply = $this->rest_post("station/".$this->stream->get_api_uid_1()."/frontend/start");
        if($reply["status"] == true)
        {
            $json = json_decode($reply["message"]);
            if($json->success == true)
            {
                if($skip_auto_dj == 1)
                {
                    $this->last_api_message = "Server started";
                    return true;
                }
                if($this->package->get_autodj() == true)
                {
                    $this->last_api_message = "Server started / Unable to start AutoDJ";
                    $reply = $this->rest_post("station/".$this->stream->get_api_uid_1()."/backend/start");
                    if($reply["status"] == true)
                    {
                        $json = json_decode($reply["message"]);
                        if($json->success == true)
                        {
                            $this->last_api_message = "Server+AutoDJ started";
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    protected function susspend_server() : bool
    {
        if($this->stop_server() == true)
        {

        }
    }
    protected function un_susspend_server() : bool
    {

    }
    protected function change_password() : bool
    {

    }
    protected function change_title_now(string $newtitle="Not set") : bool
    {

    }
}
?>
