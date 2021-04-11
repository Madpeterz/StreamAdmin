<?php

namespace App\Helpers\ServerApi;

abstract class FunctionsServerApiHelper extends SetServerApiHelper
{
    protected function flagCheck(string $flagname): bool
    {
        $this->setMessage("starting function " . __FUNCTION__);
        $functionname = "get" . ucfirst($flagname);
        if (($this->api_config->$functionname() == true) && ($this->server->$functionname() == true)) {
            $this->setMessage("API flag " . $flagname . " allowed");
            return true;
        }
        if ($this->api_config->$functionname() == false) {
            $this->setMessage("API flag " . $flagname . " disallowed by api_config");
        }
        if ($this->server->$functionname() == false) {
            $this->setMessage("API flag " . $flagname . " disallowed by server");
        }
        return false;
    }
    protected function checkFlags(array $flags): bool
    {
        $this->setMessage("starting function " . __FUNCTION__);
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
        $this->setMessage($this->serverApi->getLastApiMessage());
        if ($update_status == false) {
            // rollback here rental here as it failed
        }
        return $update_status;
    }
    public function callableAction(string $action): bool
    {
        $this->setMessage("starting function " . __FUNCTION__);
        if ($this->serverApi == null) {
            $this->setMessage("no server api setup");
            return false;
        }
        if (array_key_exists($action, $this->callable_actions) == false) {
            $this->setMessage("Not a known callable action");
            return false;
        }
        if ($this->checkFlags($this->callable_actions[$action]) == false) {
            return false;
        }
        $this->setMessage("Passed callable action checks");
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
