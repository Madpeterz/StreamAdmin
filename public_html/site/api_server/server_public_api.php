<?php
class server_public_api extends server_public_api_basic
{
    public function event_start_sync_username(stream $stream,server $server,string $old_username) : bool
    {
        return $this->sync_username($stream,$server,$old_username);
    }
    public function opt_autodj_next(stream $stream,server $server) : bool
    {
        return $this->autodj_next($stream,$server);
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
    public function opt_password_reset(stream $stream,server $server)
    {
        return $this->change_password($stream,$server);
    }
    public function change_tile(stream $stream,server $server,string $newtitle="New title")
    {
        return $this->change_title_now($stream,$server,$newtitle);
    }
}
?>
