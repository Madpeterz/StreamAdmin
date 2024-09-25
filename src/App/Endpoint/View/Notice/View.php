<?php

namespace App\Endpoint\View\Notice;

use App\Endpoint\View\Shared\SwapsTable;
use App\Models\Stream;

abstract class View extends SwapsTable
{
    public function __construct()
    {
        parent::__construct();
        if ((new Stream())->HasAny() == false) {
            $this->output->redirect("stream?message=Please create a stream first");
        }
        $this->setSwapTag("html_title", "Notices");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] 
        / <a href='[[SITE_URL]]notice'>Notices</a> / ");
        $this->setSwapTag("page_actions", "<a href='[[SITE_URL]]notice/create'>"
        . "<button type='button' class='btn btn-success'>Create</button></a>");
    }
}
