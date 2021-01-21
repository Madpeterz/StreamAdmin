<?php

namespace App\Endpoint\View\Slconfig;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", " System setup");
        $this->setSwapTag("page_title", " Editing system setup");
        $this->setSwapTag("page_actions", "");
    }
}
