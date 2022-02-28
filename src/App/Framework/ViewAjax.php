<?php

namespace App\Framework;

use App\Config;
use YAPF\Bootstrap\Template\ViewAjax as TemplateViewAjax;
use YAPF\InputFilter\InputFilter;

abstract class ViewAjax extends TemplateViewAjax
{
    protected Config $siteConfig;
    protected InputFilter $input;
    public function __construct(
        bool $AutoLoadTemplate = true
    ) {
        global $system;
        $this->input = new InputFilter();
        $this->siteConfig = $system;
        parent::__construct();
    }

    protected function post(string $field): InputFilter
    {
        return $this->post($field);
    }
}
