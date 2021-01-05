<?php

namespace App\Endpoints\View\Login;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct(false);
        $this->output->tempateFull();
        $this->output->setSwapTagString("html_title", "Login");
        $this->output->setSwapTagString("page_title", "");
        $this->output->setSwapTagString("page_actions", "");
    }
}
