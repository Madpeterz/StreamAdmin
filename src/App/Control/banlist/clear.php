<?php

namespace App\Control\Banlist;

use App\Models\Banlist;
use App\Template\ViewAjax;

class Clear extends ViewAjax
{
    public function process(): void
    {
        $banlist = new Banlist();
        $this->output->setSwapTagString("redirect", "banlist");
        if ($banlist->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "unable to find entry");
            return;
        }
        $remove_status = $banlist->removeEntry();
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to remove entry");
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "entry removed");
        return;
    }
}
