<?php

namespace App\View\Package;

use App\Template;
use App\Template\View as BasicView;

abstract class View extends BasicView
{
    public function __construct()
    {
        parent::__construct();
        $template = new Template();
        if ($template->HasAny() == false) {
            $this->output->redirect("template?message=Please create a template before creating a package");
            return;
        }
        $this->output->setSwapTagString("html_title", "Packages");
        $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
        $this->output->setSwapTagString(
            "page_actions",
            "<a href='[[url_base]]package/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
    }
}
