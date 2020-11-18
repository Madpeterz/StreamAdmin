<?php

namespace App\View\Login;

use App\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", "Login");
        $this->output->setSwapTagString("page_title", "");
        $this->output->setSwapTagString("page_actions", "");
    }
}
