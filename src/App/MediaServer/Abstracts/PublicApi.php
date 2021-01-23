<?php

namespace App\MediaServer\Abstracts;

abstract class PublicApi extends ApiBasic
{
        /**
     * accountState
     * states:
     *  true = enabled
     *  false = disabled
     * @return mixed[] [status => bool, state=>bool]
     */
    public function getAccountState(): array
    {
        return $this->accountState();
    }
    public function eventStartSyncUsername(string $old_username): bool
    {
        return $this->syncUsername($old_username);
    }
    public function optAutodjNext(): bool
    {
        return $this->autodjNext();
    }
    public function optToggleAutodj(): bool
    {
        return $this->toggleAutodj();
    }
    public function optToggleStatus(bool $status = false): bool
    {
        if ($status == true) {
            return $this->startServer();
        }
        return $this->stopServer();
    }
    public function optPasswordReset(): bool
    {
        return $this->changePassword();
    }
    public function changeTitle(string $newtitle = "New title"): bool
    {
        return $this->changeTitleNow($newtitle);
    }
}
