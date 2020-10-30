<?php
abstract class server_public_api extends server_public_api_basic
{
    public function get_account_state()
    {
        return $this->account_state();
    }
    public function event_start_sync_username(string $old_username) : bool
    {
        return $this->sync_username($old_username);
    }
    public function opt_autodj_next() : bool
    {
        return $this->autodj_next();
    }
    public function opt_toggle_autodj() : bool
    {
        return $this->toggle_autodj();
    }
    public function opt_toggle_status(bool $status=false) : bool
    {
        if($status == true) return $this->start_server();
        else return $this->stop_server();
    }
    public function opt_password_reset()
    {
        return $this->change_password();
    }
    public function change_title(string $newtitle="New title")
    {
        return $this->change_title_now($newtitle);
    }
}
?>
