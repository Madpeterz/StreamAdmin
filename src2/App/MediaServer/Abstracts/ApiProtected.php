<?php

namespace App\MediaServer\Abstracts;

use App\R7\Set\StreamSet;

abstract class ApiProtected extends RestApi
{
    protected $last_api_message = "";
    protected $needs_retry = false;

    protected function terminateAccount(string $old_username): bool
    {
        $this->last_api_message = "Skipped terminateAccount not supported on this api";
        return true;
    }
    protected function createAccount(): bool
    {
        $this->last_api_message = "Skipped create_account not supported on this api";
        return true;
    }

    protected function removeDJ(string $djaccount): bool
    {
        $this->last_api_message = "Skipped remove_dj not supported on this api";
        return true;
    }
    /**
     * accountState
     * states:
     *  true = enabled
     *  false = disabled
     * @return mixed[] [status => bool, state=>bool, "message" => ""]
     */
    protected function accountState(): array
    {
        $this->last_api_message = "Skipped account_state not supported on this api";
        return ["status" => false,"state" => false, "message" => "Skipped"];
    }

    protected function syncUsername(string $old_username): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }

    protected function toggleAutodj(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function autodjNext(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function stopServer(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function startServer(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function susspendServer(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function unSusspendServer(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function changePassword(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function changeTitleNow(string $newtitle): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
}
