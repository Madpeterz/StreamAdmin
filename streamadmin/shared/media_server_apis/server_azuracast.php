<?php
class server_azuracast extends server_public_api
{
    // $this->last_api_message
    protected $station_djs_map = null;
    protected function get_client_auth()
    {
        $this->options['headers']['Authorization'] = 'Bearer ' . $this->server->get_api_password();
    }
    protected function get_post_formated(array $postdata=array()) : array
    {
        return array('json' => $postdata);
    }
    protected function terminate_account(string $old_username)  : bool
    {

    }
    protected function create_account() : bool
    {

    }
    protected function remove_dj(string $djaccount) : bool
    {
        $this->last_api_message = "unable to get list of djs";
        $has_dj_list = true;
        if($this->station_djs_map == null)
        {
            $reply = $this->get_dj_list();
            $has_dj_list = $reply["status"];
        }
        if($has_dj_list == true)
        {
            $this->last_api_message = "unable to find DJ";
            $removed = false;
            foreach($this->station_djs_map as $key => $value)
            {
                if($value == $djaccount)
                {
                    $this->last_api_message = "unable to remove DJ";
                    $reply = $this->rest_delete("station/".$this->stream->get_api_uid_1()."/streamer/".$key."");
                    if($reply["status"] == true)
                    {
                        $json = json_decode($reply["message"]);
                        $removed = $json->success;
                        $this->last_api_message = "DJ removed";
                        if($json->success == false)
                        {
                            $this->last_api_message = "Failed to remove DJ account";
                        }
                    }
                    break;
                }
            }
            return $removed;
        }
        return false;
    }
    protected function dj_list() : array
    {
        $this->last_api_message = "unable to get list of djs";
        $reply = $this->rest_get("station/".$this->stream->get_api_uid_1()."/streamers");
        if($reply["status"] == true)
        {
            $this->last_api_message = "fetched DJ list";
            $json = json_decode($reply["message"]);
            $this->station_djs_map = array();
            foreach($json as $index => $entry)
            {
                $this->station_djs_map[$entry->id] = $entry->streamer_username;
            }
            return array("status"=>true,"list"=>array_values($this->station_djs_map));
        }
        return array("status"=>false,"list"=>array());
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
    protected function account_state() : array
    {
        //array("status"=>$status,"state"=>$state);
        $status = false;
        $state = false;
        $this->last_api_message = "fetching account state";
        $reply = $this->rest_get("admin/user/".$this->stream->get_api_uid_3()."");
        $status = $reply["status"];
        if($status == true)
        {
            $this->last_api_message = "got account state";
            $json = json_decode($reply["message"]);
            if(count($json->roles) >= 1)
            {
                $state = true;
            }
        }
        return array("status"=>$status,"state"=>$state);
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
        $reply = $this->rest_get("​admin​/users");
        $usernames = array();
        $passwords = array();
        $status = $reply["status"] ;
        if($status == true)
        {
            $json = json_decode($reply["message"]);
            foreach($json as $index => $entry)
            {
                $usernames[] = $entry->email;
                if($include_passwords == true)
                {
                    $passwords[] = $entry->new_password; /// ?
                }
            }
        }
        return array("status"=>$status,"usernames"=>$usernames,"passwords"=>$passwords);
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
        $status_state = $this->stream_state();
        if($status_state["status"] == false )
        {
            return false;
        }
        if($status_state["state"] == false)
        {
            $this->last_api_message = "server already stopped";
            return true;
        }
        $stopped_auto_dj = !$status_state["autodj"];
        if($stopped_auto_dj == false)
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
        $reply = $this->account_state();
        $status = false;
        if($reply["status"] == true)
        {
            if($reply["state"] == false)
            {
                $this->last_api_message = "Account allready susspended";
                return false;
            }
            if($this->stop_server() == false)
            {
                return false;
            }
            $this->last_api_message = "Attempting to remove access to server";
            $post_fields = array("roles"=>array());
            $reply = $this->rest_put("admin/user/".$this->stream->get_api_uid_3()."",$post_fields);
            error_log(print_r($reply,true));
            if($reply["status"] == true)
            {
                $json = json_decode($reply["message"]);
                if($json->success == true)
                {
                    $this->last_api_message = "Account: susspended";
                }
                return $json->success;
            }
        }
        return false;
    }
    protected function un_susspend_server() : bool
    {
        $reply = $this->account_state();
        $status = false;
        if($reply["status"] == true)
        {
            if($reply["state"] == true)
            {
                $this->last_api_message = "Account allready active";
                return false;
            }
            $this->last_api_message = "Sending request to un_susspend to server";
            $post_fields = array("roles"=>array($this->stream->get_api_uid_2()));
            $reply = $this->rest_put("admin/user/".$this->stream->get_api_uid_3()."",$post_fields);
            if($reply["status"] == true)
            {
                $this->last_api_message = "Request failed";
                $json = json_decode($reply["message"]);
                if($json->success == true)
                {
                    $this->last_api_message = "Account: active";
                }
                return $json->success;
            }
        }
    }
    protected function change_password() : bool
    {

    }
    protected function change_title_now(string $newtitle="Not set") : bool
    {

    }
}
?>
