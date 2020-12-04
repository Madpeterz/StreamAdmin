<?php

namespace App\View\Error;

use App\Template\View as TemplateView;

abstract class View extends TemplateView
{
    public function __construct()
    {
        parent::__construct(false);
        $this->output->tempateFull();
        $this->output->setSwapTagString("html_title", "Error");
        $this->output->setSwapTagString("page_title", "Error");
        $this->output->setSwapTagString("page_actions", "");
    }
}
