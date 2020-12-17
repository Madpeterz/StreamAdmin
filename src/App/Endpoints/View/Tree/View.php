<?php

namespace App\Endpoints\View\Tree;

use App\Models\Stream;
use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $stream = new Stream();
        if ($stream->HasAny() == false) {
            $this->output->redirect("stream?message=Please create a stream first!");
        }
        $this->output->setSwapTagString("html_title", "Tree vender");
        $this->output->setSwapTagString(
            "page_title",
            "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Tree vender"
        );
        $this->output->setSwapTagString(
            "page_actions",
            "<a href='[[url_base]]tree/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
    }
}
