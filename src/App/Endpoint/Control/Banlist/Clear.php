<?php

namespace App\Endpoint\Control\Banlist;

use App\Models\Banlist;
use App\Models\Staff;
use App\Template\ControlAjax;

class Clear extends ControlAjax
{
    public function process(): void
    {
        $banlist = new Banlist();
        $this->setSwapTag("redirect", "banlist");
        if ($banlist->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("unable to find entry");
            return;
        }
        $remove_status = $banlist->removeEntry();
        if ($remove_status->status == false) {
            $this->failed("Unable to remove entry");
            return;
        }
        $this->ok("Entry removed");
    }
}
