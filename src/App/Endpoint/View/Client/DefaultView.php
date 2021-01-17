<?php

namespace App\Endpoints\View\Banlist;

use App\Endpoints\View\Avatar\ListMode;
use App\Endpoints\View\Avatar\SelectNoticeLevel;
use App\Endpoints\View\Client\View as View;

class DefaultView extends View
{
    public function process(): void
    {
        $this->setSwapTag(
            "page_actions",
            "<a href='[[url_base]]client/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
        $view = new SelectNoticeLevel();
        if ($this->slconfig->getClients_list_mode() == true) {
            $view = new ListMode();
        }
        $view->process();
    }
}
