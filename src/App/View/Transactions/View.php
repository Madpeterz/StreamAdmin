<?php

namespace App\View\Transactions;

use App\Template\View as BasicView;

abstract class View extends BasicView
{
    protected $year = 2020;
    protected $month = 1;
    public function __construct()
    {
        parent::__construct();
        $this->output->setSwapTagString("html_title", "Transactions");
        $this->output->setSwapTagString(
            "page_title",
            "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Transactions: "
        );
        $this->output->setSwapTagString("page_actions", "");
        $this->month = date("m");
        $this->year = date("Y");
    }
}
