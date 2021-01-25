<?php

namespace App\Endpoint\View\Package;

use App\Models\Template;
use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $template = new Template();
        if ($template->HasAny() == false) {
            $this->output->redirectWithMessage("template", "Please create a template before creating a package");
            return;
        }
        $this->setSwapTag("html_title", "Packages");
        $this->setSwapTag("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->setSwapTag(
            "page_actions",
            "<a href='[[url_base]]package/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
    }
}
