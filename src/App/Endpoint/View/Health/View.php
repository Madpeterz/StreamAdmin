<?php

namespace App\Endpoint\View\Health;

use App\Framework\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Health");
        $this->setSwapTag("page_title", "<i class=\"fas fa-heartbeat\"></i> Health ");
        $this->setSwapTag("page_actions", "");
    }
}
