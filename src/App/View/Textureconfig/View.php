<?php

namespace App\View\Textureconfig;

use App\Stream;
use App\Template\View as BasicView;

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
        $this->output->setSwapTagString("html_title", "Texture packs");
        $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->output->setSwapTagString(
            "page_actions",
            "<a href='[[url_base]]textureconfig/create'><button type='button' "
            . "class='btn btn-success'>Create</button></a>"
        );
    }
}
