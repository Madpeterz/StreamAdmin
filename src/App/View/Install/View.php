<?php

namespace App\View\Install;

use App\Template\View as TemplateView;

abstract class View extends TemplateView
{
    public function __construct()
    {
        if (defined("INSTALLMODE") == false) {
            die("Error attempting to access installer incorrectly");
        }
        parent::__construct(false);
        $this->output->tempateFull();
        $this->output->setSwapTagString("html_title", "Installer");
        $this->output->setSwapTagString("page_title", "Installer");
        $this->output->setSwapTagString("page_actions", "");
    }
}
