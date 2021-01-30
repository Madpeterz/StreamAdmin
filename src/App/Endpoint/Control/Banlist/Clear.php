<?php

namespace App\Endpoint\Control\Banlist;

use App\R7\Model\Banlist;
use App\Template\ViewAjax;

class Clear extends ViewAjax
{
    public function process(): void
    {
        $banlist = new Banlist();
        $this->setSwapTag("redirect", "banlist");
        if ($banlist->loadID($this->page) == false) {
            $this->setSwapTag("message", "unable to find entry");
            return;
        }
        $remove_status = $banlist->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag("message", "Unable to remove entry");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "entry removed");
        return;
    }
}
