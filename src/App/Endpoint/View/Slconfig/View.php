<?php

namespace App\Endpoint\View\Slconfig;

use App\Framework\Menu;

abstract class View extends Menu
{
    public function __construct()
    {
        parent::__construct();
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->output->redirectWithMessage("home", "Only the system owner can adjust setup!");
            return;
        }
        $this->setSwapTag("html_title", " System setup");
        $this->setSwapTag("page_title", " Editing system setup");
        $this->setSwapTag("page_actions", "");
    }
}
