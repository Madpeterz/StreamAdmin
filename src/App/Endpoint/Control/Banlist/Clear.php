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
            $this->failed("unable to find entry");
            return;
        }
        $remove_status = $banlist->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed("Unable to remove entry");
            return;
        }
        $this->ok("Entry removed");
    }
}
