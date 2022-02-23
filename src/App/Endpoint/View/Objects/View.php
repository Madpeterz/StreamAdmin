<?php

namespace App\Endpoint\View\Objects;

use App\Framework\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Objects");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Objects");
        $this->setSwapTag("page_actions", "<a href='[[SITE_URL]]objects/clear'>"
        . "<button type='button' class='btn btn-outline-warning'>Clear</button></a>");
    }
}
