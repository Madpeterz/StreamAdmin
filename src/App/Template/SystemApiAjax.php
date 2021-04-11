<?php

namespace App\Template;

use YAPF\InputFilter\InputFilter;

abstract class SystemApiAjax extends ViewAjax
{
    protected $method = "";
    protected $action = "";
    protected $mode = "";
    protected $hash = "";
    protected $unixtime = 0;

    protected bool $soft_fail = false;

    public function getSoftFail(): bool
    {
        return $this->soft_fail;
    }


    public function __construct(bool $AutoLoadTemplate = false)
    {
        parent::__construct($AutoLoadTemplate);
        $this->requiredValues();
        $this->timeWindow();
        if ($this->load_ok == false) {
            $this->setSwapTag("status", false);
            return;
        }
        $this->setSwapTag("message", "ready");
        $this->output->tempateSecondLifeAjax();
    }

    protected function timeWindow(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        $timewindow = 120;
        $now = time();
        if ($this->unixtime > $now) {
            if ($this->unixtime > ($now + $timewindow)) {
                $this->load_ok = false;
            }
        } elseif ($this->unixtime < $now) {
            if ($this->unixtime < ($now - $timewindow)) {
                $this->load_ok = false;
            }
        }
        if ($this->load_ok == false) {
            $this->setSwapTag("status", false);
            $this->setSwapTag("message", "timewindow is out of scope");
            return;
        }
    }

    protected function requiredValues(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        $input = new InputFilter();
        $this->unixtime = $input->postFilter("unixtime");
        $this->token = $input->postFilter("token");
        if ($this->token == $this->slconfig->getHttpInboundSecret()) {
            $this->load_ok = false;
        }
        if ($this->load_ok == false) {
            $this->setSwapTag("message", "One or more required values are missing");
            return;
        }
    }
}
