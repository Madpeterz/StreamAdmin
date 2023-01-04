<?php

namespace App\Endpoint\View\Auditlog;

use App\Framework\Menu;

abstract class View extends Menu
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Auditlog");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] 
        / <a href='[[SITE_URL]]Auditlog'>Auditlog</a>");
        $this->setSwapTag("page_actions", "");
    }
}
