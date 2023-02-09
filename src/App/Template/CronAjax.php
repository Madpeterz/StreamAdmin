<?php

namespace App\Template;

use App\Config;
use YAPF\Bootstrap\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

abstract class CronAjax extends ViewAjax
{
    protected $unixtime = 0;
    protected $token = "";
    protected bool $soft_fail = false;
    protected ?InputFilter $input;
    protected Config $siteConfig;


    public function getSoftFail(): bool
    {
        return $this->soft_fail;
    }


    public function __construct(bool $AutoLoadTemplate = false)
    {
        global $system;
        parent::__construct($AutoLoadTemplate);
        $this->input = new InputFilter();
        $this->siteConfig = $system;
        $this->load_ok = true;
        $this->failed("ready");
    }
}
