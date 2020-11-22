<?php

abstract class server_public_api_basic extends server_api_protected
{
    public function get_last_api_message(): string
    {
        if (is_string($this->last_api_message) == false) {
            return "last api message broken";
        }
        return $this->last_api_message;
    }
    public function get_last_api_need_retry(): bool
    {
        return $this->needs_retry;
    }
    public function get_account_name_list(bool $include_passwords = false, stream_set $stream_set = null): array
    {
        return $this->account_name_list($include_passwords, $stream_set);
    }
    public function get_server_status(): array
    {
        return $this->server_status();
    }
    public function get_stream_state(): array
    {
        return $this->stream_state();
    }
    public function get_dj_list(): array
    {
        return $this->dj_list();
    }
    public function purge_dj_account(string $djaccount): bool
    {
        return $this->remove_dj($djaccount);
    }
    public function set_account_state(bool $state): bool
    {
        if ($this->package == null) {
            $this->package = new package();
            if ($this->package->loadID($this->stream->getPackagelink()) == false) {
                $this->package = null;
            }
        }
        if ($this->package != null) {
            $account_state = $this->account_state($this->stream, $this->server);
            if ($account_state["status"] == true) {
                if ($account_state["state"] != $state) {
                    if ($state == true) {
                        if ($this->un_susspend_server() == true) {
                            if ($this->package->getAutodj() == false) {
                                return $this->start_server();
                            } else {
                                return true;
                            }
                        }
                    } else {
                        return $this->susspend_server();
                    }
                } else {
                    $this->last_api_message = "No action required";
                    return true;
                }
            } else {
                $this->last_api_message = "Unable to get account state";
            }
        } else {
            $this->last_api_message = "Unable to get package";
        }
        return false;
    }
    public function remove_account(string $old_username): bool
    {
        return $this->terminate_account($old_username);
    }
    public function recreate_account(): bool
    {
        return $this->create_account();
    }
}
