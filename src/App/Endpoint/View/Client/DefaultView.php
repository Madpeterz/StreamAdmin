<?php

namespace App\Endpoint\View\Client;

class DefaultView extends View
{
    public function process(): void
    {
        $this->setSwapTag(
            "page_actions",
            "<a href='[[SITE_URL]]client/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
        $view = new SelectNoticeLevel();
        if ($this->siteConfig->getSlConfig()->getClientsListMode() == true) {
            $view = new ListMode();
        }
        $view->process();
        $this->output = $view->getOutputObject();
    }
}
