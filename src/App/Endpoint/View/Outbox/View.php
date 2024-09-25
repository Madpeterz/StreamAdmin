<?php

namespace App\Endpoint\View\Outbox;

use App\Endpoint\View\Shared\SwapsTable;
use App\Models\Stream;

abstract class View extends SwapsTable
{
    public function __construct()
    {
        parent::__construct();
        $stream = new Stream();
        if ($stream->HasAny() == false) {
            $this->output->redirect("stream?message=Please create a stream first");
            return;
        }
        $this->setSwapTag("html_title", "Outbox");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->setSwapTag("page_actions", "");
    }
}
