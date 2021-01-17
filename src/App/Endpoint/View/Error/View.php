<?php

namespace App\Endpoints\View\Error;

use App\Template\View as TemplateView;

abstract class View extends TemplateView
{
    public function __construct()
    {
        parent::__construct(false);
        $this->output->tempateFull();
        $this->setSwapTag("html_title", "Error");
        $this->setSwapTag("page_title", "Error");
        $this->setSwapTag("page_actions", "");
    }
}
