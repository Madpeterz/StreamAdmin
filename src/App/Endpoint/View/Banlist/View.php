<?php

namespace App\Endpoints\View\Banlist;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", "Banlist");
        $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->output->setSwapTagString("page_actions", "");
    }
}
