<?php

namespace App\Endpoint\Control\Tree;

use App\R7\Model\Treevender;
use App\R7\Set\TreevenderpackagesSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postFilter("accept");
        $this->setSwapTag("redirect", "tree");
        if ($accept != "Accept") {
            $this->setSwapTag("redirect", "tree/manage/" . $this->page . "");
            $this->failed("Did not Accept");
            return;
        }
        $treevender = new Treevender();
        if ($treevender->loadID($this->page) == false) {
            $this->failed("Unable to find tree vender");
            return;
        }
        $treevender_package_set = new TreevenderpackagesSet();
        $treevender_package_set->loadOnField("treevenderLink", $treevender->getId());
        $purge_status = $treevender_package_set->purgeCollection();
        if ($purge_status["status"] == false) {
            $this->failed("Unable to purge packages linked to tree vender");
            return;
        }
        $remove_status = $treevender->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to remove tree vender: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->ok("Tree vender removed");
    }
}
