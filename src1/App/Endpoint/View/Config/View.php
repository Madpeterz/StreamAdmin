<?php

namespace App\Endpoint\View\Config;

use App\Framework\Menu;

abstract class View extends Menu
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Config");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]]");
        $this->setSwapTag("page_actions", "");
    }
}
