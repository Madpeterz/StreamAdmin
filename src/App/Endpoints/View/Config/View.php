<?php

namespace App\Endpoints\View\Config;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", "Config");
        $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]]");
        $this->output->setSwapTagString("page_actions", "");
    }
}
