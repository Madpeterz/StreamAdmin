<?php

namespace App\Template;

use App\Config;
use App\Helpers\SystemApiKey;
use YAPF\Bootstrap\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

abstract class SystemApiAjax extends ViewAjax
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
        $this->requiredValues();
        $this->timeWindow();
        $this->hashok();
        if ($this->load_ok == false) {
            $this->setSwapTag("status", false);
            return;
        }
        $this->failed("ready");
    }
    protected function hashok(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        if (nullSafeStrLen($this->siteConfig->getSlConfig()->_HttpInboundSecret) < 5) {
            $this->load_ok = false;
            $this->failed("httpcode length must be 5 or longer");
            return;
        }
        if (nullSafeStrLen($this->siteConfig->getSlConfig()->_HttpInboundSecret) > 30) {
            $this->failed("httpcode length must be 30 or less");
            $this->load_ok = false;
            return;
        }

        $secret = $this->siteConfig->getSlConfig()->_HttpInboundSecret;
        $codes = array_merge([$secret], SystemApiKey::accepted($secret, $this->method, $this->action));
        foreach ($codes as $code) {
            $hashcheck = sha1(implode("", [$this->unixtime, $this->method, $this->action, $code]));
            if (hash_equals($hashcheck, $this->token) == true) {
                return;
            }
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
            "unixtime" => "i",
            "token" => "s",
            "method" => "s",
            "action" => "s",
        ];
        foreach ($required_values as $fieldname => $typematch) {
            $value = $this->input->post($fieldname)->checkStringLengthMin(1)->asString();
            if ($typematch == "i") {
                $value = $this->input->post($fieldname)->asInt();
            }
            if ($value === null) {
                $this->load_ok = false;
                $this->failed("Value: " . $fieldname . " is missing");
                return;
            }
            $this->$fieldname = $value;
        }
    }
}
