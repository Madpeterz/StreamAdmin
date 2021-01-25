<?php

namespace App\Endpoint\Control\Objects;

use App\Models\ObjectsSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Clear extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postFilter("accept");
        $this->setSwapTag("redirect", "objects");
        if ($accept != "Accept") {
            $this->setSwapTag("message", "Did not Accept");
            return;
        }
        $objects_set = new ObjectsSet();
        $objects_set->loadAll();
        $purge_status = $objects_set->purgeCollection();
        if ($purge_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to purge collection: %1\$s", $purge_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Collection purged");
    }
}
