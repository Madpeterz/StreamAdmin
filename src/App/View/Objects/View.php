<?php

namespace App\View\Notice;

use App\Models\Stream;
use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", "Objects");
        $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Objects");
        $this->output->setSwapTagString("page_actions", "<a href='[[url_base]]objects/clear'>"
        . "<button type='button' class='btn btn-outline-warning'>Clear</button></a>");
    }
}
