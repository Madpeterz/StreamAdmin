<?php

namespace App\Helpers\ServerApi;

abstract class FunctionsServerApiHelper extends SetServerApiHelper
{
    protected function flagCheck(string $flagname): bool
    {
        $functionname = "get" . ucfirst($flagname);
        if (($this->api_config->$functionname() == true) && ($this->server->$functionname() == true)) {
            $this->message = "API flag " . $flagname . " allowed";
            return true;
        }
        if ($this->api_config->$functionname() == false) {
            $this->message = "API flag " . $flagname . " disallowed by api_config";
        }
        if ($this->server->$functionname() == false) {
            $this->message = "API flag " . $flagname . " disallowed by server";
        }
        return false;
    }
    protected function checkFlags(array $flags): bool
    {
        $flag_accepted = true;
        foreach ($flags as $flag) {
            if ($this->flagCheck($flag) == false) {
                $flag_accepted = false;
                break;
            }
        }
        return $flag_accepted;
    }
    protected function randString(int $length): string
    {
        if ($length < 8) {
            $length = 8;
        }
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }
    protected function updateAccountState(bool $state): bool
    {
        if ($this->rental != null) {
            // flag to set rental to $state
        }
        $update_status = $this->serverApi->setAccountState($state);
        $this->message = $this->serverApi->getLastApiMessage();
        if ($update_status == false) {
            // rollback here rental here as it failed
        }
        return $update_status;
    }
    public function callableAction(string $action): bool
    {
        if ($this->serverApi == null) {
            $this->message = "no server api setup";
            return false;
        }
        if (array_key_exists($action, $this->callable_actions) == false) {
            $this->message = "Not a known callable action";
            return false;
        }
        if ($this->checkFlags($this->callable_actions[$action]) == false) {
            return false;
        }
        $this->message = "Passed callable action checks";
        return true;
    }

    protected $dj_list = [];
    protected $removed_dj_counter = 0;
    /**
     * loadedDjs
     * @return mixed[]
     */
    public function loadedDjs(): array
    {
        return $this->dj_list;
    }
    public function getRemovedDjCounter(): int
    {
        return $this->removed_dj_counter;
    }
}
