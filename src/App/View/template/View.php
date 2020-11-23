<?php

namespace App\View\Templates;

use App\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", "Templates");
        $this->output->setSwapTagString(
            "page_title",
            "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Teamplate: "
        );
        $this->output->setSwapTagString(
            "page_actions",
            "<a href='[[url_base]]template/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
    }
}
