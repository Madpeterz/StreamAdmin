<?php

namespace App\Endpoint\View\Stream;

use App\Framework\Menu;
use App\Models\Package;
use App\Models\Server;

abstract class View extends Menu
{
    public function __construct()
    {
        parent::__construct();
        $package = new Package();
        if ($package->HasAny() == false) {
            $this->output->redirectWithMessage("package", "Please create a package before creating a stream");
            return;
        }
        $server = new Server();
        if ($server->HasAny() == false) {
            $this->output->redirectWithMessage("server", "Please create a server before creating a stream");
        }
        $this->setSwapTag("html_title", "Streams");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->setSwapTag(
            "page_actions",
            "<a href='[[SITE_URL]]stream/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
    }
}
