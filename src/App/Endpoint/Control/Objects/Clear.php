<?php

namespace App\Endpoints\Control\Objects;

use App\Models\ObjectsSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Clear extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postFilter("accept");
        $this->output->setSwapTagString("redirect", "objects");
        if ($accept != "Accept") {
            $this->output->setSwapTagString("message", "Did not Accept");
            return;
        }
        $objects_set = new ObjectsSet();
        $objects_set->loadAll();
        $purge_status = $objects_set->purgeCollection();
        if ($purge_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to purge collection: %1\$s", $purge_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Collection purged");
    }
}
