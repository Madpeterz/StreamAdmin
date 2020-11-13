<?php

namespace App\View\Home;

use App\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", "Dashboard");
        $this->output->setSwapTagString("page_title", "Dashboard");
        $this->output->setSwapTagString("page_actions", "");
    }
}
