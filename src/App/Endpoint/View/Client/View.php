<?php

namespace App\Endpoint\View\Client;

use App\Models\Stream;
use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $stream = new Stream();
        if ($stream->HasAny() == false) {
            $this->output->redirectWithMessage("stream", "Please create a stream before creating a client");
            return;
        }
        $this->setSwapTag("html_title", "Clients");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->setSwapTag(
            "page_actions",
            "<a href='[[url_base]]client/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
    }
}
