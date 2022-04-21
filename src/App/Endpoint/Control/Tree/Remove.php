<?php

namespace App\Endpoint\Control\Tree;

use App\Models\Treevender;
use App\Template\ControlAjax;

class Remove extends ControlAjax
{
    public function process(): void
    {

        $accept = $this->input->post("accept")->asString();
        $this->setSwapTag("redirect", "tree");
        if ($accept != "Accept") {
            $this->setSwapTag("redirect", "tree/manage/" . $this->siteConfig->getPage());
            $this->failed("Did not Accept");
            return;
        }
        $treevender = new Treevender();
        if ($treevender->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find tree vender");
            return;
        }
        $treevender_package_set = $treevender->relatedTreevenderpackages();
        $purge_status = $treevender_package_set->purgeCollection();
        if ($purge_status->status == false) {
            $this->failed("Unable to purge packages linked to tree vender");
            return;
        }
        $remove_status = $treevender->removeEntry();
        if ($remove_status->status == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to remove tree vender: %1\$s", $remove_status->message)
            );
            return;
        }
        $this->ok("Tree vender removed");
    }
}
