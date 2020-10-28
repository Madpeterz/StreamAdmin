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
        $this->last_api_message = "unable to get station status";
        $reply = $this->rest_get("station/".$this->stream->get_api_uid_1()."/status");
        $state = false;
        $source = false;
        $autodj = false;
        if($reply["status"] == true)
        {
            $this->last_api_message = "stream appears to be down";
            $json = json_decode($reply["message"]);
            if($json->frontend_running == true)
            {
                $this->last_api_message = "stream up";
                $state = true;
                $source = true;
                if($json->backend_running == true)
                {
                    $this->last_api_message = "stream up/auto dj up";
                    $autodj = true;
                }
            }
        }
        return array("status"=>$reply["status"],"state"=>$state,"source"=>$source,"autodj"=>$autodj);
    }

    protected function account_name_list(bool $include_passwords=false,stream_set $stream_set=null) : array
    {
        //array("status"=>$all_ok,"usernames"=>$current_usernames,"passwords"=>$current_passwords);

    }

    protected function toggle_autodj() : bool
    {
        $this->last_api_message = "Package does not support auto DJ";
        if($this->package->get_autodj() == true)
        {
            $status_state = $this->stream_state();
            if($status_state["status"] == true)
            {
                $this->last_api_message = "server appears to be down (needs to be started before you can toggle)";
                if($status_state["state"] == true)
                {
                    $options = array(true=>"stop",false=>"start");
                    $this->last_api_message = "Attempting to toggle auto dj";
                    $reply = $this->rest_post("station/".$this->stream->get_api_uid_1()."/backend/".$options[$status_state["autodj"]]);
                    if($reply["status"] == true)
                    {
                        $json = json_decode($reply["message"]);
                        if($json->success == true)
                        {
                            $this->last_api_message = "Toggled auto DJ to: ".$options[$status_state["autodj"]]."";
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    protected function autodj_next() : bool
    {
        $this->last_api_message = "No auto DJ";
        if($this->package->get_autodj() == true)
        {
            $status_state = $this->stream_state();
            if($status_state["status"] == true)
            {
                $this->last_api_message = "AutoDJ not running";
                if($status_state["autodj"] == true)
                {
                    $this->last_api_message = "Failed to skip track";
                    $reply = $this->rest_post("station/".$this->stream->get_api_uid_1()."/backend/skip");
                    if($reply["status"] == true)
                    {
                        $json = json_decode($reply["message"]);
                        $this->last_api_message = "Skip accepted";
                        return $json->success;
                    }
                }
            }
        }
        return false;
    }
    protected function stop_server() : bool
    {
        $stopped_auto_dj = true;
        $this->last_api_message = "Package does not support auto DJ";
        if($this->package->get_autodj() == true)
        {
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
