<?php

namespace App\Endpoint\View\Transactions;

use App\Framework\Menu;

abstract class View extends Menu
{
    protected $year = 2020;
    protected $month = 1;
    public function __construct()
    {
        parent::__construct();
        $this->setSwapTag("html_title", "Transactions");
        $this->setSwapTag(
            "page_title",
            "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Transactions: "
        );
        $this->setSwapTag("page_actions", "");
        $this->month = date("m");
        $this->year = date("Y");
    }
}
