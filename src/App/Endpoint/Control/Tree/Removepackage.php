<?php

namespace App\Endpoints\Control\Tree;

use App\Models\Treevenderpackages;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Removepackage extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postFilter("accept");
        $this->output->setSwapTagString("redirect", "tree");
        if ($accept != "Accept") {
            $this->output->setSwapTagString(
                "message",
                "You have failed to type Accept now repent as you are sent back to the listings"
            );
            return;
        }
        $treevender_packages = new Treevenderpackages();
        if ($treevender_packages->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to load the linked package object for the tree vender");
            return;
        }
        $redirect_to = $treevender_packages->getTreevenderlink();
        $remove_status = $treevender_packages->removeEntry();
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to remove linked package from tree vender: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("redirect", "tree/manage/" . $redirect_to . "");
        $this->output->setSwapTagString("message", "Tree vender linked package removed");
    }
}
