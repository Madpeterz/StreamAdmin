<?php

namespace App\Endpoint\View\Banlist;

use App\Endpoint\View\Avatar\ListMode;
use App\Endpoint\View\Avatar\SelectNoticeLevel;
use App\Endpoint\View\Client\View as View;

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
    }
}
