<?php

namespace App\Endpoint\View\Notice;

use App\Models\Stream;
use App\Framework\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $stream = new Stream();
        if ($stream->HasAny() == false) {
            $this->output->redirect("stream?message=Please create a stream first");
        }
        $this->setSwapTag("html_title", "Notices");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] 
        / <a href='[[SITE_URL]]notice'>Notices</a> / ");
        $this->setSwapTag("page_actions", "<a href='[[SITE_URL]]notice/create'>"
        . "<button type='button' class='btn btn-success'>Create</button></a>");
    }
}
