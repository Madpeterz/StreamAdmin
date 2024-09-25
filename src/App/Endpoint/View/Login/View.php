<?php

namespace App\Endpoint\View\Login;

use App\Framework\Menu;

abstract class View extends Menu
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
