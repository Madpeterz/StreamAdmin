<?php

namespace App\Helpers\ServerApi;

use App\MediaServer\Abstracts\PublicApi;
use App\R7\Model\Apis;
use App\R7\Model\Avatar;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use YAPF\Core\SQLi\SqlConnectedClass;

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

    protected function setMessage(string $message): void
    {
        error_log($message);
        $this->message = $message;
    }

    protected $callable_actions = [
        "apiEnableAccount" => ["eventEnableStart"],
        "apiListDjs" => ["eventClearDjs"],
        "apiChangeTitle" => ["eventEnableStart"],
        "apiPurgeDjs" => ["eventClearDjs"],
        "apiDisableAccount" => ["eventDisableRevoke"],
        "apiServerStatus" => ["apiServerStatus"],
        "apiStart" => ["optToggleStatus","eventEnableStart"],
        "apiStop" => ["optToggleStatus","eventEnableStart"],
        "apiAutodjToggle" => ["optToggleAutodj"],
        "apiAutodjNext" => ["optAutodjNext"],
        "apiCustomizeUsername" => ["eventStartSyncUsername"],
        "apiRecreateAccount" => [],
        "apiResetPasswords" => ["optPasswordReset"],
        "apiSetPasswords" => ["optPasswordReset","eventResetPasswordRevoke"],
    ];
}
