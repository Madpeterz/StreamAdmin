<?php

namespace App\View\Banlist;

use App\View\Avatar\ListMode;
use App\View\Avatar\SelectNoticeLevel;
use App\View\Client\View as View;

class DefaultView extends View
{
    public function process()
    {
        $this->output->setSwapTagString(
            "page_actions",
            "<a href='[[url_base]]client/create'><button type='button' class='btn btn-success'>Create</button></a>"
        );
        $view = new SelectNoticeLevel();
        if ($this->slconfig->get_clients_list_mode() == true) {
            $view = new ListMode();
        }
        $view->process();
    }
}
