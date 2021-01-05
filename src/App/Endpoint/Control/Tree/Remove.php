<?php

namespace App\Endpoints\Control\Tree;

use App\Models\Treevender;
use App\Models\TreevenderpackagesSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postFilter("accept");
        $this->output->setSwapTagString("redirect", "tree");
        if ($accept != "Accept") {
            $this->output->setSwapTagString("redirect", "tree/manage/" . $this->page . "");
            $this->output->setSwapTagString("message", "Did not Accept");
            return;
        }
        $treevender = new Treevender();
        if ($treevender->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find tree vender");
            return;
        }
        $treevender_package_set = new TreevenderpackagesSet();
        $treevender_package_set->loadOnField("treevenderlink", $treevender->getId());
        $purge_status = $treevender_package_set->purgeCollection();
        if ($purge_status["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to purge packages linked to tree vender");
            return;
        }
        $remove_status = $treevender->removeEntry();
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to remove tree vender: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Tree vender removed");
    }
}
