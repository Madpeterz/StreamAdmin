<?php

namespace App\MediaServer\Abstracts;

use App\R7\Model\Package;
use App\R7\Set\StreamSet;

abstract class ApiBasic extends ApiProtected
{
    public function getLastApiMessage(): string
    {
        if (is_string($this->last_api_message) == false) {
            if (is_array($this->last_api_message) == true) {
                return "" . json_encode($this->last_api_message) . "";
            }
            return "last api message broken";
        }
        return $this->last_api_message;
    }

    public function getLastApiNeedRetry(): bool
    {
        return $this->needs_retry;
    }
    /**
     * accountNameList
     * @return mixed[] [status => bool, usernames=>array,passwords=>array]
     */
    public function accountNameList(bool $include_passwords = false, StreamSet $stream_set = null): array
    {
        $this->last_api_message = "Skipped accountNameList not supported on this api";
        return ["status" => false,"usernames" => [],"passwords" => []];
    }
    /**
     * serverStatus
     * @return mixed[] [status => bool, loads=>[1,5,15], ram=>[free,max], streams=>[total,active], message=> string]
     */
    public function serverStatus(): array
    {
        return [
            "status" => false,
            "loads" => ["1" => 0,"5" => 0,"15" => 0],
            "ram" => ["free" => 0,"max" => 0],
            "streams" => ["total" => 0,"active" => 0],
            "message" => "This api does not support server status",
        ];
    }
    /**
     * streamState
     * @return mixed[] [status => bool, state=>bool,source=>bool, autodj=>bool, message=>string]
     */
    public function streamState(): array
    {
        $this->last_api_message = "Skipped stream_state not supported on this api";
        return [
            "message" => "Server appears to be down",
            "status" => false,
            "state" => false,
            "source" => false,
            "autodj" => false,
        ];
    }
    /**
     * djList
     * @return mixed[] [status => bool, list=> array]
     */
    public function djList(): array
    {
        $this->last_api_message = "Skipped dj_list not supported on this api";
        return ["status" => true,"list" => []];
    }
    public function purgeDjAccount(string $djaccount): bool
    {
        return $this->removeDj($djaccount);
    }
    public function setAccountState(bool $state): bool
    {
        if ($this->package == null) {
            $this->package = new Package();
            if ($this->package->loadID($this->stream->getPackageLink()) == false) {
                $this->package = null;
            }
        }
        if ($this->package == null) {
            $this->last_api_message = "Unable to get package";
            return false;
        }
        $account_state = $this->accountState($this->stream, $this->server);
        if ($account_state["status"] == false) {
            $this->last_api_message = "Unable to get account state: " . $account_state["message"];
            return false;
        }
        if ($account_state["state"] == $state) {
            $this->last_api_message = "No change needed";
            return true;
        }
        if ($state == true) {
            if ($this->unSusspendServer() == false) {
                return false;
            }
            if ($this->package->getAutodj() == false) {
                return $this->startServer();
            }
            return true;
        }
        return $this->susspendServer();
    }
    public function removeAccount(string $old_username = ""): bool
    {
        return $this->terminateAccount($old_username);
    }
    public function recreateAccount(): bool
    {
        $this->last_api_message = "Running recreateAccount";
        return $this->createAccount();
    }
}
