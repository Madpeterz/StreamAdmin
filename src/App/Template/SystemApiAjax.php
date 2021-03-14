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
        $this->hashCheck();
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
        $required_sl = [
            "method",
            "action",
            "mode",
        ];

        $input = new InputFilter();
        $this->staticpart = "";
        foreach ($required_sl as $slvalue) {
            $value = $input->postFilter($slvalue);
            if ($value !== null) {
                $this->$slvalue = $value;
                $this->staticpart .= $value;
            } else {
                $this->load_ok = false;
                $this->setSwapTag("message", "Value: " . $slvalue . " is missing");
                break;
            }
        }
        $this->unixtime = $input->postFilter("unixtime");
        if ($this->unixtime === null) {
            $this->load_ok = false;
        }
        $this->hash = $input->postFilter("hash");
        if ($this->hash === null) {
            $this->load_ok = false;
        }
        if ($this->load_ok == false) {
            $this->setSwapTag("message", "One or more required values are missing");
            return;
        }
    }
    protected function hashCheck(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        $raw = $this->unixtime . "" . $this->staticpart . "" . $this->slconfig->getHttpInboundSecret();
        $hashcheck = sha1($raw);
        if ($hashcheck != $this->hash) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to vaildate request to API endpoint: ");
            return;
        }
    }
}
