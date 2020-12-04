<?php

namespace App\View\Staff;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", "Staff");
        $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Staff ");
        $this->output->setSwapTagString("page_actions", "");
        if ($this->session->getOwnerLevel() == true) {
            $this->output->setSwapTagString(
                "page_actions",
                "<a href='[[url_base]]staff/create'><button type='button' class='btn btn-success'>Create</button></a>"
            );
        }
    }
}
