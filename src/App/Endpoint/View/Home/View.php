<?php

namespace App\Endpoint\View\Home;

use App\Framework\View as TemplateView;

abstract class View extends TemplateView
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Dashboard");
        $this->setSwapTag("page_title", "Dashboard");
        $this->setSwapTag("page_actions", "");
    }
}
