<?php

namespace App\MediaServer\Abstracts;

use App\Models\Package;
use App\Models\StreamSet;

abstract class ApiBasic extends ApiProtected
{
    public function getLastApiMessage(): string
    {
        if (is_string($this->last_api_message) == false) {
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
     * @return mixed[] [status => bool, state=>bool,source=>bool, autodj=>bool]
     */
    public function streamState(): array
    {
        $this->last_api_message = "Skipped stream_state not supported on this api";
        return ["status" => false,"state" => false,"source" => false,"autodj" => false];
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
        if ($this->package != null) {
            $account_state = $this->accountState($this->stream, $this->server);
            if ($account_state["status"] == true) {
                if ($account_state["state"] != $state) {
                    if ($state == true) {
                        if ($this->unSusspendServer() == true) {
                            if ($this->package->getAutodj() == false) {
                                return $this->startServer();
                            } else {
                                return true;
                            }
                        }
                    } else {
                        return $this->susspendServer();
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
    public function removeAccount(string $old_username): bool
    {
        return $this->terminateAccount($old_username);
    }
    public function recreateAccount(): bool
    {
        return $this->createAccount();
    }
}
