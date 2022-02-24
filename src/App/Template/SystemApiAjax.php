<?php

namespace App\Template;

abstract class SystemApiAjax extends ViewAjax
{
    protected $unixtime = 0;
    protected $token = "";
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
        $this->hashok();
        if ($this->load_ok == false) {
            $this->setSwapTag("status", false);
            return;
        }
        $this->failed("ready");
        $this->output->tempateSecondLifeAjax();
    }
    protected function hashok(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        if (strlen($this->slconfig->getHttpInboundSecret()) < 5) {
            $this->load_ok = false;
            $this->failed("httpcode length must be 5 or longer");
            return;
        }
        if (strlen($this->slconfig->getHttpInboundSecret()) > 30) {
            $this->failed("httpcode length must be 30 or less");
            $this->load_ok = false;
            return;
        }

        $bits = [$this->unixtime,$this->method,$this->action,$this->slconfig->getHttpInboundSecret()];
        $hashcheck = sha1(implode("", $bits));
        if ($this->token == $hashcheck) {
            return;
        }
        $this->load_ok = false;
        $this->failed("Invaild token");
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
            $this->failed("timewindow is out of scope");
            return;
        }
    }

    protected function requiredValues(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        $required_values = [
            "unixtime",
            "token",
            "method",
            "action",
        ];

        $this->staticpart = "";
        foreach ($required_values as $slvalue) {
            $value = $this->input->post($slvalue);
            if ($value === null) {
                $this->load_ok = false;
                $this->failed("Value: " . $slvalue . " is missing");
                return;
            }
            $this->$slvalue = $value;
            $this->staticpart .= $value;
        }
    }
}
