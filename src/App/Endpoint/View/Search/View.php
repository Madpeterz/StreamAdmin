<?php

namespace App\Endpoints\View\Search;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", "Search");
        $this->output->setSwapTagString("page_title", "Search results [Not loaded]");
        $this->output->setSwapTagString("page_actions", "");
    }
}
