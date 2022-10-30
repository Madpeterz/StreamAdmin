<?php

namespace App\Template;

use App\Config;
use YAPF\Bootstrap\Template\ViewAjax;
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
            $to = $this->method;
        }
        $this->setSwapTag("redirect", $to);
        $this->ok($message);
    }
    protected function ok(string $message = ""): void
    {
        $this->setMessage($message, true);
    }
}
