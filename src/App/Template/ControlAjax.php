<?php

namespace App\Template;

use App\Config;
use App\Models\Auditlog;
use YAPF\Bootstrap\Template\ViewAjax;
use YAPF\Framework\Responses\DbObjects\CreateReply;
use YAPF\InputFilter\InputFilter;

abstract class ControlAjax extends ViewAjax
{
    protected Config $siteConfig;
    protected InputFilter $input;
    public function __construct(bool $AutoLoadTemplate = false)
    {
        parent::__construct($AutoLoadTemplate);
        set_time_limit(15);
        global $system;
        $this->siteConfig = $system;
        $this->input = new InputFilter();
    }
    protected function failed(string $message): void
    {
        $this->setMessage($message, false);
    }
    protected function redirectWithMessage(string $message, ?string $to = null): void
    {
        if ($to === null) {
            $to = $this->siteConfig->getModule();
        }
        $this->setSwapTag("redirect", $to);
        $this->ok($message);
    }
    protected function ok(string $message = ""): void
    {
        $this->setMessage($message, true);
    }
    public function createAuditLog(
        ?string $sourceid = null,
        string $valuename = "Not Set",
        ?string $oldvalue = null,
        ?string $newvalue = null,
        ?string $moduleName = null
    ): CreateReply {
        $auditLog = new Auditlog();
        $auditLog->_Store = $this->siteConfig->getModule();
        if ($moduleName != null) {
            $auditLog->_Store = $moduleName;
        }
        $auditLog->_Sourceid = $sourceid;
        $auditLog->_Valuename = $valuename;
        $auditLog->_Oldvalue = $oldvalue;
        $auditLog->_Newvalue = $newvalue;
        $auditLog->_Unixtime = time();
        $auditLog->_AvatarLink = $this->siteConfig->getSession()->getAvatarLinkId();
        return $auditLog->createEntry();
    }
    public function createMultiAudit(?string $sourceid, array $fields, array $oldvalues, array $newvalues): void
    {
        $loop = 0;
        while ($loop < count($fields)) {
            if ($fields[$loop] == "id") {
                $loop++;
                continue;
            }
            if ($oldvalues[$loop] != $newvalues[$loop]) {
                $this->createAuditLog($sourceid, $fields[$loop], $oldvalues[$loop], $newvalues[$loop]);
            }
            $loop++;
        }
    }
}
