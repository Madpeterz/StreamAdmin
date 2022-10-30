<?php

namespace App\Endpoint\Control\Objects;

use App\Models\Sets\ObjectsSet;
use App\Template\ControlAjax;

class Clear extends ControlAjax
{
    public function process(): void
    {
        $accept = $this->input->post("accept")->asString();
        $this->setSwapTag("redirect", "objects");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            return;
        }
        $objects_set = new ObjectsSet();
        $objects_set->loadAll();
        $purge_status = $objects_set->purgeCollection();
        if ($purge_status->status == false) {
            $this->failed(
                sprintf("Unable to clear objects from DB because: %1\$s", $purge_status->message)
            );
            return;
        }
        $this->redirectWithMessage("Objects cleared from DB");
    }
}
