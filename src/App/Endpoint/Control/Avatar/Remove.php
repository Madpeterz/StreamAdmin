<?php

namespace App\Endpoint\Control\Avatar;

use App\Models\Avatar;
use App\Framework\ViewAjax;

class Remove extends ViewAjax
{
    public function process(): void
    {

        $accept = $this->post("accept")->asString();
        if ($accept == null) {
            $this->failed("Accept button not triggered");
            return;
        }
        $this->setSwapTag("redirect", "avatar");
        $this->failed("Not processed");
        if ($accept != "Accept") {
            $this->setSwapTag("redirect", "avatar/manage/" . $this->siteConfig->getPage() . "");
            $this->failed("Did not Accept");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadByAvatarUid($this->siteConfig->getPage()) == false) {
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
