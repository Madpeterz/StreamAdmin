<?php

namespace App\Endpoints\View\Slconfig;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", " System setup");
        $this->output->setSwapTagString("page_title", " Editing system setup");
        $this->output->setSwapTagString("page_actions", "");
    }
}