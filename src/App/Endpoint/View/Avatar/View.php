<?php

namespace App\Endpoints\View\Avatar;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Avatars");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Avatars");
        $this->setSwapTag(
            "page_actions",
            "<a href='[[url_base]]avatar/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
    }
}
