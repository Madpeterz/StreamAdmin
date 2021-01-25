<?php

namespace App\Endpoint\View\Login;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct(true);
        $this->output->tempateFull();
        $this->setSwapTag("html_title", "Login");
        $this->setSwapTag("page_title", "");
        $this->setSwapTag("page_actions", "");
    }
}
