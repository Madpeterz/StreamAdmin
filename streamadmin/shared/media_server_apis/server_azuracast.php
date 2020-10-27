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
        $reply = $this->rest_get("/status");
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
            $reply = $this->rest_post("/station/".$this->stream->set_api_uid_1()."/backend/skip");
        }
    }
    protected function stop_server() : bool
    {
        if($this->package->get_autodj() == true)
        {
            // stop auto DJ
            $reply = $this->rest_post("/station/".$this->stream->set_api_uid_1()."/backend/stop");
        }
        // stop stream
        $reply = $this->rest_post("/station/".$this->stream->set_api_uid_1()."/frontend/stop");
    }
    protected function start_server(int $skip_auto_dj=0) : bool
    {
        // start stream
        $reply = $this->rest_post("/station/".$this->stream->set_api_uid_1()."/frontend/start");

        if($skip_auto_dj == 0)
        {
            // also start autoDJ if enabled
            if($this->package->get_autodj() == true)
            {
                // start auto DJ
                $reply = $this->rest_post("/station/".$this->stream->set_api_uid_1()."/backend/start");
            }
        }
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
