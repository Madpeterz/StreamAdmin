<?php

namespace App\Endpoint\View\Banlist;

use App\Framework\Menu;

abstract class View extends Menu
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Banlist");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->setSwapTag("page_actions", "");
    }
}
