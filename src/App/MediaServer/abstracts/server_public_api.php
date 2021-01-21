<?php

abstract class server_public_api extends server_public_api_basic
{
    public function get_account_state()
    {
        return $this->account_state();
    }
    public function eventStartSyncUsername(string $old_username): bool
    {
        return $this->sync_username($old_username);
    }
    public function optAutodjNext(): bool
    {
        return $this->autodj_next();
    }
    public function optToggleAutodj(): bool
    {
        return $this->toggle_autodj();
    }
    public function optToggleStatus(bool $status = false): bool
    {
        if ($status == true) {
            return $this->start_server();
        } else {
            return $this->stop_server();
        }
    }
    public function optPasswordReset()
    {
        return $this->change_password();
    }
    public function change_title(string $newtitle = "New title")
    {
        return $this->change_title_now($newtitle);
    }
}
