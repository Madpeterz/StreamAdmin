<?php

namespace App\Endpoint\Control\Tree;

use App\Models\Treevender;
use App\Models\Sets\TreevenderpackagesSet;
use App\Framework\ViewAjax;

class Remove extends ViewAjax
{
    public function process(): void
    {

        $accept = $this->input->post("accept");
        $this->setSwapTag("redirect", "tree");
        if ($accept != "Accept") {
            $this->setSwapTag("redirect", "tree/manage/" . $this->siteConfig->getPage() . "");
            $this->failed("Did not Accept");
            return;
        }
        $treevender = new Treevender();
        if ($treevender->loadID($this->siteConfig->getPage()) == false) {
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
