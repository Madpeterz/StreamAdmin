<?php

namespace App\Endpoint\View\Home;

use App\Framework\Menu;

abstract class View extends Menu
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Dashboard");
        $this->setSwapTag("page_title", "Dashboard");
        $this->setSwapTag("page_actions", "");
    }
}
