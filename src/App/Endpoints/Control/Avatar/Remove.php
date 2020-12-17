<?php

namespace App\Endpoints\Control\Avatar;

use App\Models\Avatar;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new inputFilter();
        $accept = $input->postFilter("accept");
        $this->output->setSwapTagString("redirect", "avatar");
        $this->output->setSwapTagString("message", "Not processed");
        if ($accept != "Accept") {
            $this->output->setSwapTagString("message", "Did not Accept");
            $this->output->setSwapTagString("redirect", "avatar/manage/" . $this->page . "");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadByField("avatar_uid", $this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find avatar");
            return;
        }
        $remove_status = $avatar->removeEntry();
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to remove avatar: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Avatar removed");
    }
}
