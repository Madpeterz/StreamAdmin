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
        $accept = $input->postString("accept");
        $this->setSwapTag("redirect", "tree");
        if ($accept != "Accept") {
            $this->failed(
                "You have failed to type Accept now repent as you are sent back to the listings"
            );
            return;
        }
        $treevender_packages = new Treevenderpackages();
        if ($treevender_packages->loadID($this->page) == false) {
            $this->failed("Unable to load the linked package object for the tree vender");
            return;
        }
        $redirect_to = $treevender_packages->getTreevenderLink();
        $remove_status = $treevender_packages->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(
                sprintf("Unable to remove linked package from tree vender: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->setSwapTag("redirect", "tree/manage/" . $redirect_to . "");
        $this->ok("Tree vender linked package removed");
    }
}
