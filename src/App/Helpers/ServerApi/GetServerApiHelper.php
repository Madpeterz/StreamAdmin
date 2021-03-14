<?php

namespace App\Helpers\ServerApi;

abstract class GetServerApiHelper extends DefinesServerApiHelper
{
    public function getMessage(): string
    {
        return $this->message;
    }
    public function eventRecreateRevoke(): bool
    {
        return $this->apiRecreateAccount();
    }
    public function eventEnableStart(): bool
    {
        return $this->apiEnableAccount();
    }
    public function eventClearDjs(): bool
    {
        return $this->apiPurgeDjs();
    }
    public function eventDisableExpire(): bool
    {
        return $this->apiDisableAccount();
    }
    public function eventDisableRevoke(): bool
    {
        return $this->apiDisableAccount();
    }
    public function eventEnableRenew(): bool
    {
        return $this->apiEnableAccount();
    }
    public function eventResetPasswordRevoke(): bool
    {
        return $this->apiResetPasswords();
    }
    public function eventStartSyncUsername(): bool
    {
        return $this->apiCustomizeUsername();
    }
    public function eventRevokeResetUsername(): bool
    {
        return $this->apiCustomizeUsername();
    }
    public function optAutodjNext(): bool
    {
        return $this->apiAutodjNext();
    }
    public function optPasswordReset(): bool
    {
        return $this->apiResetPasswords();
    }
    public function optToggleAutodj(): bool
    {
        return $this->apiAutodjToggle();
    }
}
