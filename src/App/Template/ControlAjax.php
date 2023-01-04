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
        global $system;
        $this->siteConfig = $system;
        $this->input = new InputFilter();
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
        string $valuename,
        ?string $oldvalue = null,
        ?string $newvalue = null,
        ?string $moduleName = null
    ): CreateReply {
        $auditLog = new Auditlog();
        $auditLog->setStore($this->siteConfig->getModule());
        if ($moduleName != null) {
            $auditLog->setStore($moduleName);
        }
        $auditLog->setSourceid($sourceid);
        $auditLog->setValuename($valuename);
        $auditLog->setOldvalue($oldvalue);
        $auditLog->setNewvalue($newvalue);
        $auditLog->setUnixtime(time());
        $auditLog->setAvatarLink($this->siteConfig->getSession()->getAvatarLinkId());
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
