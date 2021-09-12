<?php

namespace App\Endpoint\View\Install;

use App\Template\View as TemplateView;

abstract class View extends TemplateView
{
    public function __construct()
    {
        global $cache;
        if ($cache != null) {
            $cache->purge();
        }
        if (defined("INSTALLMODE") == false) {
            die("Error attempting to access installer incorrectly");
        }
        parent::__construct(true);
        $this->output->tempateFull();
        $this->setSwapTag("html_title", "Installer");
        $this->setSwapTag("page_title", "Installer");
        $this->setSwapTag("page_actions", "");
    }
}
