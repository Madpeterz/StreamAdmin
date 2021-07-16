<?php

namespace App\Endpoint\View\Client;

class DefaultView extends View
{
    public function process(): void
    {
        $this->setSwapTag(
            "page_actions",
            "<a href='[[url_base]]client/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
        $view = new SelectNoticeLevel();
        if ($this->slconfig->getClientsListMode() == true) {
            $view = new ListMode();
        }
        $view->process();
        $this->output = $view->getOutputObject();
    }
}
