<?php

namespace App\Endpoints\View\Login;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct(false);
        $this->output->tempateFull();
        $this->setSwapTag("html_title", "Login");
        $this->setSwapTag("page_title", "");
        $this->setSwapTag("page_actions", "");
    }
}
