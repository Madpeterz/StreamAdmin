<?php

namespace App\Endpoint\View\Export;

use App\Framework\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Export");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Export");
        $this->setSwapTag("page_actions", "");
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->output->redirectWithMessage("home", "Only the system owner can create exports!");
            return;
        }
    }
}
