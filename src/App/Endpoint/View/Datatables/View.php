<?php

namespace App\Endpoint\View\Datatables;

use App\Framework\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Datatables");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Datatables");
        $this->setSwapTag("page_actions", "");
    }
}
