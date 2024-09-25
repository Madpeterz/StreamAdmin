<?php

namespace App\Endpoint\View\Client;

use App\Framework\Menu;
use App\Models\Stream;

abstract class View extends Menu
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
            "<a href='[[SITE_URL]]client/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
    }
}
