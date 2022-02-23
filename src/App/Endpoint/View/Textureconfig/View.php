<?php

namespace App\Endpoint\View\Textureconfig;

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
            return;
        }
        $this->setSwapTag("html_title", "Texture packs");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->setSwapTag(
            "page_actions",
            "<a href='[[SITE_URL]]textureconfig/create'><button type='button' "
            . "class='btn btn-success'>Create</button></a>"
        );
    }
}
