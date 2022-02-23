<?php

namespace App\Endpoint\Control\Banlist;

use App\Framework\ViewAjax;
use App\Models\Banlist;

class Clear extends ViewAjax
{
    public function process(): void
    {
        $banlist = new Banlist();
        $this->setSwapTag("redirect", "banlist");
        if ($banlist->loadID($this->siteConfig->getPage()) == false) {
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
