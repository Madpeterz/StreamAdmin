<?php

namespace App\Endpoint\Control\Avatar;

use App\Models\Avatar;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new inputFilter();
        $accept = $input->postFilter("accept");
        $this->setSwapTag("redirect", "avatar");
        $this->setSwapTag("message", "Not processed");
        if ($accept != "Accept") {
            $this->setSwapTag("message", "Did not Accept");
            $this->setSwapTag("redirect", "avatar/manage/" . $this->page . "");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadByField("avatarUid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to find avatar");
            return;
        }
        $remove_status = $avatar->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to remove avatar: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "Avatar removed");
    }
}
