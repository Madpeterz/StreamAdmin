<?php

namespace App\View\Home;

use App\Template\View as TemplateView;

abstract class View extends TemplateView
{
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", "Dashboard");
        $this->output->setSwapTagString("page_title", "Dashboard");
        $this->output->setSwapTagString("page_actions", "");
    }
}
