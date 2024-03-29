<?php

namespace App\Endpoint\Control\Objects;

use App\R7\Set\ObjectsSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Clear extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postString("accept");
        $this->setSwapTag("redirect", "objects");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            return;
        }
        $objects_set = new ObjectsSet();
        $objects_set->loadAll();
        $purge_status = $objects_set->purgeCollection();
        if ($purge_status["status"] == false) {
            $this->failed(
                sprintf("Unable to clear objects from DB because: %1\$s", $purge_status["message"])
            );
            return;
        }
        $this->ok("Objects cleared from DB");
    }
}
