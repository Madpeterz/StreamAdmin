<?php

namespace App\Endpoint\Control\Tree;

use App\R7\Model\Treevenderpackages;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Removepackage extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postFilter("accept");
        $this->setSwapTag("redirect", "tree");
        if ($accept != "Accept") {
            $this->setSwapTag(
                "message",
                "You have failed to type Accept now repent as you are sent back to the listings"
            );
            return;
        }
        $treevender_packages = new Treevenderpackages();
        if ($treevender_packages->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to load the linked package object for the tree vender");
            return;
        }
        $redirect_to = $treevender_packages->getTreevenderLink();
        $remove_status = $treevender_packages->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to remove linked package from tree vender: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("redirect", "tree/manage/" . $redirect_to . "");
        $this->setSwapTag("message", "Tree vender linked package removed");
    }
}
