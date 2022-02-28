<?php

namespace App\Endpoint\Control\Objects;

use App\Models\Sets\ObjectsSet;
use App\Framework\ViewAjax;

class Clear extends ViewAjax
{
    public function process(): void
    {

        $accept = $this->post("accept")->asString();
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
