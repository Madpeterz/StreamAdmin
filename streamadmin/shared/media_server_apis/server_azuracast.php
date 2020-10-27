<?php
class server_azuracast extends server_public_api
{
    // $this->last_api_message
    protected function terminate_account(stream $stream,server $server,string $old_username)  : bool
    {

    }
    protected function create_account(stream $stream,server $server,package $package) : bool
    {

    }
    protected function remove_dj(stream $stream,server $server,string $djaccount) : bool
    {

    }
    protected function dj_list(stream $stream,server $server) : array
    {
        
    }
    protected function server_status(server $server) : array
    {

    }
    protected function account_data(stream $stream,server $server)
    {

    }
    protected function account_state(stream $stream,server $server) : array
    {
        //array("status"=>$status,"state"=>$state);
    }
    protected function stream_state(stream $stream,server $server) : array
    {
        //array("status"=>false,"state"=>false,"source"=>false);
    }
    protected function account_name_list(server $server,bool $include_passwords=false,stream_set $stream_set=null) : array
    {
        //array("status"=>$all_ok,"usernames"=>$current_usernames,"passwords"=>$current_passwords);
    }

    protected function sync_username(stream $stream,server $server,string $old_username) : bool
    {

    }
    protected function toggle_autodj(stream $stream,server $server) : bool
    {

    }
    protected function autodj_next(stream $stream,server $server) : bool
    {

    }
    protected function stop_server(stream $stream,server $server) : bool
    {

    }
    protected function start_server(stream $stream,server $server,int $skip_auto_dj=0) : bool
    {

    }
    protected function susspend_server(stream $stream,server $server) : bool
    {

    }
    protected function un_susspend_server(stream $stream,server $server) : bool
    {

    }
    protected function change_password(stream $stream,server $server) : bool
    {

    }
    protected function change_title_now(stream $stream,server $server,string $newtitle="Not set") : bool
    {

    }
}
?>
