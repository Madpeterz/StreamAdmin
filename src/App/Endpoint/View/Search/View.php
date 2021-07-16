<?php

namespace App\Endpoint\View\Search;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Search");
        $this->setSwapTag("page_title", "Search results [Not loaded]");
        $this->setSwapTag("page_actions", "");
    }
}
