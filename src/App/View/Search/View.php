<?php

namespace App\View\Search;

use App\View as BasicView;

abstract class View extends BasicView
{
    protected Array $allowDisallow = [0 => "Disabled",1 => "Allow"];
    protected Array $yesNo = [0 => "No",1 => "Yes"];
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", "Search");
        $this->output->setSwapTagString("page_title", "Search results [Not loaded]");
        $this->output->setSwapTagString("page_actions", "");
    }
}
