<?php

namespace App\Helpers\ServerApi;

use App\MediaServer\Abstracts\PublicApi;
use App\R7\Model\Apis;
use App\R7\Model\Avatar;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use YAPF\Core\SqlConnectedClass;

abstract class DefinesServerApiHelper extends SqlConnectedClass
{
    protected ?Server $server = null;
    protected ?Package $package = null;
    protected ?Rental $rental = null;
    protected ?Stream $stream = null;
    protected ?Avatar $avatar = null;
    protected $message = "No actions taken";
    protected ?PublicApi $serverApi = null;
    protected ?Apis $api_config = null;

    protected $callable_actions = [
        "api_enable_account" => ["eventEnableStart"],
        "api_list_djs" => ["eventClearDjs"],
        "api_change_title" => ["eventEnableStart"],
        "api_purge_djs" => ["eventClearDjs"],
        "api_disable_account" => ["eventDisableRevoke"],
        "apiServerStatus" => ["apiServerStatus"],
        "api_start" => ["optToggleStatus","eventEnableStart"],
        "api_stop" => ["optToggleStatus","eventEnableStart"],
        "api_autodj_toggle" => ["optToggleAutodj"],
        "api_autodj_next" => ["optAutodjNext"],
        "api_customize_username" => ["eventStartSyncUsername"],
        "api_recreate_account" => [],
        "api_reset_passwords" => ["optPasswordReset"],
        "api_set_passwords" => ["optPasswordReset","eventResetPasswordRevoke"],
    ];


    public function apiAutodjToggle(): bool
    {
        return false;
    }
    public function apiResetPasswords(): bool
    {
        return false;
    }
    public function apiAutodjNext(): bool
    {
        return false;
    }
    public function apiCustomizeUsername(): bool
    {
        return false;
    }
    public function apiEnableAccount(): bool
    {
        return false;
    }
    public function apiDisableAccount(): bool
    {
        return false;
    }
    public function apiPurgeDjs(): bool
    {
        return false;
    }
    public function apiRecreateAccount(): bool
    {
        return false;
    }
}
