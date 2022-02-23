<?php

namespace App\Endpoint\View\Template;

use App\Framework\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Templates");
        $this->setSwapTag(
            "page_title",
            "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Teamplate: "
        );
        $this->setSwapTag(
            "page_actions",
            "<a href='[[SITE_URL]]template/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
    }
}
