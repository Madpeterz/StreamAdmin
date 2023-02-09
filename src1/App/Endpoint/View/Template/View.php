<?php

namespace App\Endpoint\View\Template;

use App\Endpoint\View\Shared\SwapsTable;

abstract class View extends SwapsTable
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
