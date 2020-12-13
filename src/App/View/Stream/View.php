<?php

namespace App\View\Stream;

use App\Models\Package;
use App\Models\Server;
use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $package = new Package();
        if ($package->HasAny() == false) {
            $this->output->redirect("package?message=Please create a package before creating a stream");
            return;
        }
        $server = new Server();
        if ($server->HasAny() == false) {
                $this->output->redirect("server?message=Please create a server before creating a stream");
        }
        $this->output->setSwapTagString("html_title", "Streams");
        $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->output->setSwapTagString(
            "page_actions",
            "<a href='[[url_base]]stream/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
    }
}
