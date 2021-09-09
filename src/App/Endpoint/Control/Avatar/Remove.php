<?php

namespace App\Endpoint\Control\Avatar;

use App\R7\Model\Avatar;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new inputFilter();
        $accept = $input->postString("accept");
        if ($accept == null) {
            $this->failed("Accept button not triggered");
            return;
        }
        $this->setSwapTag("redirect", "avatar");
        $this->failed("Not processed");
        if ($accept != "Accept") {
            $this->setSwapTag("redirect", "avatar/manage/" . $this->page . "");
            $this->failed("Did not Accept");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadByAvatarUid($this->page) == false) {
            $this->failed("Unable to find avatar");
            return;
        }
        $remove_status = $avatar->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(sprintf("Unable to remove avatar: %1\$s", $remove_status["message"]));
            return;
        }
        $this->ok("Avatar removed");
    }
}
